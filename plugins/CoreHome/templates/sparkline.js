/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function initializeSparklines () {
	$("a[name='evolutionGraph']").each( function() {
		var graph = $(this);
		if(graph && graph.size() > 0) {
			//try to find sparklines and add them clickable behaviour
			$(this).parent().find('div.sparkline').each( function() {
				var url = "";
				var sparklineUrl = '';
				//find the sparkline and get it's src attribute
				$("img.sparkline", this).each(function() {
					//search viewDataTable parameter and replace it with value for chart
					var reg = new RegExp("(viewDataTable=sparkline)", "g");
					sparklineUrl = this.src;
					url = sparklineUrl.replace(reg,'viewDataTable=generateDataChartEvolution');
				});
				if(url != ""){
					//on click, reload the graph with the new url
					$(this).click( function() {
						var idDataTable = graph.attr('graphId');
						//get the main page graph and reload with new data
						var chart = $('#'+idDataTable+"Chart");
						var loading = $(document.createElement('div')).addClass('jqplot-loading');
						loading.css({
							width: chart.innerWidth()+'px',
							height: chart.innerHeight()+'px',
							opacity: 0
						});
						chart.prepend(loading);
						loading.css({opacity: .7});
						$.get(url, {}, function(data) {
							chart.trigger('replot', data);
						}, 'json');
						piwikHelper.lazyScrollTo(graph[0], 400);
						// Set the new clicked column in the datatable object
						var sparklineColumn = broadcast.getValueFromUrl('columns', sparklineUrl);
						if(dataTables[idDataTable])
						{
							dataTables[idDataTable].setGraphedColumn(sparklineColumn);
						}
					});
					$(this).hover( 	
						function() { 
							$(this).css({
										"cursor": "pointer", 
										"border-bottom": "1px dashed #C3C3C3"
									});
						}, 
						function(){
							$(this).css({"border-bottom":"1px solid white"});
						}
					);
				}
			});
		}
	});
}
