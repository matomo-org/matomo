{if isset($onlyShowAddNewGoal)}
    <h2>{'Goals_AddNewGoal'|translate}</h2>
    <p>{'Goals_LearnMoreAboutGoalTrackingDocumentation'|translate:"<a href='misc/redirectToUrl.php?url=http://piwik.org/docs/tracking-goals-web-analytics/' target='_blank'>":"</a>"}
    </p>
{else}
	
    <div class="clear"></div>
	<h2>{'Goals_GoalsManagement'|translate}</h2>
	<div class="entityList">
		<ul class='listCircle'>
			<li><a onclick='' name='linkAddNewGoal'><u>{'Goals_CreateNewGOal'|translate}</u></a></li>
			<li><a onclick='' name='linkEditGoals'>{'Goals_ViewAndEditGoals'|translate}</a></li>
			<li>{'Goals_LearnMoreAboutGoalTrackingDocumentation'|translate:"<a href='misc/redirectToUrl.php?url=http://piwik.org/docs/tracking-goals-web-analytics/' target='_blank'>":"</a>"}</li>
		</ul>
	</div>
	<br/>
{/if}

{ajaxErrorDiv}
{ajaxLoadingDiv id=goalAjaxLoading}
	
<div class="entityContainer">
	{if !isset($onlyShowAddNewGoal)}
		{include file="Goals/templates/list_goal_edit.tpl"}
	{/if}
	{include file="Goals/templates/form_add_goal.tpl"}
	{if !isset($onlyShowAddNewGoal)}
		<div class='entityCancel' style='display:none'>
			{'General_OrCancel'|translate:"<a class='entityCancelLink'>":"</a>"}
		</div>
	{/if}
	<a id='bottom'></a>
</div>
<br/><br/>
{loadJavascriptTranslations plugins='Goals'}
<script type="text/javascript">

var mappingMatchTypeName = {ldelim} 
	"url": "{'Goals_URL'|translate|escape}", 
	"file": "{'Goals_Filename'|translate|escape}", 
	"external_website": "{'Goals_ExternalWebsiteUrl'|translate|escape}" 
{rdelim};
var mappingMatchTypeExamples = {ldelim}
	"url": "{'General_ForExampleShort'|translate} {'Goals_Contains'|translate:"'checkout/confirmation'"|escape} \
		<br />{'General_ForExampleShort'|translate|escape} {'Goals_IsExactly'|translate:"'http://example.com/thank-you.html'"|escape} \
		<br />{'General_ForExampleShort'|translate|escape} {'Goals_MatchesExpression'|translate:"'(.*)\\\/demo\\\/(.*)'"|escape}", 
	"file": "{'General_ForExampleShort'|translate|escape} {'Goals_Contains'|translate:"'files/brochure.pdf'"|escape} \
		<br />{'General_ForExampleShort'|translate|escape} {'Goals_IsExactly'|translate:"'http://example.com/files/brochure.pdf'"|escape} \
		<br />{'General_ForExampleShort'|translate|escape} {'Goals_MatchesExpression'|translate:"'(.*)\\\.zip'"|escape}", 
	"external_website": "{'General_ForExampleShort'|translate|escape} {'Goals_Contains'|translate:"'amazon.com'"|escape} \
		<br />{'General_ForExampleShort'|translate|escape} {'Goals_IsExactly'|translate:"'http://mypartner.com/landing.html'"|escape} \
		<br />{'General_ForExampleShort'|translate|escape} {'Goals_MatchesExpression'|translate:"'http://www.amazon.com\\\/(.*)\\\/yourAffiliateId'"|escape}" 
{rdelim};
bindGoalForm();

{if !isset($onlyShowAddNewGoal)}
piwik.goals = {$goalsJSON};
bindListGoalEdit();
{else}
initAndShowAddGoalForm();
{/if}

</script>
