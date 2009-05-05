$(document).ready( function(){
	$("a[name='evolutionGraph']").each( function() {
		var graph = $(this);
		if(graph && graph.size() > 0) {
			//try to find sparklines and add them clickable behaviour
			$(this).parent().find('div.sparkline').each( function() {
				var url = "";
				//find the sparkline and get it's src attribute
				$("img.sparkline", this).each(function() {
					//search viewDataTable parameter and replace it with value for chart
					var reg = new RegExp("(viewDataTable=sparkline)", "g");
					url = this.src.replace(reg,'viewDataTable=generateDataChartEvolution');
				});
				if(url != ""){
					//on click, reload the graph with the new url
					$(this).click( function() {
						//get the main page graph and reload with new data
						piwikHelper.findSWFGraph(graph.attr('graphId')+"Chart_swf").reload(url);
						piwikHelper.lazyScrollTo(graph[0], 400);
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
});