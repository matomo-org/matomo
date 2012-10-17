
var Piwik_Insight_Resolutions = (function() {
	
	/** jQuery */
	var $ = jQuery;
	
	/** Info about the screen heights of the visitors */
	var heights = [];
	
	/** Reference to create element function */
	var c;
	
	/** Refernce to translate function */
	var trans;
	
	/** Load screen respolutions */
	function load(callback) {
		Piwik_Insight_Client.api('getResolutions', function(data) {
			var totalVisits = 0;
			
			// Step 1: Build a map with the heights as index.
			// This way, multiple solutions with the same height
			// can be joined.
			var heightsMap = {};
			for (var i = 0; i < data.length; i++) {
				if (data[i].label == 'unknown') {
					continue;
				}
				
				var res = data[i].label.split('x');
				var height = parseInt(res[1], 10);
				data[i].height = height;
				
				if (typeof heightsMap[height] == 'undefined') {
					heightsMap[height] = {
						visits: data[i].nb_visits,
						height: height
					};
				} else {
					heightsMap[height].visits += data[i].nb_visits;
				}
				
				totalVisits += data[i].nb_visits;
			}
			
			// Step 2: Transform the map to a sorted array.
			for (height in heightsMap) {
				heights.push(heightsMap[height]);
			}
			heights.sort(function(a, b) {
				return a.height - b.height;
			});
			
			// Step 3: Derive the number of visits with a height
			// greater or equal.
			for (i = 0; i < heights.length; i++) {
				heights[i].visitsGE = 0;
				for (var j = heights.length - 1; j >= 0; j--) {
					if (heights[j].height >= heights[i].height) {
						heights[i].visitsGE += heights[j].visits;
					} else {
						break;
					}
				}
			}
			
			// Step 4: Calculate the visits rate with a height
			// greater or equal.
			for (i = 0; i < heights.length; i++) {
				heights[i].visitsGERate = round(heights[i].visitsGE / totalVisits);
				heights[i].visitsRate = round(heights[i].visits / totalVisits);
			}
			
			callback();
		});
	}
	
	/** Round a percentage */
	function round(rate) {
		var percentage = rate * 100;
		if (percentage < 10) {
			percentage = Math.round(percentage * 10) / 10;
		} else {
			percentage = Math.round(percentage);
		}
		return percentage;
	}
	
	/** Create overlay that shows the resolutions of the visitors */
	function build(callback) {
		var body = $('body');
		
		// create sidebar
		var sideBar = c('div', '#Resolutions')
		body.prepend(sideBar);
		
		// create overlay
		var screenDiff = 170;
		var overlay = c('div', '#ResolutionsOverlay').height(screenDiff-2);
		overlay.css('opacity', .8).hide();
		body.prepend(overlay);
		
		var overlayText = c('div', '#ResolutionsOverlayText').hide();
		body.prepend(overlayText);
		
		// create items
		var prevHeight = 1;
		for (i = 0; i < heights.length; i++) {
			var height = heights[i];
			var itemHeight = height.height - prevHeight - 2;
			
			var item = c('div', 'Resolution');
			item.css({
				height: itemHeight + 'px',
				top: prevHeight + 'px'
			});
			
			var label = c('div', 'Label').html(height.visitsGERate);
			var labelPos = Math.max(0, itemHeight - 15);
			label.css('top', labelPos + 'px');
			
			item.hover((function(height) {
				return function() {
					var exact = trans('ExactHeight')
							.replace('%s', height.visits)
							.replace('%s', height.visitsRate + '%');
					
					var atLeast = trans('AtLeastHeight')
							.replace('%s', height.visitsGE)
							.replace('%s', height.visitsGERate + '%');
					
					overlayText.html('<b>' + trans('ScreenHeight') + ': ' + height.height
							+ 'px</b><br />' + exact + '<br />' + atLeast + '<br />'
							+ trans('ResolutionOverlay'));
					
					overlay.css('top', (height.height - screenDiff) + 'px').show();
					overlayText.css('top', (height.height - 73) + 'px').show();
				};
			})(height), function() {
				overlay.hide();
				overlayText.hide();
			});
			
			item.css('cursor', 'pointer').click((function(height) {
				return function() {
					window.outerHeight = height - 35;
					return false;
				};
			})(height.height));
			
			item.append(label);
			sideBar.append(item);
			
			prevHeight = height.height + 1;
		}
		
		callback();
	}
	
	return {
		
		initialize: function(finishCallback) {
			c = Piwik_Insight_Client.createElement;
			trans = Piwik_Insight_Translations.get;
			load(function() {
				build(function() {
					finishCallback();
				})
			});
		}
		
	};
	
})();
