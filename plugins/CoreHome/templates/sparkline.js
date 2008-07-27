
$(document).ready( function(){

	//for every section
	$("a[name='evolutionGraph']").each(
		function()
		{
			//try to find the graph
			var graph = $(this);
		
			if(graph && graph.size() > 0)
			{
				//console.log($(this).parent());
				//try to find sparklines and add them clickable behaviour
				$(this).parent().find('p').each(
					function()
					{
						var url = "";
						//find the sparkline and get it's src attribute
						$(".sparkline", this).each(
							function()
							{
								//search viewDataTable parameter and replace it with value for chart
								var reg = new RegExp("(viewDataTable=sparkline)", "g");
								url = this.src.replace(reg,'viewDataTable=generateDataChartEvolution');
							}
						);
						
						if(url != "")
						{
							$("*", this).each(
								function()
								{
									//on click, reload the graph with the new url
									$(this).click(
										function()
										{	
											//get the main page graph and reload with new data
											findSWFGraph(graph.attr('graphId')+"Chart_swf").reload(url);
											lazyScrollTo(graph[0], 400);
										}
									);
									
									//on hover, change cursor to indicate clickable item
									$(this).hover(
										function()
										{  
									 		$(this).css({ cursor: "pointer"}); 
									  	}, function (){}
									);
								}
							);
						}
					}
				);
			}
		}
	);
});
