(function($) {
	
	//If the UI scope is not availalable, add it
	$.ui = $.ui || {};
	
	//Add methods that are vital for all mouse interaction stuff (plugin registering)
	$.extend($.ui, {
		plugin: {
			add: function(w, c, o, p) {
				var a = $.ui[w].prototype; if(!a.plugins[c]) a.plugins[c] = [];
				a.plugins[c].push([o,p]);
			},
			call: function(instance, name, arguments) {
				var c = instance.plugins[name]; if(!c) return;
				var o = instance.interaction ? instance.interaction.options : instance.options;
				var e = instance.interaction ? instance.interaction.element : instance.element;
				
				for (var i = 0; i < c.length; i++) {
					if (o[c[i][0]]) c[i][1].apply(e, arguments);
				}	
			}	
		}
	});
	
	$.fn.mouseInteractionDestroy = function() {
		this.each(function() {
			if($.data(this, "ui-mouse")) $.data(this, "ui-mouse").destroy(); 	
		});
	}
	
	$.ui.mouseInteraction = function(el,o) {
	
		if(!o) var o = {};
		this.element = el;
		$.data(this.element, "ui-mouse", this);
		
		this.options = {};
		$.extend(this.options, o);
		$.extend(this.options, {
			handle : o.handle ? ($(o.handle, el)[0] ? $(o.handle, el) : $(el)) : $(el),
			helper: o.helper || 'original',
			preventionDistance: o.preventionDistance || 0,
			dragPrevention: o.dragPrevention ? o.dragPrevention.toLowerCase().split(',') : ['input','textarea','button','select','option'],
			cursorAt: { top: ((o.cursorAt && o.cursorAt.top) ? o.cursorAt.top : 0), left: ((o.cursorAt && o.cursorAt.left) ? o.cursorAt.left : 0), bottom: ((o.cursorAt && o.cursorAt.bottom) ? o.cursorAt.bottom : 0), right: ((o.cursorAt && o.cursorAt.right) ? o.cursorAt.right : 0) },
			cursorAtIgnore: (!o.cursorAt) ? true : false, //Internal property
			appendTo: o.appendTo || 'parent'			
		})
		o = this.options; //Just Lazyness
		
		if(!this.options.nonDestructive && (o.helper == 'clone' || o.helper == 'original')) {

			// Let's save the margins for better reference
			o.margins = {
				top: parseInt($(el).css('marginTop')) || 0,
				left: parseInt($(el).css('marginLeft')) || 0,
				bottom: parseInt($(el).css('marginBottom')) || 0,
				right: parseInt($(el).css('marginRight')) || 0
			};

			// We have to add margins to our cursorAt
			if(o.cursorAt.top != 0) o.cursorAt.top = o.margins.top;
			if(o.cursorAt.left != 0) o.cursorAt.left += o.margins.left;
			if(o.cursorAt.bottom != 0) o.cursorAt.bottom += o.margins.bottom;
			if(o.cursorAt.right != 0) o.cursorAt.right += o.margins.right;
			
			
			if(o.helper == 'original')
				o.wasPositioned = $(el).css('position');
			
		} else {
			o.margins = { top: 0, left: 0, right: 0, bottom: 0 };
		}
		
		var self = this;
		this.mousedownfunc = function(e) { // Bind the mousedown event
			return self.click.apply(self, [e]);	
		}
		o.handle.bind('mousedown', this.mousedownfunc);
		
		//Prevent selection of text when starting the drag in IE
		if($.browser.msie) $(this.element).attr('unselectable', 'on');
		
	}
	
	$.extend($.ui.mouseInteraction.prototype, {
		plugins: {},
		currentTarget: null,
		lastTarget: null,
		timer: null,
		slowMode: false,
		init: false,
		destroy: function() {
			this.options.handle.unbind('mousedown', this.mousedownfunc);
		},
		trigger: function(e) {
			return this.click.apply(this, arguments);
		},
		click: function(e) {

			var o = this.options;
			
			window.focus();
			if(e.which != 1) return true; //only left click starts dragging
		
			// Prevent execution on defined elements
			var targetName = (e.target) ? e.target.nodeName.toLowerCase() : e.srcElement.nodeName.toLowerCase();
			for(var i=0;i<o.dragPrevention.length;i++) {
				if(targetName == o.dragPrevention[i]) return true;
			}

			//Prevent execution on condition
			if(o.startCondition && !o.startCondition.apply(this, [e])) return true;

			var self = this;
			this.mouseup = function(e) { return self.stop.apply(self, [e]); }
			this.mousemove = function(e) { return self.drag.apply(self, [e]); }

			var initFunc = function() { //This function get's called at bottom or after timeout
				$(document).bind('mouseup', self.mouseup);
				$(document).bind('mousemove', self.mousemove);
				self.opos = [e.pageX,e.pageY]; // Get the original mouse position
			}
			
			if(o.preventionTimeout) { //use prevention timeout
				if(this.timer) clearInterval(this.timer);
				this.timer = setTimeout(function() { initFunc(); }, o.preventionTimeout);
				return false;
			}
		
			initFunc();
			return false;
			
		},
		start: function(e) {
			
			var o = this.options; var a = this.element;
			o.co = $(a).offset(); //get the current offset
				
			this.helper = typeof o.helper == 'function' ? $(o.helper.apply(a, [e,this]))[0] : (o.helper == 'clone' ? $(a).clone()[0] : a);

			if(o.appendTo == 'parent') { // Let's see if we have a positioned parent
				var cp = a.parentNode;
				while (cp) {
					if(cp.style && ($(cp).css('position') == 'relative' || $(cp).css('position') == 'absolute')) {
						o.pp = cp;
						o.po = $(cp).offset();
						o.ppOverflow = !!($(o.pp).css('overflow') == 'auto' || $(o.pp).css('overflow') == 'scroll'); //TODO!
						break;	
					}
					cp = cp.parentNode ? cp.parentNode : null;
				};
				
				if(!o.pp) o.po = { top: 0, left: 0 };
			}
			
			this.pos = [this.opos[0],this.opos[1]]; //Use the relative position
			this.rpos = [this.pos[0],this.pos[1]]; //Save the absolute position
			
			if(o.cursorAtIgnore) { // If we want to pick the element where we clicked, we borrow cursorAt and add margins
				o.cursorAt.left = this.pos[0] - o.co.left + o.margins.left;
				o.cursorAt.top = this.pos[1] - o.co.top + o.margins.top;
			}



			if(o.pp) { // If we have a positioned parent, we pick the draggable relative to it
				this.pos[0] -= o.po.left;
				this.pos[1] -= o.po.top;
			}
			
			this.slowMode = (o.cursorAt && (o.cursorAt.top-o.margins.top > 0 || o.cursorAt.bottom-o.margins.bottom > 0) && (o.cursorAt.left-o.margins.left > 0 || o.cursorAt.right-o.margins.right > 0)) ? true : false; //If cursorAt is within the helper, set slowMode to true
			
			if(!o.nonDestructive) $(this.helper).css('position', 'absolute');
			if(o.helper != 'original') $(this.helper).appendTo((o.appendTo == 'parent' ? a.parentNode : o.appendTo)).show();

			// Remap right/bottom properties for cursorAt to left/top
			if(o.cursorAt.right && !o.cursorAt.left) o.cursorAt.left = this.helper.offsetWidth+o.margins.right+o.margins.left - o.cursorAt.right;
			if(o.cursorAt.bottom && !o.cursorAt.top) o.cursorAt.top = this.helper.offsetHeight+o.margins.top+o.margins.bottom - o.cursorAt.bottom;
		
			this.init = true;	

			if(o._start) o._start.apply(a, [this.helper, this.pos, o.cursorAt, this, e]); // Trigger the start callback
			this.helperSize = { width: outerWidth(this.helper), height: outerHeight(this.helper) }; //Set helper size property
			return false;
						
		},
		stop: function(e) {			
			
			var o = this.options; var a = this.element; var self = this;

			$(document).unbind('mouseup', self.mouseup);
			$(document).unbind('mousemove', self.mousemove);

			if(this.init == false) return this.opos = this.pos = null;
			if(o._beforeStop) o._beforeStop.apply(a, [this.helper, this.pos, o.cursorAt, this, e]);

			if(this.helper != a && !o.beQuietAtEnd) { // Remove helper, if it's not the original node
				$(this.helper).remove(); this.helper = null;
			}
			
			if(!o.beQuietAtEnd) {
				//if(o.wasPositioned)	$(a).css('position', o.wasPositioned);
				if(o._stop) o._stop.apply(a, [this.helper, this.pos, o.cursorAt, this, e]);
			}

			this.init = false;
			this.opos = this.pos = null;
			return false;
			
		},
		drag: function(e) {

			if (!this.opos || ($.browser.msie && !e.button)) return this.stop.apply(this, [e]); // check for IE mouseup when moving into the document again
			var o = this.options;
			
			this.pos = [e.pageX,e.pageY]; //relative mouse position
			if(this.rpos && this.rpos[0] == this.pos[0] && this.rpos[1] == this.pos[1]) return false;
			this.rpos = [this.pos[0],this.pos[1]]; //absolute mouse position
			
			if(o.pp) { //If we have a positioned parent, use a relative position
				this.pos[0] -= o.po.left;
				this.pos[1] -= o.po.top;	
			}
			
			if( (Math.abs(this.rpos[0]-this.opos[0]) > o.preventionDistance || Math.abs(this.rpos[1]-this.opos[1]) > o.preventionDistance) && this.init == false) //If position is more than x pixels from original position, start dragging
				this.start.apply(this,[e]);			
			else {
				if(this.init == false) return false;
			}
		
			if(o._drag) o._drag.apply(this.element, [this.helper, this.pos, o.cursorAt, this, e]);
			return false;
			
		}
	});

	var num = function(el, prop) {
		return parseInt($.css(el.jquery?el[0]:el,prop))||0;
	};
	function outerWidth(el) {
		var $el = $(el), ow = $el.width();
		for (var i = 0, props = ['borderLeftWidth', 'paddingLeft', 'paddingRight', 'borderRightWidth']; i < props.length; i++)
			ow += num($el, props[i]);
		return ow;
	}
	function outerHeight(el) {
		var $el = $(el), oh = $el.width();
		for (var i = 0, props = ['borderTopWidth', 'paddingTop', 'paddingBottom', 'borderBottomWidth']; i < props.length; i++)
			oh += num($el, props[i]);
		return oh;
	}

 })($);
