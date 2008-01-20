
$(document).ready( function(){

	//find object of class 'sparkline' in the div 'Visits_summary'
	$("#Visits_summary .sparkline").each(
		function()
		{
			//on click, get the url of the image, modify it and reload the graph
			$(this).click(
				function()
				{		
					//search viewDataTable parameter and replace it with value for chart
					var reg = new RegExp("(viewDataTable=sparkline)", "g");
					var url = this.src.replace(reg,'viewDataTable=generateDataChartEvolution');
					
					//get the main page graph and reload with new data
					findSWFGraph("getLastVisitsGraphChart_swf").reload(url);
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
});
