$(document).ready(function(){
	$("#periodString").hide();
	$("#otherPeriods").hide();
	$("#calendar").hide();
	$("#periodString").show();
	
	// we get the content of the div before modifying it (append image, etc.)
	// so we can restore its value when we want
	var savedCurrentPeriod = $("#periodString #currentPeriod").html();

	// timeout used to fadeout the menu
	var timeout = null;
	var timeoutLength;

	// restore the normal style of the current period type eg "DAY"	
	function restoreCurrentPeriod()
	{
		$("#currentPeriod")
			.removeClass("hoverPeriod")
			.html(savedCurrentPeriod);
	}
	// remove the sub menu created that contains the other periods availble
	// eg. week | month | year
	function removePeriodMenu() {
		$("#otherPeriods").fadeOut('fast');
		setCurrentPeriodStyle = true;
	}
	
	// state machine a bit complex and was hard to come up with 
	// there should be a simpler way to do it with jquery...
	// if set to true, means that we want to style our current period
	// for example add bold and append the image
	var setCurrentPeriodStyle = true;

	$("#periodString #periods")
			.hover(function(){
				$(this).css({ cursor: "pointer"});
				
				// cancel the timeout
				// indeed if the user goes away of the div and goes back on
				// we don't hide the submenu!
				if(timeout != null)
				{
					clearTimeout(timeout);
					timeout = null;
					timeoutLength = 500;
				}
				else
				{
					timeoutLength = 0;
					setCurrentPeriodStyle = true;
				}
				if( setCurrentPeriodStyle == true) 
				{
					$("#currentPeriod:not(.hoverPeriod)")
						.addClass("hoverPeriod")
						.append('&nbsp;<img src="plugins/Home/templates/images/more_period.gif" style="vertical-align:middle">');
				}
			}, function(){
				restoreCurrentPeriod();
				// we callback the function to hide the sub menu
				// only if it was visible (otherwise it messes the state machine)
				if($("#otherPeriods").is(":visible"))
				{
					timeout = setTimeout( removePeriodMenu , timeoutLength);
				}
				setCurrentPeriodStyle = false;
			})
			.click( function() {
				// we restore the initial style on the DAY link
				restoreCurrentPeriod();
				// the menu shall fadeout after 500ms
				timeoutLength = 500;
				// appearance!
				$("#otherPeriods").fadeIn();

			}
	);

	$("#periodString #date")
		.hover( function(){
				$(this).css({ cursor: "pointer"});
			}, function(){
			
		})
		.click(function(){
			$("#calendar").toggle();
		});
} );