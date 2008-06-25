<h1>{'Installation_Tables'|translate}</h1>
{if isset($someTablesInstalled)}
	<div class="warning">{'Installation_TablesWarning'|translate}
	<img src="themes/default/images/warning_medium.png">
	</div>
	<div id="toggle" style="display:none;color:#4F2410"><small><i>{'Installation_TablesFound'|translate}:
		<br />{$tablesInstalled} </i></small></div>

	<p>{'Installation_TablesWarningHelp'|translate}</p>
	
	<p class="nextStep"><a href="{url action=$nextModuleName}">{'Installation_TablesReuse'|translate} &raquo;</a></p>
	<p class="nextStep" id="eraseAllTables"><a href="{url deleteTables=1}">{'Installation_TablesDelete'|translate} &raquo;</a></p>
				
{/if}

{if isset($existingTablesDeleted)}
	<div class="success"> {'Installation_TablesDeletedSuccess'|translate} 
	<img src="themes/default/images/success_medium.png"></div>
{/if}


{if isset($tablesCreated)}
	<div class="success"> {'Installation_TablesCreatedSuccess'|translate} 
	<img src="themes/default/images/success_medium.png"></div>
{/if}





{literal}
<script>
$(document).ready( function(){
	{/literal}
	var strConfirmEraseTables = "{'Installation_TablesDeleteConfirm'|translate} ";
	{literal}	
	
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
