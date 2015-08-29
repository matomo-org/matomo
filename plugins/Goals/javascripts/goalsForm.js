/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function showAddNewGoal() {
    hideForms();
    $(".entityAddContainer").show();
    showCancel();
    hideCreateGoal();
    piwikHelper.lazyScrollTo(".entityContainer", 400);
    return false;
}

function showEditGoals() {
    hideForms();
    showCreateGoal();
    $("#entityEditContainer").show();
    piwikHelper.lazyScrollTo(".entityContainer", 400);
    return false;
}

function hideForms() {
    $(".entityAddContainer").hide();
    $("#entityEditContainer").hide();
}

function showCancel() {
    $(".entityCancel").show();
    $('.entityCancelLink').click(function () {
        hideForms();
        $(".entityCancel").hide();
        showEditGoals();
    });
}

function showCreateGoal() {
    $("#add-goal").show();
}

function hideCreateGoal() {
    $("#add-goal").hide();
}

function onMatchAttributeChange(matchAttribute)
{
    if ('event' === matchAttribute) {
        $('.entityAddContainer .whereEvent').show();
        $('.entityAddContainer .whereUrl').hide();
    } else {
        $('.entityAddContainer .whereEvent').hide();
        $('.entityAddContainer .whereUrl').show();
    }

    $('#match_attribute_name').html(mappingMatchTypeName[matchAttribute]);
    $('#examples_pattern').html(mappingMatchTypeExamples[matchAttribute]);
}

function updateMatchAttribute () {
    var matchTypeId = $(this).val();
    onMatchAttributeChange(matchTypeId);
}

// init the goal form with existing goal value, if any
function initGoalForm(goalMethodAPI, submitText, goalName, matchAttribute, pattern, patternType, caseSensitive, revenue, allowMultiple, goalId) {
    $('#goal_name').val(goalName);
    if (matchAttribute == 'manually') {
        $('select[name=trigger_type] option[value=manually]').prop('selected', true);
        $('input[name=match_attribute]').prop('disabled', true);
        $('#match_attribute_section').hide();
        $('#match_attribute_section2').hide();
        $('#manual_trigger_section').show();
        matchAttribute = 'url';
    } else {
        $('select[name=trigger_type] option[value=visitors]').prop('selected', true);
    }

    if (0 === matchAttribute.indexOf('event')) {
        $('select[name=event_type] option[value=' + matchAttribute + ']').prop('selected', true);
        matchAttribute = 'event';
    }

    onMatchAttributeChange(matchAttribute);

    $('input[name=match_attribute][value=' + matchAttribute + ']').prop('checked', true);
    $('input[name=allow_multiple][value=' + allowMultiple + ']').prop('checked', true);
    $('#match_attribute_name').html(mappingMatchTypeName[matchAttribute]);
    $('#examples_pattern').html(mappingMatchTypeExamples[matchAttribute]);
    $('select[name=pattern_type] option[value=' + patternType + ']').prop('selected', true);
    $('input[name=pattern]').val(pattern);
    $('#case_sensitive').prop('checked', caseSensitive);
    $('input[name=revenue]').val(revenue);
    $('input[name=methodGoalAPI]').val(goalMethodAPI);
    $('#goal_submit').val(submitText);
    if (goalId != undefined) {
        $('input[name=goalIdUpdate]').val(goalId);
    }

    // force re-run of iCheck. They were already initialized with all radio fields not selected. see #5961
    $('.entityAddContainer div.form-radio').removeClass('form-radio');
    $(document).trigger('Goals.edit', {});
}

function bindGoalForm() {

    $('select[name=trigger_type]').change(function () {
        var triggerTypeId = $(this).val();
        if (triggerTypeId == "manually") {
            $('input[name=match_attribute]').prop('disabled', true);
            $('#match_attribute_section').hide();
            $('#match_attribute_section2').hide();
            $('#manual_trigger_section').show();
        } else {
            $('input[name=match_attribute]').removeProp('disabled');
            $('#match_attribute_section').show();
            $('#match_attribute_section2').show();
            $('#manual_trigger_section').hide();
            // force re-run of iCheck
            $('.entityAddContainer div.form-radio').removeClass('form-radio');
            $(document).trigger('Goals.edit', {});
        }
    });

    $(document).bind('Goals.edit', function () {
        $('input[name=match_attribute]').off('change', updateMatchAttribute);
        $('input[name=match_attribute]').change(updateMatchAttribute);
    });

    $('#goal_submit').click(function () {
        // prepare ajax query to API to add goal
        ajaxAddGoal();
        return false;
    });

    $('#add-goal').click(function () {
        initAndShowAddGoalForm();
        piwikHelper.lazyScrollTo('#goal_name');
    });
}

function ajaxDeleteGoal(idGoal) {
    piwikHelper.lazyScrollTo(".entityContainer", 400);

    var parameters = {};
    parameters.format = 'json';
    parameters.idGoal = idGoal;
    parameters.module = 'API';
    parameters.method = 'Goals.deleteGoal';

    var ajaxRequest = new ajaxHelper();
    ajaxRequest.addParams(parameters, 'get');
    ajaxRequest.setLoadingElement('#goalAjaxLoading');
    ajaxRequest.setCallback(function () { location.reload(); });
    ajaxRequest.send(true);
}

function ajaxAddGoal() {
    piwikHelper.lazyScrollTo(".entityContainer", 400);

    var parameters = {};
    parameters.name = encodeURIComponent($('#goal_name').val());

    if ($('[name=trigger_type]').val() == 'manually') {
        parameters.matchAttribute = 'manually';
        parameters.patternType = 'regex';
        parameters.pattern = '.*';
        parameters.caseSensitive = 0;
    } else {
        parameters.matchAttribute = $('input[name=match_attribute]:checked').val();

        if (parameters.matchAttribute === 'event') {
            parameters.matchAttribute = $('select[name=event_type]').val();
        }

        parameters.patternType = $('[name=pattern_type]').val();
        parameters.pattern = encodeURIComponent($('input[name=pattern]').val());
        parameters.caseSensitive = $('#case_sensitive').prop('checked') == true ? 1 : 0;
    }
    parameters.revenue = $('input[name=revenue]').val();
    parameters.allowMultipleConversionsPerVisit = $('input[name=allow_multiple]:checked').val() == true ? 1 : 0;

    parameters.idGoal = $('input[name=goalIdUpdate]').val();
    parameters.format = 'json';
    parameters.module = 'API';
    parameters.method = $('input[name=methodGoalAPI]').val();

    var ajaxRequest = new ajaxHelper();
    ajaxRequest.addParams(parameters, 'get');
    ajaxRequest.setLoadingElement('#goalAjaxLoading');
    ajaxRequest.setCallback(function () {
        location.reload();
    });
    ajaxRequest.send(true);
}

function editGoal(goalId)
{
    var goal = piwik.goals[goalId];
    initGoalForm("Goals.updateGoal", _pk_translate('Goals_UpdateGoal'), goal.name, goal.match_attribute, goal.pattern, goal.pattern_type, (goal.case_sensitive != '0'), goal.revenue, goal.allow_multiple, goalId);
    showAddNewGoal();
}

function bindListGoalEdit() {
    $('.edit-goal').click(function () {
        var goalId = $(this).attr('id');
        editGoal(goalId);
        return false;
    });

    $('.delete-goal').click(function () {
        var goalId = $(this).attr('id');
        var goal = piwik.goals[goalId];

        $('#confirm').find('h2').text(sprintf(_pk_translate('Goals_DeleteGoalConfirm'), '"' + goal.name + '"'));
        piwikHelper.modalConfirm('#confirm', {yes: function () {
            ajaxDeleteGoal(goalId);
        }});
        return false;
    });

    $('a[name=linkEditGoals]').click(function () {
        return showEditGoals();
    });
}

function initAndShowAddGoalForm() {
    initGoalForm('Goals.addGoal', _pk_translate('Goals_AddGoal'), '', 'url', '', 'contains', /*caseSensitive = */false, /*allowMultiple = */'0', '0');
    return showAddNewGoal();
}
