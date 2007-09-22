$(document).ready( function(){
	var allH2 = new Array;
	var indexAllH2 = 0;
	//hide main loading span
	$('#loadingPiwik').hide();
	
	// foreach each div with a class section we add this DIV ID to the menu array
	$('.section')
		.hide()
		.each( function(){ 
		$(this).prepend("<h2>"+ $(this).attr('id') +"</h2>");
		allH2[indexAllH2++] = $(this).attr('id');
	});
	
	// we generate the HTML of the menu
	var htmlMenu = '';
	for(i in allH2)
	{
		htmlMenu += " <span>" + allH2[i] + "</span>";
		//don't add a separator after last element
		if(i != allH2.length-1)
		{
			htmlMenu += "<small>#</small>";
		}
	}
	
	// we fill the span MENU with the HTML generated above
	$('#generatedMenu')
		.html( htmlMenu )
		.each( function() {
			// then for each element of this menu we add a click event 
			// that shows the associated DIV 
			$('span',this).click( function() {
					$('.section').hide();
					$('#'+$(this).text()).toggle();
				})
		});
		
	// we show the first section by default
	$('.section').slice(0,1).show();
});