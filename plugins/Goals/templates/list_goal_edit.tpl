<div id='entityEditContainer' style="display:none;">
	<table class="dataTable entityTable">
	<thead>
	<tr>
		<th class="first">Id</th>
        <th>{'Goals_GoalName'|translate}</th>
        <th>{'Goals_GoalIsTriggeredWhen'|translate}</th>
        <th>{'Goals_ColumnRevenue'|translate}</th>
        <th>{'General_Edit'|translate}</th>
        <th>{'General_Delete'|translate}</th>
	</tr>
	</thead>
	{foreach from=$goals item=goal}
	<tr>
		<td class="first">{$goal.idgoal}</td>
		<td>{$goal.name}</td>
        <td><span class='matchAttribute'>{$goal.match_attribute}</span> {if isset($goal.pattern_type)}<br />{'Goals_Pattern'|translate} {$goal.pattern_type}: {$goal.pattern}</b>{/if}</td>
		<td>{if $goal.revenue==0}-{else}{$goal.revenue|money:$idSite}{/if}</td>
		<td><a href='#' name="linkEditGoal" id="{$goal.idgoal}" class="link_but"><img src='themes/default/images/ico_edit.png' border="0" /> {'General_Edit'|translate}</a></td>
		<td><a href='#' name="linkDeleteGoal" id="{$goal.idgoal}" class="link_but"><img src='themes/default/images/ico_delete.png' border="0" /> {'General_Delete'|translate}</a></td>
	</tr>
	{/foreach}
	</table>
</div>

<div class="ui-confirm" id="confirm">
    <h2></h2>
    <input role="yes" type="button" value="{'General_Yes'|translate}" />
    <input role="no" type="button" value="{'General_No'|translate}" />
</div> 

<script type="text/javascript">
var goalTypeToTranslation = {ldelim}
    "manually" : "{'Goals_ManuallyTriggeredUsingJavascriptFunction'|translate}",
    "file" : "{'Goals_Download'|translate}",
    "url" : "{'Goals_VisitUrl'|translate}",
    "title" : "{'Goals_VisitPageTitle'|translate}",
    "external_website" : "{'Goals_ClickOutlink'|translate}"
{rdelim}
{literal}
$(document).ready( function() {	
	// translation of the goal "match attribute" to human readable description
	$('.matchAttribute').each( function() {
		matchAttribute = $(this).text();
		translation = goalTypeToTranslation[matchAttribute];
		$(this).text(translation);
	});
} );
{/literal}
</script>
