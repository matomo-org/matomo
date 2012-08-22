/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var Piwik_Tooltip = (function() {
	
	var domElement = false;
	var visible = false;
	var addedClass = false;
	var topOffset = 15;
	
	/** Create and initialize the tooltip */
	var initialize = function() {
		domElement = $(document.createElement('div'));
		domElement.addClass('piwik-tooltip');
		$('body').prepend(domElement);
		
		$(document).mousemove(function(e) {
			if (visible) {
				var tipWidth = domElement.outerWidth();
				var maxX = $('body').innerWidth() - tipWidth - 25;
				if (e.pageX < maxX) {
					// tooltip right of mouse
					domElement.css({
						top: (e.pageY - topOffset) + "px",
						left: (e.pageX + 15) + "px"
					});
				}
				else {
					// tooltip left of mouse
					domElement.css({
						top: (e.pageY - topOffset) + "px",
						left: (e.pageX - 15 - tipWidth) + "px"
					});
				}
			}
		});
	};
	
	return {
		
		/** Show the tooltip with HTML content. */
		show: function(html, addClass, maxWidth) {
			if (domElement === false) {
				initialize();
			}
			
			if (visible && addedClass != addClass) {
				domElement.removeClass(addedClass);
			} else {
				visible = true;
				$(document).trigger('mousemove');
				domElement.show();
			}
			
			if (addClass && addedClass != addClass) {
				addedClass = addClass;
				domElement.addClass(addClass);
			}
			
			domElement.css({width: 'auto'});
			domElement.html(html);
			if (domElement.outerWidth() > maxWidth) {
				domElement.css({width: maxWidth + 'px'});
			}
			
			if (domElement.outerHeight() < 25) {
				topOffset = 5;
			} else {
				topOffset = 15;
			}
		},
		
		/** Show the tooltip with title/text content. */
		showWithTitle: function(title, text, addClass) {
			var html = '<span class="tip-title">' + title + '</span><br />' + text;
			this.show(html, addClass);
		},
		
		/** Hide the tooltip */
		hide: function() {
			if (domElement !== false) {
				domElement.hide();
			}
			
			if (addedClass) {
				domElement.removeClass(addedClass);
				addedClass = false;
			}
			
			visible = false;
		}
		
	};
	
})();

