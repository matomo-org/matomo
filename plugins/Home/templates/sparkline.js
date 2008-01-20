
$(document).ready( function(){

	//find paragraph in the div 'Visits_summary'
	$("#Visits_summary p").each(
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
								findSWFGraph("getLastVisitsGraphChart_swf").reload(url);
								//slowly scroll the page to the graph
								var targetOffset = $("#Visits_summary a[name='evolutionGraph']").offset().top;
								$('html,body').animate({scrollTop: targetOffset}, 500);					
							}
						);
						
						//on hover, change cursor to indicate clickable item
						$(this).hover(
							function()
							{  
						 		$(this).css({ cursor: "pointer"}); 
						  	},
						  	function()
						  	{  
						 		$(this).css({ cursor: "auto"}); 
						  	}
						);
					}
				);
			}
		}
	);
});
