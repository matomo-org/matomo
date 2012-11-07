var Piwik_Insight = (function() {
	
	var $container, $iframe, $sidebar, $main, $location; 
	
	var isFullScreen = false;
	
	/** Load the sidebar for a url */
	function loadSidebar(currentUrl) {
		piwikHelper.ajaxCall('Insight', 'renderSidebar', {
			currentUrl: currentUrl
		}, function(response) {
			var $response = $(response);
			
			var $responseLocation = $response.find('.Insight_Location');
			var $url = $responseLocation.find('span');
			$url.html(piwikHelper.addBreakpointsToUrl($url.text()));
			$location.html($responseLocation.html());
			$responseLocation.remove();
			
			$sidebar.empty().append($response);
			
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
			loadSidebar(currentUrl);
		}

	};

})();