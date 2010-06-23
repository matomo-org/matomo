
<div id="AddEditGoals">
{if isset($onlyShowAddNewGoal)}
	<h2>Add a new Goal</h2>
{else}
	<h2><a onclick='' name="linkAddNewGoal">+ Add a new Goal</a> 
	or <a onclick='' name="linkEditGoals">Edit</a> existing Goals</h2>
{/if}

	<div>
	<div id="ajaxError" style="display:none"></div>
	<div id="ajaxLoading" style="display:none"><div id="loadingPiwik"><img src="themes/default/images/loading-blue.gif" alt="" /> {'General_LoadingData'|translate}</div></div>
	</div>
	
{if !isset($onlyShowAddNewGoal)}
	{include file="Goals/templates/list_goal_edit.tpl"}
{/if}
	{include file="Goals/templates/form_add_goal.tpl"}
	
	<a id='bottom'></a>
</div>

{literal}
<script type="text/javascript" src="plugins/Goals/templates/GoalForm.js"></script>
<script language="javascript">

var mappingMatchTypeName = { 
	"url": "URL", 
	"file": "filename", 
	"external_website": "external website URL" 
};
var mappingMatchTypeExamples = { 
	"url": "eg. contains 'checkout/confirmation'<br>eg. is exactly 'http://example.com/thank-you.html'<br>eg. matches the expression '(.*)\\\/demo\\\/(.*)'", 
	"file": "eg. contains 'files/brochure.pdf'<br>eg. is exactly 'http://example.com/files/brochure.pdf'<br>eg. matches the expression '(.*)\\\.zip'", 
	"external_website": "eg. contains 'amazon.com'<br>eg. is exactly 'http://mypartner.com/landing.html'<br>eg. matches the expression 'http://www.amazon.com\\\/(.*)\\\/yourAffiliateId'" 
};

bindGoalForm();

{/literal}

{if !isset($onlyShowAddNewGoal)}
piwik.goals = {$goalsJSON};
bindListGoalEdit();
{else}
initAndShowAddGoalForm();
{/if}

</script>
