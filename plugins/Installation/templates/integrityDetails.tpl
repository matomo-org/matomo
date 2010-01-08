{if !isset($warningMessages)}
{assign var=warningMessages value=$infos.integrityErrorMessages}
{/if}
<div id="integrity-results" title="{'Installation_SystemCheckFileIntegrity'|translate}" style="display:none; font-size: 62.5%;">
	<table>
	{foreach from=$warningMessages item=msg}
		<tr><td>{$msg}</td></tr>
	{/foreach}
	</table>
</div>
<script type="text/javascript">
{literal}<!--
$(function() {
	$("#integrity-results").dialog({
		bgiframe: true,
		modal: true,
		autoOpen: false,
		width: 600,
		buttons: {
			Ok: function() {
			$(this).dialog('close');
			}
		}
	});
});
$('#more-results').click(function() {
	$('#integrity-results').dialog('open');
})
.hover(
	function(){ 
		$(this).addClass("ui-state-hover"); 
	},
	function(){ 
		$(this).removeClass("ui-state-hover"); 
	}
).mousedown(function(){
	$(this).addClass("ui-state-active"); 
})
.mouseup(function(){
		$(this).removeClass("ui-state-active");
});
//-->{/literal}
</script>
