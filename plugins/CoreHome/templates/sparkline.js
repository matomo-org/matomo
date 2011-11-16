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
						// get the main page graph and reload with new data
						var chart = $('#'+idDataTable+"Chart");
						// when the metrics picker is used, the id of the data table might be updated (which is correct behavior).
						// for example, in goal reports it might change from GoalsgetEvolutionGraph to GoalsgetEvolutionGraph1. 
						// if this happens, we can't find the graph using $('#'+idDataTable+"Chart");
						// instead, we just use the first evolution graph we can find.
						if (chart.size() == 0) {
							chart = $('div.dataTableGraphEvolutionWrapper div.piwik-graph');
						}
						chart.trigger('showLoading');
						$.get(url, {}, function(data) {
							chart.trigger('replot', data);
						}, 'json');
						piwikHelper.lazyScrollTo(graph[0], 400);
						// set the new clicked column and idGoal in the datatable object
						var sparklineColumn = broadcast.getValueFromUrl('columns', sparklineUrl);
						var idGoal = broadcast.getValueFromUrl('idGoal', sparklineUrl);
						if(dataTables[idDataTable])
						{
							dataTables[idDataTable].setGraphedColumn(sparklineColumn);
							dataTables[idDataTable].param.idGoal = idGoal;
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
