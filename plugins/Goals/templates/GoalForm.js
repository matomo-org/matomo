
function showAddNewGoal()
{
	$("#GoalForm").show();
	$("#EditGoals").hide();
	$.scrollTo("#AddEditGoals", 400);
	return false;
}

function showEditGoals()
{
	$("#EditGoals").show();
	$("#GoalForm").hide();
	$.scrollTo("#AddEditGoals", 400);
	return false;
}

// init the goal form with existing goal value, if any
function initGoalForm(goalMethodAPI, submitText, goalName, matchAttribute, pattern, patternType, caseSensitive, revenue, goalId)
{
	$('#goal_name').val(goalName);
	$('input[@name=match_attribute][value='+matchAttribute+']').attr('checked', true);
	$('#match_attribute_name').html(mappingMatchTypeName[matchAttribute]);
	$('#examples_pattern').html(mappingMatchTypeExamples[matchAttribute]);
	$('option[value='+patternType+']').attr('selected', true);
	$('input[name=pattern]').val(pattern);
	$('#case_sensitive').attr('checked', caseSensitive);
	$('input[name=revenue]').val(revenue);
	$('input[name=methodGoalAPI]').val(goalMethodAPI);
	$('#goal_submit').val(submitText);
	if(goalId != undefined) {
		$('input[name=goalIdUpdate]').val(goalId);
	}
}

function initAndShowAddGoalForm()
{
	initGoalForm('Goals.addGoal', 'Add Goal', '', 'url', '', 'contains', false, '0');
	return showAddNewGoal(); 
}
function bindGoalForm()
{
	$('input[@name=match_attribute]').click( function() {
		var matchTypeId = $(this).attr('value');
		$('#match_attribute_name').html(mappingMatchTypeName[matchTypeId]);
		$('#examples_pattern').html(mappingMatchTypeExamples[matchTypeId]);
	});
	
	$('#goal_submit').click( function() {
		// prepare ajax query to API to add goal
		ajaxRequestAddGoal = getAjaxAddGoal();
		$.ajax( ajaxRequestAddGoal );
		return false;
	});
	
	$('a[name=linkAddNewGoal]').click( function(){ 
		initAndShowAddGoalForm();
	} );
}

function bindListGoalEdit()
{
	$('a[name=linkEditGoal]').click( function() {
		var goalId = $(this).attr('id');
		var goal = piwik.goals[goalId];
		initGoalForm("Goals.updateGoal", "Update Goal", goal.name, goal.match_attribute, goal.pattern, goal.pattern_type, (goal.case_sensitive=='0' ? false : true), goal.revenue, goalId);
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

	$('a[name=linkEditGoals]').click( function(){ 
		return showEditGoals(); 
	} );
}
function getAjaxDeleteGoal(idGoal)
{
	var ajaxRequest = getStandardAjaxConf();
	toggleAjaxLoading();
	
	var parameters = new Object;
	parameters.idSite = piwik.idSite;
 	parameters.idGoal =  idGoal;
 	parameters.method =  'Goals.deleteGoal';
	parameters.module = 'API';
	parameters.format = 'json';
 	parameters.token_auth = piwik.token_auth;
	ajaxRequest.data = parameters;
	return ajaxRequest;
}

function getAjaxAddGoal()
{
	var ajaxRequest = getStandardAjaxConf();
	toggleAjaxLoading();
	
	var parameters = new Object;
	
	parameters.idSite = piwik.idSite;
	parameters.name = encodeURIComponent( $('#goal_name').val() );
	parameters.matchAttribute = $('input[name=match_attribute][checked]').val();
	parameters.patternType = $('[name=pattern_type]').val();
	parameters.pattern = encodeURIComponent( $('input[name=pattern]').val() );
	parameters.caseSensitive = $('#case_sensitive').attr('checked') == true ? 1: 0;
	parameters.revenue = $('input[name=revenue]').val();
	
 	parameters.idGoal =  $('input[name=goalIdUpdate]').val();
 	parameters.method =  $('input[name=methodGoalAPI]').val();
	parameters.module = 'API';
	parameters.format = 'json';
 	parameters.token_auth = piwik.token_auth;
	
	ajaxRequest.data = parameters;
	return ajaxRequest;
}
