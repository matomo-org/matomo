<h1>Creating the tables</h1>
{if isset($someTablesInstalled)}
	<div class="warning">Some <span id="linkToggle">Piwik tables</span> are already installed in the DB
	<img src="themes/default/images/warning_medium.png">
	</div>
	<div id="toggle" style="display:none;color:#4F2410"><small><i>The following tables have been found in the database: 
		<br>{$tablesInstalled} </i></small></div>

	<p>Either choose to reuse the existing database tables or select a clean install 
	to erase all existing data in the database.</p>
	
	<p class="nextStep"><a href="{url action=$nextModuleName}">Reuse the existing tables &raquo;</a></p>
	<p class="nextStep" id="eraseAllTables"><a href="{url deleteTables=1}">Delete the detected tables &raquo;</a></p>
				
{/if}

{if isset($existingTablesDeleted)}
	<div class="success"> Existing Piwik tables deleted with success
	<img src="themes/default/images/success_medium.png"></div>
{/if}


{if isset($tablesCreated)}
	<div class="success"> Tables created with success! 
	<img src="themes/default/images/success_medium.png"></div>
{/if}





{literal}
<script>
$(document).ready( function(){
	var strConfirmEraseTables = "Are you sure you want to delete all the Piwik tables from this database?";
	
	// toggle the display of the tables detected during the installation when clicking
	// on the span "linkToggle"
	$("#linkToggle")
		.css("border-bottom","thin dotted #ff5502")
		
		.hover( function() {  
			 	 $(this).css({ cursor: "pointer"}); 
			  	},
			  	function() {  
			 	 $(this).css({ cursor: "auto"}); 
			  	})
		.css("border-bottom","thin dotted #ff5502")
		.click(function(){
			$("#toggle").toggle();} );
			
	$("#eraseAllTables")
		.click( function(){ 
			if(confirm(	strConfirmEraseTables ) ) 
			{ 
				window.location.href = $(this).attr('href'); 
			}
			else 
			{ 
				return false; 
			}
		});
			
	;
});
</script>
{/literal}