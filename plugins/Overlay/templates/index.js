var Piwik_Insight = (function() {
	
	var $container, $iframe, $sidebar, $main, $location, $loading; 
	
	var isFullScreen = false;
	
	var iframeDomain = '';
	
	/** Load the sidebar for a url */
	function loadSidebar(currentUrl) {
		$sidebar.hide();
		$location.html('&nbsp;');
		$loading.show();
		
		iframeDomain = currentUrl.match(/http(s)?:\/\/(www\.)?([^\/]*)/i)[3];
		
		piwikHelper.ajaxCall('Insight', 'renderSidebar', {
			currentUrl: currentUrl
		}, function(response) {
			var $response = $(response);
			
			var $responseLocation = $response.find('.Insight_Location');
			var $url = $responseLocation.find('span');
			$url.html(piwikHelper.addBreakpointsToUrl($url.text()));
			$location.html($responseLocation.html()).show();
			$responseLocation.remove();
			
			$location.find('span').hover(function() {
				if (iframeDomain) {
					// use addBreakpointsToUrl because it also encoded html entities
					Piwik_Tooltip.show('<b>' + Piwik_Insight_Translations.domain + ':</b> ' + 
						piwikHelper.addBreakpointsToUrl(iframeDomain), 'Insight_Tooltip');
				}
			}, function() {
				Piwik_Tooltip.hide();
			});
			
			$sidebar.empty().append($response).show();
			$loading.hide();
			
			var $fullScreen = $sidebar.find('a.Insight_FullScreen');
			$fullScreen.click(function() {
				handleFullScreen();
				return false;
			});
		}, 'html');
	}
	
	/** Adjust the height of the iframe */
	function adjustHeight() {
		var height, iframeHeight;
		if (isFullScreen) {
			iframeHeight = height = $(window).height();
		} else {
			height = $(window).height() - $main.offset().top - 25;
			iframeHeight = height - 4;
		}
		height = Math.max(300, height);
		$container.height(height);
		$iframe.height(iframeHeight);
	}
	
	/** Handle full screen */ 
	function handleFullScreen() {
		if (!isFullScreen) {
			// open full screen
			isFullScreen = true;
			$container.addClass('Insight_FullScreen');
			adjustHeight();
		} else {
			// close full screen
			isFullScreen = false;
			$container.removeClass('Insight_FullScreen');
			adjustHeight();
		}
	}
	
	return {
		
		/** This method is called when insight loads (from index.tpl) */
		init: function() {
			$container = $('#Insight_Container');
			$iframe = $container.find('iframe');
			$sidebar = $('#Insight_Sidebar');
			$location = $('#Insight_Location');
			$main = $('#Insight_Main');
			$loading = $('#Insight_Loading');
			
			adjustHeight();
			
			window.setTimeout(function() {
				// sometimes the frame is too high at first
				adjustHeight();
			}, 50);
			
			// this callback is unbound in broadcast.pageload
			$(window).resize(function() {
				adjustHeight();
			});
		},

		/** This callback is used from within the iframe */
		setCurrentUrl: function(currentUrl) {
			// put the current iframe url in the main url to enable refresh and deep linking.
			// to prevent browsers from braking the encoding, we replace the % with a $.
			var urlValue = encodeURIComponent(currentUrl).replace(/%/g, '$');
			
			// the insightUrl parameter is removed when the location changes in broadcast.propagateAjax()
			var urlKeyValue = 'insightUrl=' + urlValue;
			
			var urlOldValue = broadcast.getValueFromHash('insightUrl', window.location.href);
			if (urlOldValue != urlValue) {
				// we don't want the location in the browser history because the back and
				// forward buttons should trigger a change in the iframe.
				// so we use disableHistory = true
				broadcast.propagateAjax(urlKeyValue, true);
			}
			
			// load the sidebar for the current url
			loadSidebar(currentUrl);
		}

	};

})();