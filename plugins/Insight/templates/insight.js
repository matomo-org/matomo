var Piwik_Insight = (function() {
	
	var $container, $sidebar, $main, $location; 
	
	/** Load the sidebar for a url */
	function loadSidebar(currentUrl) {
		$.get('index.php', {
			module: 'Insight',
			action: 'renderSidebar',
			idSite: piwik.idSite,
			period: piwik.period,
			date: piwik.currentDateString,
			currentUrl: currentUrl
		}, function(response) {
			var $response = $(response);
			var $responseLocation = $response.find('.Insight_Location');
			$location.html($responseLocation.html());
			$responseLocation.remove();
			$sidebar.empty().append($response);
		});
	}
	
	/** Adjust the height of the iframe */
	function adjustHeight() {
		var height = $(window).height() - $main.offset().top - 45;
		height = Math.max(300, height);
		$container.height(height);
	}
	
	return {
		
		/** This method is called when insight loads (from index.tpl) */
		init: function() {
			$container = $('#Insight_Container');
			$sidebar = $('#Insight_Sidebar');
			$location = $('#Insight_Location');
			$main = $('#Insight_Main');
			
			$main.hide();
			window.setTimeout(function() {
				$main.show();
				adjustHeight();
			}, 2000);
			
			// TODO: unbind the callback
			// use events of piwik navigation
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