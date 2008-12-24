
<div id="AddEditGoals">

	<h2><a href='#' name="linkAddNewGoal">+ Add a new Goal</a> 
	or <a href='#' name="linkEditGoals">Edit</a> existing Goals</h2>
	
	<script>
	piwik.goals = {$goalsJSON};
	</script>
	
	<div>
	<div id="ajaxError" style="display:none"></div>
	<div id="ajaxLoading" style="display:none"><div id="loadingPiwik"><img src="themes/default/images/loading-blue.gif" alt="" /> {'General_LoadingData'|translate}</div></div>
	</div>
	
	<span id='EditGoals' style="display:none;">
		<table class="tableForm">
		<thead style="font-weight:bold">
			<td>Id</td>
			<td>Goal Name</td>
			<td>Goal is Triggered when</td>
			<td>Revenue</td>
			<td>Edit</td>
			<td>Delete</td>
		</thead>
		{foreach from=$goals item=goal}
		<tr>
			<td>{$goal.idgoal}</td>
			<td>{$goal.name}</td>
			<td>{$goal.match_attribute}  <br>Pattern {$goal.pattern_type}: {$goal.pattern}</b></td>
			<td>{if $goal.revenue==0}-{else}{$currency}{$goal.revenue}{/if}</td>
			<td><a href='#' name="linkEditGoal" id="{$goal.idgoal}"><img src='plugins/UsersManager/images/edit.png' border=0> Edit</a></td>
			<td><a href='#' name="linkDeleteGoal" id="{$goal.idgoal}"><img src='plugins/UsersManager/images/remove.png' border=0> Delete</a></td>
		</tr>
		{/foreach}
		</table>
	</span>
	
	<span id='GoalForm' style="display:none;">
	<form>
		<table class="tableForm">	
			<tr>
				<td>Goal Name </td>
				<td><input type="text" name="name" value="" id="goal_name" /></td>
			</tr>
			<tr>
				<td>Goal is triggered when visitors:</td>
				<td>
					<input type="radio" onclick="" checked="true" id="match_attribute_url" value="url" name="match_attribute"/>
					<label for="match_attribute_url">Visit a given URL (page or group of pages)</label>
					<br>
					<input type="radio" onclick="" id="match_attribute_file" value="file" name="match_attribute"/>
					<label for="match_attribute_file">Download a file</label>
					<br>
					<input type="radio" onclick="" id="match_attribute_external_website" value="external_website" name="match_attribute"/>
					<label for="match_attribute_external_website">Click on a Link to an external website </label>
				</td>
			</tr>
			<tr>
				<td>where the <span id="match_attribute_name"></span></td>
				<td>
					<select name="pattern_type">
						<option value="contains">contains</option>
						<option value="exact">is exactly</option>
						<option value="regex">matches the expression</option>
					</select>
					
					<input type="text" name="pattern" value=""/>
					<br>
					<div id="examples_pattern"></div>
				</td>
			</tr>
			<tr>
				<td>(optional) Goal default value is </td>
				<td>{$currency} <input type="text" name="revenue" size="1" value="0"/></td>
			</tr>
			<tr>
				<td colspan="2" style="border:0">
				<div class="submit">	
					<input type="hidden" name="methodGoalAPI" value="">	
					<input type="hidden" name="goalIdUpdate" value="">
					<input type="submit" value="Add Goal" name="submit" id="goal_submit" class="submit" />
				</div>
				</td>
			</tr>
		</table>
	</form>
	</span>
	
	<a id='bottom'></a>
</div>

{literal}
<style>
#examples_pattern {
	color:#9B9B9B;
}
</style>
<script type="text/javascript" src="plugins/Goals/templates/GoalForm.js"></script>
<script language="javascript">
var mappingMatchTypeName = { 
	"url": "URL", 
	"file": "filename", 
	"external_website": "external website URL" 
};
var mappingMatchTypeExamples = { 
	"url": "eg. contains 'checkout/confirmation'<br>eg. is exactly 'http://example.com/thank-you.html'<br>eg. matches the expression '[.*]\\\/demo\\\/[.*]'", 
	"file": "eg. contains 'files/brochure.pdf'<br>eg. is exactly 'http://example.com/files/brochure.pdf'<br>eg. matches the expression '[.*]\\\.zip'", 
	"external_website": "eg. contains 'amazon.com'<br>eg. is exactly 'http://mypartner.com/landing.html'<br>eg. matches the expression 'http://www.amazon.com\\\/[.*]\\\/yourAffiliateId'" 
};

$('a[name=linkEditGoal]').click( function() {
	var goalId = $(this).attr('id');
	var goal = piwik.goals[goalId];
	initGoalForm("Goals.updateGoal", "Update Goal", goal.name, goal.match_attribute, goal.pattern, goal.pattern_type, goal.revenue, goalId);
	showAddNewGoal();
	return false;
});

$('a[name=linkDeleteGoal]').click( function() {
	var goalId = $(this).attr('id');
	var goalName = 'test goal';//piwik.goals[goalId][name]
	if(confirm(sprintf('Are you sure you want to delete the Goal %s?','"'+goalName+'"')))
	{
		$.ajax( getAjaxDeleteGoal( goalId ) );
		return false;
	}
});

bindGoalForm();

$('a[name=linkAddNewGoal]').click( function(){ 
	initGoalForm('Goals.addGoal', 'Add Goal', '', 'url', '', 'contains', '0');
	return showAddNewGoal(); 
} );
$('a[name=linkEditGoals]').click( function(){ 
	return showEditGoals(); 
} );

</script>

{/literal}
