/*
 * Superfish v1.4.1 - jQuery menu widget
 * Copyright (c) 2007 Joel Birch
 *
 * Dual licensed under the MIT and GPL licenses:
 * 	http://www.opensource.org/licenses/mit-license.php
 * 	http://www.gnu.org/licenses/gpl.html
 *
 * CHANGELOG: http://users.tpg.com.au/j_birch/plugins/superfish/changelog.txt
 */

(function($){
	$.superfish = {};
	$.superfish.o = [];
	$.superfish.op = {};
	$.superfish.currentActiveMenu = 'init';
	$.superfish.defaults = {
		hoverClass	: 'sfHover',
		pathClass	: 'overideThisToUse',
		delay		: 800,
		animation	: {opacity:'show'},
		speed		: 'normal',
		oldJquery	: false, /* set to true if using jQuery version below 1.2 */
		disableHI	: true, /* set to true to disable hoverIntent usage */
		// callback functions:
		onInit		: function(){},
		onBeforeShow: function(){},
		onShow		: function(){}, /* note this name changed ('onshow' to 'onShow') from version 1.4 onward */
		onHide		: function(){}
	};
	$.fn.superfish = function(op){
		var bcClass = 'sfbreadcrumb',
			click = function(){
				//console.log('click');
				// no LI means level2 clicked
				if($(this).find('ul li').size() == 0)
				{
					//console.log('clicked sub menu');
					
					// case we clicked the submenu
					$.superfish.currentActiveMenu = $(this).parents('li');
					
					// case we clicked the main menu with NO submenu
					if($.superfish.currentActiveMenu.size() == 0)
					{
						//console.log('clicked main menu with no submenu');
						$.superfish.currentActiveMenu = $(this);
					}
				}
				else
				{
					//console.log('clicked main menu');
					$.superfish.currentActiveMenu = $(this);
				}
				//console.log($.superfish.currentActiveMenu.filter('.sfHover'));
				//console.log($.superfish.currentActiveMenu.filter('.sfHover').html());//each(function(){ $(this).removeClass('sfHover'); });
				$.superfish.currentActiveMenu.parent().find('ul li.sfHover').removeClass('sfHover');
				//console.log('click, the main is '); console.log($.superfish.currentActiveMenu.html() );
	
			},
			over = function(){
				var $$ = $(this), menu = getMenu($$);
				getOpts(menu,true);
				clearTimeout(menu.sfTimer);
				
				$$.showSuperfishUl().siblings().hideSuperfishUl();
			},
			out = function(){
				var $$ = $(this), menu = getMenu($$);
				var o = getOpts(menu,true);
				clearTimeout(menu.sfTimer);
				if (!$$.is('.'+bcClass) ) {
					menu.sfTimer=setTimeout(function(){
						
						// if there is an active menu (a clicked menu)
						if($.superfish.currentActiveMenu != 'init')
						{
							//console.log('showing '); console.log($.superfish.currentActiveMenu.html());
							$.superfish.currentActiveMenu.siblings('.sfHover').removeClass('sfHover');
							$.superfish.currentActiveMenu.showSuperfishUl().siblings().hideSuperfishUl();
						}
						else
						{
							$$.hideSuperfishUl();
						}
						
						//console.log($.superfish.currentActiveMenu);
						if (o.$path.length){over.call(o.$path);}
					},o.delay);
				}		
			},
			getMenu = function($el){ return $el.parents('ul.superfish:first')[0]; },
			getOpts = function(el,menuFound){ el = menuFound ? el : getMenu(el); return $.superfish.op = $.superfish.o[el.serial]; },
			hasUl = function(){ return $.superfish.op.oldJquery ? 'li[ul]' : 'li:has(ul)'; };

		return this.each(function() {
			var s = this.serial = $.superfish.o.length;
			var o = $.extend({},$.superfish.defaults,op);
			o.$path = $('li.'+o.pathClass,this).each(function(){
				$(this).addClass(o.hoverClass+' '+bcClass)
					.filter(hasUl()).removeClass(o.pathClass);
			});
			$.superfish.o[s] = $.superfish.op = o;
			
			$('li',this).click(click);
			
			$(hasUl(),this)[($.fn.hoverIntent && !o.disableHI) ? 'hoverIntent' : 'hover'](over,out)
				
				.not('.'+bcClass)
				.hideSuperfishUl()
				;
			
			
			var $a = $('a',this);
			$a.each(function(i){
				var $li = $a.eq(i).parents('li');
				$a.eq(i).focus(function(){over.call($li);}).blur(function(){out.call($li);});
			});
			
			o.onInit.call(this);
			
		}).addClass('superfish');
	};
	
	$.fn.extend({
		hideSuperfishUl : function(){
			var o = $.superfish.op,
				$ul = $('li.'+o.hoverClass,this).add(this)
					.find('>ul').hide().css('visibility','hidden');
			o.onHide.call($ul);
			return this;
		},
		showSuperfishUl : function(){
			var o = $.superfish.op,
				$ul = this.addClass(o.hoverClass)
					.find('>ul:hidden').css('visibility','visible');
			//console.log(this.html());
			o.onBeforeShow.call($ul);
			$ul.animate(o.animation,o.speed,function(){ o.onShow.call(this); });
			return this;
		}
	});
	
	$(window).unload(function(){
		$('ul.superfish').each(function(){
			$('li',this).unbind('mouseover','mouseout','mouseenter','mouseleave');
		});
	});
})(jQuery);