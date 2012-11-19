/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var Piwik_Popover = (function() {

	var container = false;
	var isOpen = false;
	var closeCallback = false;

	var createContainer = function() {
		if (container === false) {
			container = $(document.createElement('div')).attr('id', 'Piwik_Popover');
		}
	};
	
	var openPopover = function(title) {
		createContainer();
		
		container.dialog({
			title: title,
			modal: true,
			width: '950px',
			position: ['center', 'center'],
			resizable: false,
			autoOpen: true,
			open: function(event, ui) {
				$('.ui-widget-overlay').on('click.popover', function() {
					container.dialog('close');
				});
			},
			close: function(event, ui) {
				container.find('div.jqplot-target').trigger('piwikDestroyPlot');
				container[0].innerHTML = ''; // IE8 fix
				container.dialog('destroy').remove();
				globalAjaxQueue.abort();
				$('.ui-widget-overlay').off('click.popover');
				isOpen = false;
				broadcast.propagateNewPopoverParameter(false);
				if (typeof closeCallback == 'function') {
					closeCallback();
					closeCallback = false;
				}
			}
		});
		
		isOpen = true;
	};
	
	var centerPopover = function() {
		if (container !== false) {
			container.dialog({position: ['center', 'center']});
		}
	};

	return {

		/**
		 * Open the popover with a loading message
		 *
		 * @param popoverName        string    name of the popover
		 * @param popoverSubject    string    subject of the popover (e.g. url, optional)
		 * @param height            int        height of the popover in px (optional)
		 */
		showLoading: function(popoverName, popoverSubject, height) {
			var loading = $(document.createElement('div')).addClass('Piwik_Popover_Loading');

			var loadingMessage = popoverSubject ? translations.CoreHome_LoadingPopoverFor_js :
				translations.CoreHome_LoadingPopover_js;

			loadingMessage = loadingMessage.replace(/%s/, popoverName);
			
			var p1 = $(document.createElement('p')).addClass('Piwik_Popover_Loading_Name');
			loading.append(p1.text(loadingMessage));

			var p2;
			if (popoverSubject) {
				popoverSubject = piwikHelper.addBreakpointsToUrl(popoverSubject);
				p1.addClass('Piwik_Popover_Loading_NameWithSubject');
				p2 = $(document.createElement('p')).addClass('Piwik_Popover_Loading_Subject');
				loading.append(p2.html(popoverSubject));
			}

			if (height) {
				loading.height(height);
			}

			if (!isOpen) {
				openPopover();
			}
				
			this.setContent(loading);
			this.setTitle('');

			if (height) {
				var offset = loading.height() - p1.outerHeight();
				if (popoverSubject) {
					offset -= p2.outerHeight();
				}
				var spacingEl = $(document.createElement('div'));
				spacingEl.height(Math.round(offset / 2));
				loading.prepend(spacingEl);
			}

			return container;
		},
		
		/** Add a help button to the current popover */
		addHelpButton: function(helpUrl) {
			if (!isOpen) {
				return;
			}
			
			var titlebar = container.parent().find('.ui-dialog-titlebar');
			
			var button = $(document.createElement('a')).addClass('ui-dialog-titlebar-help');
			button.attr({href: helpUrl, target: '_blank'});
			
			titlebar.append(button);
		},

		/** Set the title of the popover */
		setTitle: function(titleHtml) {
			container.dialog({title: titleHtml});
		},
		
		/** Set inner HTML of the popover */
		setContent: function(html) {
			if (typeof closeCallback == 'function') {
				closeCallback();
				closeCallback = false;
			}
			
			container[0].innerHTML = ''; // IE8 fix
			container.html(html);
			centerPopover();
		},
		
		/** Show an error message. All params are HTML! */
		showError: function(title, message, backLabel) {
			var error = $(document.createElement('div')).addClass('Piwik_Popover_Error');
			
			var p = $(document.createElement('p')).addClass('Piwik_Popover_Error_Title');
			error.append(p.html(title));

			if (message) {
				p = $(document.createElement('p')).addClass('Piwik_Popover_Error_Message');
				error.append(p.html(message));
			}
			
			if (backLabel) {
				var back = $(document.createElement('a')).addClass('Piwik_Popover_Error_Back');
				back.attr('href', '#').click(function() {
					history.back();
					return false;
				});
				error.append(back.html(backLabel));
			}

			if (!isOpen) {
				openPopover();
			}
				
			this.setContent(error);
		},
		
		/** Add a callback for the next time the popover is closed or the content changes */
		onClose: function(callback) {
			closeCallback = callback;
		},

		/** Close the popover */
		close: function() {
			if (isOpen) {
				container.dialog('close');
			}
		}

	};

})();

