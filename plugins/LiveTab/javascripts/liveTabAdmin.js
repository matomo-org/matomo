/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {

    $submit = $('#liveTabSubmit');

    if (!$submit) {
        return;
    }

    $submit.click(updateLiveTabSettings);

    function getSettings()
    {
        return {
            metric: $('#metricToDisplay option:selected').val(),
            lastMinutes: $('#lastMinutes').val(),
            refreshInterval: $('#refreshInterval').val()
        };
    }

    function getErrorElement()
    {
        return $('#ajaxErrorLiveTab');
    }

    function getLoadingElement()
    {
        return $('#ajaxLoadingLiveTab');
    }

    function updateLiveTabSettings()
    {
        var ajaxHandler = new ajaxHelper();
        ajaxHandler.addParams({
            module: 'API',
            format: 'json',
            method: 'LiveTab.setSettings'
        }, 'GET');
        ajaxHandler.addParams(getSettings(), 'POST');
        ajaxHandler.redirectOnSuccess();
        ajaxHandler.setLoadingElement(getLoadingElement());
        ajaxHandler.setErrorElement(getErrorElement());
        ajaxHandler.send(true);
    }
});