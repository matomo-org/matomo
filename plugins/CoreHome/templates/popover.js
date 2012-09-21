/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var Piwik_Popover = (function() {

	var container = $(document.createElement('div')).attr('id', 'Piwik_Popover');
	var isOpen = false;
	var preparedContent = false;

	var openPopover = function(title) {
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
				container.dialog('destroy').remove();
				piwikHelper.abortQueueAjax();
				$('.ui-widget-overlay').off('click.popover');
				isOpen = false;
			}
		});
		
		isOpen = true;
	};
	
	var centerPopover = function() {
		container.dialog({position: ['center', 'center']});
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
				p1.addClass('Piwik_Popover_Loading_NameWithSubject');
				p2 = $(document.createElement('p')).addClass('Piwik_Popover_Loading_Subject');
				loading.append(p2.text(popoverSubject));
			}

			if (height) {
				loading.height(height);
			}

			if (!isOpen) {
				openPopover();
			}
				
			this.setContent(loading);

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

		/**
		 * Prepare the popover
		 * 
		 * Adds the html to the DOM but hides it. Plugins can then do further processing while the
		 * loading message is still shown and use showPreparedContent() when ready.
		 * 
		 * @param html
		 */
		prepareContent: function(html) {
			preparedContent = $(document.createElement('div'));
			preparedContent.html(html).css('display', 'none');
			container.append(preparedContent);
		},
		
		/**
		 * Show the content that was previously passed to prepareContent().
		 * Hides the loading message.
		 */
		showPreparedContent: function() {
			container.find('.Piwik_Popover_Loading').remove();
			preparedContent.show();
			centerPopover();
		},

		/** Set inner HTML of the popover */
		setContent: function(html) {
			container.html(html);
			centerPopover();
		},

		/** Close the popover */
		close: function() {
			container.dialog('close');
		}

	};

})();

