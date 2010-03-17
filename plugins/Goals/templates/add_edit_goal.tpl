
<div id="AddEditGoals">
{if isset($onlyShowAddNewGoal)}
    <h2>{'Goals_AddNewGoal'|translate}</h2>
{else}
	<h2>
	{'Goals_AddNewGoalOrEditExistingGoal'|translate:"<a onclick='' name='linkAddNewGoal'>+":"</a>":"<a onclick='' name='linkEditGoals'>":"</a>"}
	</h2>
{/if}

	<div>
		<div id="ajaxError" style="display:none"></div>
		<div id="ajaxLoading" style="display:none">
			<div id="loadingPiwik"><img src="themes/default/images/loading-blue.gif" alt="" /> {'General_LoadingData'|translate}</div>
		</div>
	</div>
	
{if !isset($onlyShowAddNewGoal)}
	{include file="Goals/templates/list_goal_edit.tpl"}
{/if}
	{include file="Goals/templates/form_add_goal.tpl"}
	
	<a id='bottom'></a>
</div>

{loadJavascriptTranslations plugins='Goals'}
<script type="text/javascript" src="plugins/Goals/templates/GoalForm.js"></script>
<script language="javascript">

var mappingMatchTypeName = {ldelim} 
	"url": "URL", 
	"file": "filename", 
	"external_website": "external website URL" 
{rdelim};
var mappingMatchTypeExamples = {ldelim}
	"url": "{'General_ForExampleShort'|translate} {'Goals_Contains'|translate:"'checkout/confirmation'"} \
		<br/>{'General_ForExampleShort'|translate} {'Goals_IsExactly'|translate:"'http://example.com/thank-you.html'"} \
		<br/>{'General_ForExampleShort'|translate} {'Goals_MatchesExpression'|translate:"matches the expression '(.*)\\\/demo\\\/(.*)'"}", 
	"file": "{'General_ForExampleShort'|translate} {'Goals_Contains'|translate:"'files/brochure.pdf'"} \
		<br/>{'General_ForExampleShort'|translate} {'Goals_IsExactly'|translate:"'http://example.com/files/brochure.pdf'"} \
		<br/>{'General_ForExampleShort'|translate} {'Goals_MatchesExpression'|translate:"'(.*)\\\.zip'"}", 
	"external_website": "{'General_ForExampleShort'|translate} {'Goals_Contains'|translate:"'amazon.com'"} \
		<br>{'General_ForExampleShort'|translate} {'Goals_IsExactly'|translate:"'http://mypartner.com/landing.html'"} \
		<br>{'General_ForExampleShort'|translate} {'Goals_MatchesExpression'|translate:"'http://www.amazon.com\\\/(.*)\\\/yourAffiliateId'"}" 
{rdelim};
bindGoalForm();

{if !isset($onlyShowAddNewGoal)}
piwik.goals = {$goalsJSON};
bindListGoalEdit();
{else}
initAndShowAddGoalForm();
{/if}

</script>
