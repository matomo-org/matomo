/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function showAddNewGoal()
{
	hideForms();
	$(".entityAddContainer").show();
	showCancel();
	piwikHelper.lazyScrollTo(".entityContainer", 400);
	return false;
}

function showEditGoals()
{
	hideForms();
	$("#entityEditContainer").show();
	showCancel();
	piwikHelper.lazyScrollTo(".entityContainer", 400);
	return false;
}

function hideForms()
{
	$(".entityAddContainer").hide();
	$("#entityEditContainer").hide();
}

function showCancel()
{
	$(".entityCancel").show();
	$('.entityCancelLink').click( function(){
		hideForms();
		$(".entityCancel").hide();
	});
}

// init the goal form with existing goal value, if any
function initGoalForm(goalMethodAPI, submitText, goalName, matchAttribute, pattern, patternType, caseSensitive, revenue, allowMultiple, goalId)
{
	$('#goal_name').val(goalName);
	if(matchAttribute == 'manually') {
		$('select[name=trigger_type] option[value=manually]').prop('selected', true);
		$('input[name=match_attribute]').prop('disabled', true);
		$('#match_attribute_section').hide();
		$('#manual_trigger_section').show();
		matchAttribute = 'url';
	} else {
		$('select[name=trigger_type] option[value=visitors]').prop('selected', true);
	}
	$('input[name=match_attribute][value='+matchAttribute+']').prop('checked', true);
	$('input[name=allow_multiple][value='+allowMultiple+']').prop('checked', true);
	$('#match_attribute_name').html(mappingMatchTypeName[matchAttribute]);
	$('#examples_pattern').html(mappingMatchTypeExamples[matchAttribute]);
	$('select[name=pattern_type] option[value='+patternType+']').prop('selected', true);
	$('input[name=pattern]').val(pattern);
	$('#case_sensitive').prop('checked', caseSensitive);
	$('input[name=revenue]').val(revenue);
	$('input[name=methodGoalAPI]').val(goalMethodAPI);
	$('#goal_submit').val(submitText);
	if(goalId != undefined) {
		$('input[name=goalIdUpdate]').val(goalId);
	}
}


function bindGoalForm()
{
	$('select[name=trigger_type]').click( function() {
		var triggerTypeId = $(this).val();
		if(triggerTypeId == "manually") {
			$('input[name=match_attribute]').prop('disabled', true);
			$('#match_attribute_section').hide();
			$('#manual_trigger_section').show();
		} else {
			$('input[name=match_attribute]').removeProp('disabled');
			$('#match_attribute_section').show();
			$('#manual_trigger_section').hide();
		}
	});

	$('input[name=match_attribute]').click( function() {
		var matchTypeId = $(this).val();
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
		piwikHelper.lazyScrollTo('#goal_name');
	} );
}

function getAjaxDeleteGoal(idGoal)
{
	var ajaxRequest = piwikHelper.getStandardAjaxConf('goalAjaxLoading');
	piwikHelper.lazyScrollTo(".entityContainer", 400);
	
	var parameters = {};
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
	var ajaxRequest = piwikHelper.getStandardAjaxConf('goalAjaxLoading');
	piwikHelper.lazyScrollTo(".entityContainer", 400);
	var parameters = {};
	
	parameters.idSite = piwik.idSite;
	parameters.name = encodeURIComponent( $('#goal_name').val() );
	
	if($('[name=trigger_type]').val() == 'manually') {
		parameters.matchAttribute = 'manually';
		parameters.patternType = 'regex';
		parameters.pattern = '.*';
		parameters.caseSensitive = 0;
	} else {
		parameters.matchAttribute = $('input[name=match_attribute]:checked').val();
		parameters.patternType = $('[name=pattern_type]').val();
		parameters.pattern = encodeURIComponent( $('input[name=pattern]').val() );
		parameters.caseSensitive = $('#case_sensitive').prop('checked') == true ? 1: 0;
	}
	parameters.revenue = $('input[name=revenue]').val();
	parameters.allowMultipleConversionsPerVisit = $('input[name=allow_multiple]:checked').val() == true ? 1: 0;
	
	parameters.idGoal =  $('input[name=goalIdUpdate]').val();
	parameters.method =  $('input[name=methodGoalAPI]').val();
	parameters.module = 'API';
	parameters.format = 'json';
	parameters.token_auth = piwik.token_auth;
	
	ajaxRequest.data = parameters;
	return ajaxRequest;
}

function bindListGoalEdit()
{
	$('a[name=linkEditGoal]').click( function() {
		var goalId = $(this).attr('id');
		var goal = piwik.goals[goalId];
		initGoalForm("Goals.updateGoal", _pk_translate('Goals_UpdateGoal_js'), goal.name, goal.match_attribute, goal.pattern, goal.pattern_type, (goal.case_sensitive != '0'), goal.revenue, goal.allow_multiple, goalId);
		showAddNewGoal();
		return false;
	});
	
	$('a[name=linkDeleteGoal]').click( function() {
		var goalId = $(this).attr('id');
		var goal = piwik.goals[goalId];
		
		$('#confirm h2').text(sprintf(_pk_translate('Goals_DeleteGoalConfirm_js'), '"'+goal.name+'"'));
		piwikHelper.modalConfirm('#confirm', {yes: function(){
		    $.ajax( getAjaxDeleteGoal( goalId ) );
		}});
		return false;
	});

	$('a[name=linkEditGoals]').click( function(){ 
		return showEditGoals(); 
	} );
}

function initAndShowAddGoalForm()
{
	initGoalForm('Goals.addGoal', _pk_translate('Goals_AddGoal_js'), '', 'url', '', 'contains', caseSensitive = false, allowMultiple = '0', '0');
	return showAddNewGoal(); 
}
