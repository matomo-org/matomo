/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($, require) {

    var piwikHost = window.location.host,
        piwikPath = location.pathname.substring(0, location.pathname.lastIndexOf('/')),
        exports = require('piwik/Tracking');

    /**
     * Singleton class used to trigger events on. Plugins can bind to these events
     * to customize the tracking code shown to users.
     * 
     * The following events are triggered:
     * 
     * - customizeJavaScriptParams: function (eventObject, params)
     * 
     *   Triggered before tracking JavaScript is generated. This event can be used to
     *   customize how the generated code looks, without having to parse JavaScript.
     * 
     *   params is an object containing the data used to create the JavaScript tracking
     *   code.
     * 
     * - customizeJavaScript: function (eventObject, eventData)
     *   
     *   Triggered after tracking JavaScript is generated. This event can be used to
     *   customize how the generated code looks, if it cannot be done with the
     *   customizeJavaScriptParams event.
     *   
     *   eventData is an object with one element, 'code', which holds the JavaScript
     *   tracking code.
     * 
     * - customizeTrackerLinkParams: function (eventObject, params)
     * 
     *   Triggered before the tracking link is generated. This event can be used to
     *   customize how the generated link looks, without having to parse the HTML
     *   link code.
     * 
     *   params is an object containing the data used to create the tracking link.
     * 
     * - customizeTrackerLink: function (eventObject, eventData)
     * 
     *   Triggered after the tracking link is generated. This event can be used to
     *   customize how the generated link looks, if the customization cannot be done
     *   with the customizeTrackerLinkParams event.
     * 
     *   eventData is an object with one element, 'code', which holds the tracking
     *   link HTML.
     * 
     * @constructor
     */
    var TrackingCodeGenerator = function () {
        // empty
    }

    var TrackingCodeGeneratorSingleton = exports.TrackingCodeGenerator = new TrackingCodeGenerator();

    $(document).ready(function () {

        // get preloaded server-side data necessary for code generation
        var dataElement = $('#js-tracking-generator-data'),
            currencySymbols = JSON.parse(dataElement.attr('data-currencies')),
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
        var generateJsCode = function () {
            // get params used to generate JS code
            var params = {
                idSite: $('#js-tracker-website').find('.custom_select_main_link').attr('data-siteid'),
                groupPageTitlesByDomain: $('#javascript-tracking-group-by-domain').is(':checked'),
                mergeSubdomains: $('#javascript-tracking-all-subdomains').is(':checked'),
                mergeAliasUrls: $('#javascript-tracking-all-aliases').is(':checked'),
                visitorCustomVariables: getCustomVariables('javascript-tracking-visitor-cv'),
                pageCustomVariables: getCustomVariables('javascript-tracking-page-cv'),
                customCampaignNameQueryParam: null,
                customCampaignKeywordParam: null,
                doNotTrack: $('#javascript-tracking-do-not-track').is(':checked'),
                piwikHost: piwikHost,
                piwikPath: piwikPath
            };

            if ($('#custom-campaign-query-params-check').is(':checked')) {
                params.customCampaignNameQueryParam = $('#custom-campaign-name-query-param').val();
                params.customCampaignKeywordParam = $('#custom-campaign-keyword-query-param').val();
            }

            // allow plugins to modify data used to generate tracking code
            $(TrackingCodeGeneratorSingleton).trigger('customizeJavaScriptParams', params);

            var idSite = params.idSite;

            // generate JS
            // changes made to this code should be mirrored in core/Piwik.php function getJavascriptCode()
            var result = '<!-- Piwik -->\n\
<script type="text/javascript">\n\
  var _paq = _paq || [];\n';

            if (params.groupPageTitlesByDomain) {
                result += '  _paq.push(["setDocumentTitle", document.domain + "/" + document.title]);\n';
            }

            if (params.mergeSubdomains) {
                var mainHostAllSub = '*.' + getHostNameFromUrl(siteUrls[idSite][0]);
                result += '  _paq.push(["setCookieDomain", ' + JSON.stringify(mainHostAllSub) + ']);\n';
            }

            if (params.mergeAliasUrls) {
                var siteHosts = [];
                for (var i = 0; i != siteUrls[idSite].length; ++i) {
                    siteHosts[i] = '*.' + getHostNameFromUrl(siteUrls[idSite][i]);
                }
                result += '  _paq.push(["setDomains", ' + JSON.stringify(siteHosts) + ']);\n';
            }

            if (params.visitorCustomVariables.length) {
                result += '  // you can set up to 5 custom variables for each visitor\n';
                result += getCustomVariableJS(params.visitorCustomVariables, 'visit');
            }

            if (params.pageCustomVariables.length) {
                result += '  // you can set up to 5 custom variables for each action (page view, ' +
                    'download, click, site search)\n';
                result += getCustomVariableJS(params.pageCustomVariables, 'page');
            }

            if (params.customCampaignNameQueryParam) {
                result += '  _paq.push(["setCampaignNameKey", ' + JSON.stringify(params.customCampaignNameQueryParam) + ']);\n';
            }

            if (params.customCampaignKeywordParam) {
                result += '  _paq.push(["setCampaignKeywordKey", ' + JSON.stringify(params.customCampaignKeywordParam) + ']);\n';
            }

            if (params.doNotTrack) {
                result += '  _paq.push(["setDoNotTrack", true]);\n';
            }

            result += '  _paq.push(["trackPageView"]);\n\
  _paq.push(["enableLinkTracking"]);\n\n\
  (function() {\n\
    var u=(("https:" == document.location.protocol) ? "https" : "http") + "://' + params.piwikHost + params.piwikPath + '/";\n\
    _paq.push(["setTrackerUrl", u+"piwik.php"]);\n\
    _paq.push(["setSiteId", ' + JSON.stringify(idSite) + ']);\n\
    var d=document, g=d.createElement("script"), s=d.getElementsByTagName("script")[0]; g.type="text/javascript";\n\
    g.defer=true; g.async=true; g.src=u+"piwik.js"; s.parentNode.insertBefore(g,s);\n\
  })();\n\
</script>\n\
'+'<!-- End Piwik Code -->';

            // allow plugins to modify generated JavaScript
            var eventData = {code: result, piwikHost: piwikHost };
            $(TrackingCodeGeneratorSingleton).trigger('customizeJavaScript', eventData);
            result = eventData.code;

            $('#javascript-text').find('textarea').val(result)
        };

        // function that generates image tracker link
        var generateImageTrackerLink = function () {
            // get data used to generate the link
            var generateDataParams = {
                idSite: $('#image-tracker-website').find('.custom_select_main_link').attr('data-siteid'),
                actionName: $('#image-tracker-action-name').val(),
                piwikHost: piwikHost,
                piwikPath: piwikPath
            }

            if ($('#image-tracking-goal-check').is(':checked')) {
                generateDataParams.idGoal = $('#image-tracker-goal').val();
                if (generateDataParams.idGoal) {
                    generateDataParams.revenue = $('#image-tracker-advanced-options').find('.revenue').val();
                }
            }

            // allow plugins to modify data used to generate tracking code
            $(TrackingCodeGeneratorSingleton).trigger('customizeTrackerLinkParams', generateDataParams);

            // generate link HTML
            var params = {
                idsite: generateDataParams.idSite,
                rec: 1
            };

            if (generateDataParams.actionName) {
                params.action_name = generateDataParams.actionName;
            }

            if (generateDataParams.idGoal) {
                params.idGoal = generateDataParams.idGoal;
                if (generateDataParams.revenue) {
                    params.revenue = generateDataParams.revenue;
                }
            }

            var piwikURL = ("https:" == document.location.protocol ? "https://" : "http://") + generateDataParams.piwikHost 
                         + generateDataParams.piwikPath + '/piwik.php';

            var result = '<!-- Piwik Image Tracker -->\n\
<img src="' + piwikURL + '?' + $.param(params) + '" style="border:0" alt="" />\n\
<!-- End Piwik -->';

            // allow plugins to modify tracking link code
            var eventData = {code: result};
            $(TrackingCodeGeneratorSingleton).trigger('customizeTrackerLink', result);
            result = eventData.code;

            result = result.replace(/[&]/g, "&amp;");
            $('#image-tracking-text').find('textarea').val(result);
        };

        // on image link tracker site change, change available goals
        $('#image-tracker-website').bind('piwik:siteSelected', function (e, site) {
            getSiteData(site.id, '#image-tracking-code-options', function () {
                resetGoalSelectItems(site.id, 'image-tracker-goal');
                generateImageTrackerLink();
            });
        });

        // on js link tracker site change, change available goals
        $('#js-tracker-website').bind('piwik:siteSelected', function (e, site) {
            $('.current-site-name', '#optional-js-tracking-options').each(function () {
                $(this).text(site.name);
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
            // (5 custom variables + 1 row for add new row)
            if ($('tr', row.parent()).length == 6) {
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
            $('#js-tracker-website').find('.custom_select_main_link').attr('data-siteid'),
            '#js-code-options,#image-tracking-code-options',
            function () {
                var imageTrackerSiteId = $('#image-tracker-website').find('.custom_select_main_link').attr('data-siteid');
                resetGoalSelectItems(imageTrackerSiteId, 'image-tracker-goal');

                generateJsCode();
                generateImageTrackerLink();
            }
        );
    });

}(jQuery, require));