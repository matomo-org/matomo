/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var Piwik_Overlay = (function() {

	var $body, $iframe, $sidebar, $main, $location, $loading, $errorNotLoading;
	var $rowEvolutionLink, $transitionsLink, $fullScreenLink;

	var idSite, period, date;

	var errorTimeout = false;

	var iframeSrcBase;
	var iframeDomain = '';
	var iframeCurrentPage = '';
	var iframeCurrentPageNormalized = '';
	var iframeCurrentActionLabel = '';
	var updateComesFromInsideFrame = false;


	/** Load the sidebar for a url */
	function loadSidebar(currentUrl) {
		showLoading();

		$location.html('&nbsp;').unbind('mouseenter').unbind('mouseleave');

		iframeCurrentPage = currentUrl;
		iframeDomain = currentUrl.match(/http(s)?:\/\/(www\.)?([^\/]*)/i)[3];

		piwikHelper.abortQueueAjax();
		piwikHelper.ajaxCall('Overlay', 'renderSidebar', {
			currentUrl: currentUrl
		}, function(response) {
			hideLoading();

			var $response = $(response);

			var $responseLocation = $response.find('.Overlay_Location');
			var $url = $responseLocation.find('span');
			iframeCurrentPageNormalized = $url.data('normalizedUrl');
			iframeCurrentActionLabel = $url.data('label');
			$url.html(piwikHelper.addBreakpointsToUrl($url.text()));
			$location.html($responseLocation.html()).show();
			$responseLocation.remove();

			$location.find('span').hover(function() {
				if (iframeDomain) {
					// use addBreakpointsToUrl because it also encoded html entities
					Piwik_Tooltip.show('<b>' + Piwik_Overlay_Translations.domain + ':</b> ' +
						piwikHelper.addBreakpointsToUrl(iframeDomain), 'Overlay_Tooltip');
				}
			}, function() {
				Piwik_Tooltip.hide();
			});

			$sidebar.empty().append($response).show();
			
			if ($sidebar.find('.Overlay_NoData').size() == 0) {
				$rowEvolutionLink.show();
				$transitionsLink.show()
			}
		}, 'html');
	}

	/** Adjust the dimensions of the iframe */
	function adjustDimensions() {
		$iframe.height($(window).height());
		$iframe.width($body.width() - $iframe.offset().left - 2); // -2 because of 2px border
	}

	/** Display the loading message and hide other containers */
	function showLoading() {
		$loading.show();

		$sidebar.hide();
		$location.hide();

		$fullScreenLink.hide();
		$rowEvolutionLink.hide();
		$transitionsLink.hide();

		$errorNotLoading.hide();

		// Start a timeout that shows an error when nothing is loaded
		if (errorTimeout) {
			window.clearTimeout(errorTimeout);
		}
		errorTimeout = window.setTimeout(function() {
			hideLoading();
			$errorNotLoading.show();
		}, 9000);
	}

	/** Hide the loading message */
	function hideLoading() {
		if (errorTimeout) {
			window.clearTimeout(errorTimeout);
			errorTimeout = false;
		}
		$loading.hide();
		$fullScreenLink.show();
	}

	/** $.history callback for hash change */
	function hashChangeCallback(currentUrl) {
		if (!updateComesFromInsideFrame) {
			var iframeUrl = iframeSrcBase;
			if (currentUrl) {
				iframeUrl += '#' + Overlay_Helper.decodeFrameUrl(currentUrl);
			}
			$iframe.attr('src', iframeUrl);
			showLoading();
		} else {
			loadSidebar(currentUrl);
		}

		updateComesFromInsideFrame = false;
	}

	return {

		/** This method is called when Overlay loads (from index.tpl) */
		init: function(iframeSrc, pIdSite, pPeriod, pDate) {
			iframeSrcBase = iframeSrc;
			idSite = pIdSite;
			period = pPeriod;
			date = pDate;

			$body = $('body');
			$iframe = $('#Overlay_Iframe');
			$sidebar = $('#Overlay_Sidebar');
			$location = $('#Overlay_Location');
			$main = $('#Overlay_Main');
			$loading = $('#Overlay_Loading');
			$errorNotLoading = $('#Overlay_Error_NotLoading');

			$rowEvolutionLink = $('#Overlay_RowEvolution');
			$transitionsLink = $('#Overlay_Transitions');
			$fullScreenLink = $('#Overlay_FullScreen');

			adjustDimensions();

			showLoading();

			window.setTimeout(function() {
				// sometimes the frame is too high at first
				adjustDimensions();
			}, 50);

			// handle window resize
			$(window).resize(function() {
				adjustDimensions();
			});

			// handle hash change
			$.history.init(hashChangeCallback, {unescape: true});

			// handle date selection
			var $select = $('select#Overlay_DateRangeSelect').change(function() {
				var parts = $(this).val().split(';');
				if (parts.length == 2) {
					period = parts[0];
					date = parts[1];
					window.location.href = Overlay_Helper.getOverlayLink(idSite, period, date, iframeCurrentPage);
				}
			});

			var optionMatchFound = false;
			$select.find('option').each(function() {
				if ($(this).val() == period + ';' + date) {
					$(this).attr('selected', 'selected');
					optionMatchFound = true;
				}
			});

			if (!optionMatchFound) {
				$select.prepend('<option selected="selected">');
			}

			// handle transitions link
			$transitionsLink.click(function() {
				var transitions = new Piwik_Transitions('url', iframeCurrentPageNormalized, null);
				transitions.showPopover();
				return false;
			});

			// handle row evolution link
			$rowEvolutionLink.click(function() {
				var rowEvolution = new DataTable_RowActions_RowEvolution(null);
				rowEvolution.showRowEvolution('Actions.getPageUrls', iframeCurrentActionLabel, '0');
				return false;
			});

			// handle full screen link
			$fullScreenLink.click(function() {
				var href = iframeSrcBase;
				if (iframeCurrentPage) {
					href += '#' + iframeCurrentPage.replace(/#/g, '%23');
				}
				window.location.href = href;
				return false;
			});
		},

		/** This callback is used from within the iframe */
		setCurrentUrl: function(currentUrl) {
			showLoading();

			// put the current iframe url in the main url to enable refresh and deep linking.
			var location = window.location.href;
			var newLocation = location.split('#')[0] + '#' + Overlay_Helper.encodeFrameUrl(currentUrl);

			// location.replace() changes the current url without pushing on the browsers history
			// stack. this way, the back and forward buttons can be used on the iframe, which in
			// turn notifies the parent about the location change.
			if (newLocation != location) {
				updateComesFromInsideFrame = true;
				window.location.replace(newLocation);
			}

			// load the sidebar for the current url
			loadSidebar(currentUrl);
		}

	};

})();