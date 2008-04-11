
(function($) {

	if (window.Node && Node.prototype && !Node.prototype.contains) {
		Node.prototype.contains = function (arg) {
			return !!(this.compareDocumentPosition(arg) & 16)
		}
	}

	//Make nodes selectable by expression
	$.extend($.expr[':'], { sortable: "(' '+a.className+' ').indexOf(' ui-sortable ')" });

	$.fn.sortable = function(o) {
		return this.each(function() {
			new $.ui.sortable(this,o);	
		});
	}
	
	//Macros for external methods that support chaining
	var methods = "destroy,enable,disable,refresh".split(",");
	for(var i=0;i<methods.length;i++) {
		var cur = methods[i], f;
		eval('f = function() { var a = arguments; return this.each(function() { if(jQuery(this).is(".ui-sortable")) jQuery.data(this, "ui-sortable")["'+cur+'"](a); }); }');
		$.fn["sortable"+cur.substr(0,1).toUpperCase()+cur.substr(1)] = f;
	};
	
	//get instance method
	$.fn.sortableInstance = function() {
		if($(this[0]).is(".ui-sortable")) return $.data(this[0], "ui-sortable");
		return false;
	};
	
	$.ui.sortable = function(el,o) {
		this.element = el;
		this.set = [];
		this.offset = [];
		var options = {};
		var self = this;
		$.data(this.element, "ui-sortable", this);
		$(el).addClass("ui-sortable");
		
		$.extend(options, o);
		$.extend(options, {
			items: options.items || '> li',
			smooth: options.smooth != undefined ? options.smooth : true,
			helper: options.helper || 'clone', //JU: modified this line from the original file (helper:'clone')
			containment: options.containment ? (options.containment == 'sortable' ? el : options.containment) : null,
			zIndex: options.zIndex || 1000,
			_start: function(h,p,c,t,e) {
				self.start.apply(t, [self, e]); // Trigger the onStart callback				
			},
			_beforeStop: function(h,p,c,t,e) {
				self.stop.apply(t, [self, e]); // Trigger the onStart callback
			},
			_drag: function(h,p,c,t,e) {
				self.drag.apply(t, [self, e]); // Trigger the onStart callback
			},
			startCondition: function() {
				return !self.disabled;	
			}			
		});
		
		//Get the items
		var items = $(options.items, el);
		
		//Let's determine the floating mode
		options.floating = /left|right/.test(items.css('float'));
		
		//Let's determine the parent's offset
		if($(el).css('position') == 'static') $(el).css('position', 'relative');
		options.offset = $(el).offset({ border: false });

		items.each(function() {
			new $.ui.mouseInteraction(this,options);
		});
		
		//Add current items to the set
		items.each(function() {
			self.set.push([this,null]);
			self.offset.push($(this).offset());
		});
		
		this.options = options;
	}
	
	$.extend($.ui.sortable.prototype, {
		plugins: {},
		currentTarget: null,
		lastTarget: null,
		prepareCallbackObj: function(self, that) {
			if (!self.pos) self.pos = [0, 0];
			return {
				helper: self.helper,
				position: { left: self.pos[0], top: self.pos[1] },
				offset: self.options.cursorAt,
				draggable: self,
				current: that,
				options: self.options
			}			
		},
		refresh: function() {

			//Get the items
			var self = this;
			var items = $(this.options.items, this.element);

			var unique = [];
			items.each(function() {
				old = false;
				for(var i=0;i<self.set.length;i++) { if(self.set[i][0] == this) old = true;	}
				if(!old) unique.push(this);
			});
			
			for(var i=0;i<unique.length;i++) {
				new $.ui.mouseInteraction(unique[i],self.options);
			}
			
			//Add current items to the set
			this.set = [];
			items.each(function() {
				self.set.push([this,null]);
			});
			
		},
		destroy: function() {
			$(this.element).removeClass("ui-sortable").removeClass("ui-sortable-disabled");
			$(this.options.items, this.element).mouseInteractionDestroy();
			
		},
		enable: function() {
			$(this.element).removeClass("ui-sortable-disabled");
			this.disabled = false;
		},
		disable: function() {
			$(this.element).addClass("ui-sortable-disabled");
			this.disabled = true;
		},
		start: function(that, e) {
		
			var o = this.options;

			if(o.hoverClass) {
				that.helper = $('<div class="'+o.hoverClass+'"></div>').appendTo('body').css({
					height: this.element.offsetHeight+'px',
					width: this.element.offsetWidth+'px',
					position: 'absolute'	
				});
			}
			
			if(o.zIndex) {
				if($(this.helper).css("zIndex")) o.ozIndex = $(this.helper).css("zIndex");
				$(this.helper).css('zIndex', o.zIndex);
			}
			
			that.firstSibling = $(this.element).prev()[0];
				
			$(this.element).css('visibility', 'hidden');
			$(this.element).triggerHandler("sortstart", [e, that.prepareCallbackObj(this)], o.start);
			
			//JU: added a variable for a ugly hack
			this.occurCount = 0;
			
			//JU: reset offset cache (that's the time consumming operation under opera)
			that.offset = [];
			for(var i=0;i<that.set.length;i++)
			{
				that.offset.push($(that.set[i][0]).offset());
			}
			
			return false;
						
		},
		stop: function(that, e) {			
			
			var o = this.options;
			var self = this;
			o.beQuietAtEnd = true;

			if(o.smooth) {
				var os = $(this.element).offset();
				$(this.helper).animate({ left: os.left - o.po.left, top: os.top - o.po.top }, 200, stopIt);
			} else {
				stopIt();
			}
				
			function stopIt() {

				$(self.element).css('visibility', 'visible');
				if(that.helper) that.helper.remove();
				if(self.helper != self.element) $(self.helper).remove(); 

				if(o.ozIndex)
					$(self.helper).css('zIndex', o.ozIndex);
					
					
				//Let's see if the position in DOM has changed
				if($(self.element).prev()[0] != that.firstSibling) {
					$(self.element).triggerHandler("sortupdate", [e, that.prepareCallbackObj(self, that)], o.update);
				}
				$(self.element).triggerHandler("sortstop", [e, that.prepareCallbackObj(self, that)], o.stop);				

			}
			
			return false;
			
		},
		drag: function(that, e) {

			var o = this.options;

			this.pos = [this.pos[0]-(o.cursorAt.left ? o.cursorAt.left : 0), this.pos[1]-(o.cursorAt.top ? o.cursorAt.top : 0)];
			var nl = this.pos[0];
			var nt = this.pos[1];
			
			var widX = this.pos[0]+(this.options.po ? this.options.po.left:0);
			var widY = this.pos[1]+(this.options.po ? this.options.po.top:0);
			
			this.occurCount = 0;
			var moved = false;
			var m = that.set;
			var p = this.pos[1];
			
			for(var i=0;i<m.length;i++)
			{	
				var cio = m[i][0];
				if(this.element.contains(cio)) continue;
				var cO = that.offset[i];

				//verify cursor is within target width
				if(cO.left < widX+cio.offsetWidth/2
				&& cO.left > widX-cio.offsetWidth/2)
				{
					//above
					if(cO.top < widY+cio.offsetHeight/2
					&& cO.top > widY-cio.offsetHeight/2)
					{
						if(o.lba != cio)
						{
							$(cio).before(this.element);
							o.lba = cio;
							moved = true;
						}
					}					
				}	
			}
	
			//reposition helper if needed
			if(moved && that.helper)
			{
				var to = $(this.element).offset();
				that.helper.css({
					top: to.top+'px',
					left: to.left+'px'	
				});
							
				//JU: reset offset cache
				that.offset = [];
				for(var i=0;i<that.set.length;i++)
				{
					that.offset.push($(that.set[i][0]).offset());
				}
			}
			
			$(this.helper).css('left', nl+'px').css('top', nt+'px'); // Stick the helper to the cursor
			return false;
		}
	});

})(jQuery);

