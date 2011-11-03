/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function(){
	
	//period widget handler
	var periodWidget={
		show:function(){
			this.isOpen=1;
			$("#periodMore").show();
		},
		hide:function(){
			this.isOpen=0;
			$("#periodMore").hide();
		},
		toggle:function(e){
			if(!this.isOpen) this.show();
			else this.hide();
		}
	};

	$("#periodString #date")
		.hover( function(){
				$(this).css({ cursor: "pointer"});
			}, function(){
			
		})
		.click(function(){
			periodWidget.toggle();
			if($("#periodMore").is(":visible"))
			{
				$("#periodMore .ui-state-highlight").removeClass('ui-state-highlight');
			}
		});
	
	//close periodString onClickOutside
	$('body').on('mouseup',function(e){ 
		if(!$(e.target).parents('#periodString').length && !$(e.target).is('#periodString') && !$(e.target).is('option') && periodWidget.isOpen) {
			periodWidget.hide();
		}
	});
	
} );
