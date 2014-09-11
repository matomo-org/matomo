/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require) {

    var piwikHost = window.location.host,
        piwikPath = location.pathname.substring(0, location.pathname.lastIndexOf('/')),
        exports = require('piwik/Tracking');

    /**
     * This class is deprecated. Use server-side events instead.
     *
     * @deprecated
     */
    var TrackingCodeGenerator = function () {
        // empty
    };

    var TrackingCodeGeneratorSingleton = exports.TrackingCodeGenerator = new TrackingCodeGenerator();

    $(document).ready(function () {

        // get preloaded server-side data necessary for code generation
        var dataElement = $('#js-tracking-generator-data'),
            currencySymbols = JSON.parse(dataElement.attr('data-currencies')),
            maxCustomVariables = parseInt(dataElement.attr('max-custom-variables'), 10),
            siteUrls = {},
            siteCurrencies = {},
            allGoals = {},
            noneText = $('#image-tracker-goal').find('>option').text();

        //
        // utility methods
        //

        // returns JavaScript code for tracking custom variables based on an array of
        // custom variable name-value pairs (so an array of 2-element arrays) and
        // a scope (either 'visit' or 'page')
        var getCustomVariableJS = function (customVariables, scope) {
            var result = '';
            for (var i = 0; i != 5; ++i) {
                if (customVariables[i]) {
                    var key = customVariables[i][0],
                        value = customVariables[i][1];
                    result += '  _paq.push(["setCustomVariable", ' + (i + 1) + ', ' + JSON.stringify(key) + ', '
                        + JSON.stringify(value) + ', ' + JSON.stringify(scope) + ']);\n';
                }
            }
            return result;
        };

        // gets the list of custom variables entered by the user in a custom variable
        // section
        var getCustomVariables = function (sectionId) {
            var customVariableNames = $('.custom-variable-name', '#' + sectionId),
                customVariableValues = $('.custom-variable-value', '#' + sectionId);

            var result = [];
            if ($('.section-toggler-link', '#' + sectionId).is(':checked')) {
                for (var i = 0; i != customVariableNames.length; ++i) {
                    var name = $(customVariableNames[i]).val();

                    result[i] = null;
                    if (name) {
                        result[i] = [name, $(customVariableValues[i]).val()];
                    }
                }
            }
            return result;
        };

        // quickly gets the host + port from a url
        var getHostNameFromUrl = function (url) {
            var element = $('<a></a>')[0];
            element.href = url;
            return element.hostname;
        };

        // queries Piwik for needed site info for one site
        var getSiteData = function (idSite, sectionSelect, callback) {
            // if data is already loaded, don't do an AJAX request
            if (siteUrls[idSite]
                && siteCurrencies[idSite]
                && typeof allGoals[idSite] !== 'undefined'
            ) {
                callback();
                return;
            }

            // disable section
            $(sectionSelect).find('input,select,textarea').attr('disabled', 'disabled');

            var ajaxRequest = new ajaxHelper();
            ajaxRequest.setBulkRequests(
                // get site info (for currency)
                {
                    module: 'API',
                    method: 'SitesManager.getSiteFromId',
                    idSite: idSite
                },

                // get site urls
                {
                    module: 'API',
                    method: 'SitesManager.getSiteUrlsFromId',
                    idSite: idSite
                },

                // get site goals
                {
                    module: 'API',
                    method: 'Goals.getGoals',
                    idSite: idSite
                }
            );
            ajaxRequest.setCallback(function (data) {
                var currency = data[0][0].currency || '';
                siteCurrencies[idSite] = currencySymbols[currency.toUpperCase()];
                siteUrls[idSite] = data[1] || [];
                allGoals[idSite] = data[2] || [];

                // re-enable controls
                $(sectionSelect).find('input,select,textarea').removeAttr('disabled');

                callback();
            });
            ajaxRequest.setFormat('json');
            ajaxRequest.send(false);
        };

        // resets the select options of a goal select using a site ID
        var resetGoalSelectItems = function (idsite, id) {
            var selectElement = $('#' + id).html('');

            selectElement.append($('<option value=""></option>').text(noneText));

            var goals = allGoals[idsite] || [];
            for (var key in goals) {
                var goal = goals[key];
                selectElement.append($('<option/>').val(goal.idgoal).text(goal.name));
            }

            // set currency string
            $('#' + id).parent().find('.currency').text(siteCurrencies[idsite]);
        };

        // function that generates JS code
        var generateJsCodeAjax = null,
            generateJsCode = function () {
                // get params used to generate JS code
                var params = {
                    piwikUrl: piwikHost + piwikPath,
                    groupPageTitlesByDomain: $('#javascript-tracking-group-by-domain').is(':checked') ? 1 : 0,
                    mergeSubdomains: $('#javascript-tracking-all-subdomains').is(':checked') ? 1 : 0,
                    mergeAliasUrls: $('#javascript-tracking-all-aliases').is(':checked') ? 1 : 0,
                    visitorCustomVariables: getCustomVariables('javascript-tracking-visitor-cv'),
                    pageCustomVariables: getCustomVariables('javascript-tracking-page-cv'),
                    customCampaignNameQueryParam: null,
                    customCampaignKeywordParam: null,
                    doNotTrack: $('#javascript-tracking-do-not-track').is(':checked') ? 1 : 0,
                    disableCookies: $('#javascript-tracking-disable-cookies').is(':checked') ? 1 : 0
                };

                if ($('#custom-campaign-query-params-check').is(':checked')) {
                    params.customCampaignNameQueryParam = $('#custom-campaign-name-query-param').val();
                    params.customCampaignKeywordParam = $('#custom-campaign-keyword-query-param').val();
                }

                if (generateJsCodeAjax) {
                    generateJsCodeAjax.abort();
                }

                generateJsCodeAjax = new ajaxHelper();
                generateJsCodeAjax.addParams({
                    module: 'API',
                    format: 'json',
                    method: 'SitesManager.getJavascriptTag',
                    idSite: $('#js-tracker-website').attr('siteid')
                }, 'GET');
                generateJsCodeAjax.addParams(params, 'POST');
                generateJsCodeAjax.setCallback(function (response) {
                    generateJsCodeAjax = null;

                    $('#javascript-text').find('textarea').val(response.value);
                });
                generateJsCodeAjax.send();
            };

        // function that generates image tracker link
        var generateImageTrackingAjax = null,
            generateImageTrackerLink = function () {
                // get data used to generate the link
                var generateDataParams = {
                    piwikUrl: piwikHost + piwikPath,
                    actionName: $('#image-tracker-action-name').val()
                };

                if ($('#image-tracking-goal-check').is(':checked')) {
                    generateDataParams.idGoal = $('#image-tracker-goal').val();
                    if (generateDataParams.idGoal) {
                        generateDataParams.revenue = $('#image-tracker-advanced-options').find('.revenue').val();
                    }
                }

                if (generateImageTrackingAjax) {
                    generateImageTrackingAjax.abort();
                }

                generateImageTrackingAjax = new ajaxHelper();
                generateImageTrackingAjax.addParams({
                    module: 'API',
                    format: 'json',
                    method: 'SitesManager.getImageTrackingCode',
                    idSite: $('#image-tracker-website').attr('siteid')
                }, 'GET');
                generateImageTrackingAjax.addParams(generateDataParams, 'POST');
                generateImageTrackingAjax.setCallback(function (response) {
                    generateImageTrackingAjax = null;

                    $('#image-tracking-text').find('textarea').val(response.value);
                });
                generateImageTrackingAjax.send();
            };

        // on image link tracker site change, change available goals
        $('#image-tracker-website').bind('change', function (e, site) {
            getSiteData(site.id, '#image-tracking-code-options', function () {
                resetGoalSelectItems(site.id, 'image-tracker-goal');
                generateImageTrackerLink();
            });
        });

        // on js link tracker site change, change available goals
        $('#js-tracker-website').bind('change', function (e, site) {
            $('.current-site-name', '#optional-js-tracking-options').each(function () {
                $(this).html(site.name);
            });

            getSiteData(site.id, '#js-code-options', function () {
                var siteHost = getHostNameFromUrl(siteUrls[site.id][0]);
                $('.current-site-host', '#optional-js-tracking-options').each(function () {
                    $(this).text(siteHost);
                });

                var defaultAliasUrl = 'x.' + siteHost;
                $('.current-site-alias').text(siteUrls[site.id][1] || defaultAliasUrl);

                resetGoalSelectItems(site.id, 'js-tracker-goal');
                generateJsCode();
            });
        });

        // on click 'add' link in custom variable section, add a new row, but only
        // allow 5 custom variable entry rows
        $('.add-custom-variable').click(function (e) {
            e.preventDefault();

            var newRow = '<tr>\
			<td>&nbsp;</td>\
			<td><input type="textbox" class="custom-variable-name"/></td>\
			<td>&nbsp;</td>\
			<td><input type="textbox" class="custom-variable-value"/></td>\
		</tr>',
                row = $(this).closest('tr');

            row.before(newRow);

            // hide add button if max # of custom variables has been reached
            // (X custom variables + 1 row for add new row)
            if ($('tr', row.parent()).length == (maxCustomVariables + 1)) {
                $(this).hide();
            }

            return false;
        });

        // when any input in the JS tracking options section changes, regenerate JS code
        $('#optional-js-tracking-options').on('change', 'input', function () {
            generateJsCode();
        });

        // when any input/select in the image tracking options section changes, regenerate
        // image tracker link
        $('#image-tracking-section').on('change', 'input,select', function () {
            generateImageTrackerLink();
        });

        // on click generated code textareas, select the text so it can be easily copied
        $('#javascript-text>textarea,#image-tracking-text>textarea').click(function () {
            $(this).select();
        });

        // initial generation
        getSiteData(
            $('#js-tracker-website').attr('siteid'),
            '#js-code-options,#image-tracking-code-options',
            function () {
                var imageTrackerSiteId = $('#image-tracker-website').attr('siteid');
                resetGoalSelectItems(imageTrackerSiteId, 'image-tracker-goal');

                generateJsCode();
                generateImageTrackerLink();
            }
        );
    });

}(jQuery, require));
