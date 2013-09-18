/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// NOTE: if you cannot find the definition of a variable here, look in index.twig
function SitesManager(_timezones, _currencies, _defaultTimezone, _defaultCurrency) {

    var timezones = _timezones;
    var currencies = _currencies;
    var defaultTimezone = _defaultTimezone;
    var defaultCurrency = _defaultCurrency;
    var siteBeingEdited = false;
    var siteBeingEditedName = '';

    function sendDeleteSiteAJAX(idSite) {
        var ajaxHandler = new ajaxHelper();
        ajaxHandler.addParams({
            idSite: idSite,
            module: 'API',
            format: 'json',
            method: 'SitesManager.deleteSite'
        }, 'GET');
        ajaxHandler.redirectOnSuccess();
        ajaxHandler.setLoadingElement();
        ajaxHandler.send(true);
    }

    function sendAddSiteAJAX(row) {
        var siteName = $(row).find('input#name').val();
        var urls = $(row).find('textarea#urls').val();
        urls = urls.trim().split("\n");
        var excludedIps = $(row).find('textarea#excludedIps').val();
        excludedIps = piwikHelper.getApiFormatTextarea(excludedIps);
        var timezone = $(row).find('#timezones option:selected').val();
        var currency = $(row).find('#currencies option:selected').val();
        var excludedQueryParameters = $(row).find('textarea#excludedQueryParameters').val();
        excludedQueryParameters = piwikHelper.getApiFormatTextarea(excludedQueryParameters);
        var excludedUserAgents = $(row).find('textarea#excludedUserAgents').val();
        excludedUserAgents = piwikHelper.getApiFormatTextarea(excludedUserAgents);
        var keepURLFragments = $('#keepURLFragmentSelect', row).val();
        var ecommerce = $(row).find('#ecommerce option:selected').val();
        var sitesearch = $(row).find('#sitesearch option:selected').val();
        var searchKeywordParameters = $('input#searchKeywordParameters').val();
        var searchCategoryParameters = $('input#searchCategoryParameters').val();

        var ajaxHandler = new ajaxHelper();
        ajaxHandler.addParams({
            module: 'API',
            format: 'json',
            method: 'SitesManager.addSite'
        }, 'GET');
        ajaxHandler.addParams({
            siteName: siteName,
            timezone: timezone,
            currency: currency,
            ecommerce: ecommerce,
            excludedIps: excludedIps,
            excludedQueryParameters: excludedQueryParameters,
            excludedUserAgents: excludedUserAgents,
            keepURLFragments: keepURLFragments,
            siteSearch: sitesearch,
            searchKeywordParameters: searchKeywordParameters,
            searchCategoryParameters: searchCategoryParameters,
            urls: urls
        }, 'POST');
        ajaxHandler.redirectOnSuccess();
        ajaxHandler.setLoadingElement();
        ajaxHandler.send(true);
    }

    function sendUpdateSiteAJAX(row) {
        var siteName = $(row).find('input#siteName').val();
        var idSite = $(row).children('#idSite').html();
        var urls = $(row).find('textarea#urls').val();
        urls = urls.trim().split("\n");
        var excludedIps = $(row).find('textarea#excludedIps').val();
        excludedIps = piwikHelper.getApiFormatTextarea(excludedIps);

        var excludedQueryParameters = $(row).find('textarea#excludedQueryParameters').val();
        excludedQueryParameters = piwikHelper.getApiFormatTextarea(excludedQueryParameters);
        var excludedUserAgents = $(row).find('textarea#excludedUserAgents').val();
        excludedUserAgents = piwikHelper.getApiFormatTextarea(excludedUserAgents);
        var keepURLFragments = $('#keepURLFragmentSelect', row).val();
        var timezone = $(row).find('#timezones option:selected').val();
        var currency = $(row).find('#currencies option:selected').val();
        var ecommerce = $(row).find('#ecommerce option:selected').val();
        var sitesearch = $(row).find('#sitesearch option:selected').val();
        var searchKeywordParameters = $('input#searchKeywordParameters').val();
        var searchCategoryParameters = $('input#searchCategoryParameters').val();

        var ajaxHandler = new ajaxHelper();
        ajaxHandler.addParams({
            module: 'API',
            format: 'json',
            method: 'SitesManager.updateSite',
            idSite: idSite
        }, 'GET');
        ajaxHandler.addParams({
            siteName: siteName,
            timezone: timezone,
            currency: currency,
            ecommerce: ecommerce,
            excludedIps: excludedIps,
            excludedQueryParameters: excludedQueryParameters,
            excludedUserAgents: excludedUserAgents,
            keepURLFragments: keepURLFragments,
            siteSearch: sitesearch,
            searchKeywordParameters: searchKeywordParameters,
            searchCategoryParameters: searchCategoryParameters,
            urls: urls
        }, 'POST');
        ajaxHandler.redirectOnSuccess();
        ajaxHandler.setLoadingElement();
        ajaxHandler.send(true);
    }

    function sendGlobalSettingsAJAX() {
        var timezone = $('#defaultTimezone').find('option:selected').val();
        var currency = $('#defaultCurrency').find('option:selected').val();
        var excludedIps = $('textarea#globalExcludedIps').val();
        excludedIps = piwikHelper.getApiFormatTextarea(excludedIps);
        var excludedQueryParameters = $('textarea#globalExcludedQueryParameters').val();
        excludedQueryParameters = piwikHelper.getApiFormatTextarea(excludedQueryParameters);
        var globalExcludedUserAgents = $('textarea#globalExcludedUserAgents').val();
        globalExcludedUserAgents = piwikHelper.getApiFormatTextarea(globalExcludedUserAgents);
        var globalKeepURLFragments = $('#globalKeepURLFragments').is(':checked') ? 1 : 0;
        var searchKeywordParameters = $('input#globalSearchKeywordParameters').val();
        var searchCategoryParameters = $('input#globalSearchCategoryParameters').val();
        var enableSiteUserAgentExclude = $('input#enableSiteUserAgentExclude').is(':checked') ? 1 : 0;

        var ajaxHandler = new ajaxHelper();
        ajaxHandler.addParams({
            module: 'SitesManager',
            format: 'json',
            action: 'setGlobalSettings'
        }, 'GET');
        ajaxHandler.addParams({
            timezone: timezone,
            currency: currency,
            excludedIps: excludedIps,
            excludedQueryParameters: excludedQueryParameters,
            excludedUserAgents: globalExcludedUserAgents,
            keepURLFragments: globalKeepURLFragments,
            enableSiteUserAgentExclude: enableSiteUserAgentExclude,
            searchKeywordParameters: searchKeywordParameters,
            searchCategoryParameters: searchCategoryParameters
        }, 'POST');
        ajaxHandler.redirectOnSuccess();
        ajaxHandler.setLoadingElement('#ajaxLoadingGlobalSettings');
        ajaxHandler.setErrorElement('#ajaxErrorGlobalSettings');
        ajaxHandler.send(true);
    }

    this.init = function () {
        $('.addRowSite').click(function () {
            piwikHelper.hideAjaxError();
            $('.addRowSite').toggle();
            
            var excludedUserAgentCell = '';
            if ($('#exclude-user-agent-header').is(':visible')) {
                excludedUserAgentCell = '<td><textarea cols="20" rows="4" id="excludedUserAgents"></textarea><br />' + excludedUserAgentsHelp + '</td>';
            }

            var numberOfRows = $('table#editSites')[0].rows.length;
            var newRowId = 'rowNew' + numberOfRows;
            var submitButtonHtml = '<input type="submit" class="addsite submit" value="' + _pk_translate('General_Save') + '" />';
            $($.parseHTML(' <tr id="' + newRowId + '">\
				<td>&nbsp;</td>\
				<td><input id="name" value="Name" size="15" /><br/><br/><br/>' + submitButtonHtml + '</td>\
				<td><textarea cols="25" rows="3" id="urls">http://siteUrl.com/\nhttp://siteUrl2.com/</textarea><br />' + aliasUrlsHelp + keepURLFragmentSelectHTML + '</td>\
				<td><textarea cols="20" rows="4" id="excludedIps"></textarea><br />' + excludedIpHelp + '</td>\
				<td><textarea cols="20" rows="4" id="excludedQueryParameters"></textarea><br />' + excludedQueryParametersHelp + '</td>' +
				excludedUserAgentCell +
				'<td>' + getSitesearchSelector(false) + '</td>\
				<td>' + getTimezoneSelector(defaultTimezone) + '<br />' + timezoneHelp + '</td>\
				<td>' + getCurrencySelector(defaultCurrency) + '<br />' + currencyHelp + '</td>\
				<td>' + getEcommerceSelector(0) + '<br />' + ecommerceHelp + '</td>\
				<td>' + submitButtonHtml + '</td>\
	  			<td><span class="cancel link_but">' + sprintf(_pk_translate('General_OrCancel'), "", "") + '</span></td>\
	 		</tr>'))
                .appendTo('#editSites')
            ;

            piwikHelper.lazyScrollTo('#' + newRowId);

            $('.addsite').click(function () {
                sendAddSiteAJAX($('tr#' + newRowId));
            });

            $('.cancel').click(function () {
                piwikHelper.hideAjaxError();
                $(this).parents('tr').remove();
                $('.addRowSite').toggle();
            });
            return false;
        });

        // when click on deleteuser, the we ask for confirmation and then delete the user
        $('.deleteSite').click(function () {
                piwikHelper.hideAjaxError();
                var idRow = $(this).attr('id');
                var nameToDelete = $(this).parent().parent().find('input#siteName').val() || $(this).parent().parent().find('td#siteName').html();
                var idsiteToDelete = $(this).parent().parent().find('#idSite').html();

                $('#confirm').find('h2').text(sprintf(_pk_translate('SitesManager_DeleteConfirm'), '"' + nameToDelete + '" (idSite = ' + idsiteToDelete + ')'));
                piwikHelper.modalConfirm('#confirm', { yes: function () {

                    sendDeleteSiteAJAX(idsiteToDelete);
                }});
            }
        );

        var alreadyEdited = [];
        $('.editSite')
            .click(function () {
                piwikHelper.hideAjaxError();
                var idRow = $(this).attr('id');
                if (alreadyEdited[idRow] == 1) return;
                if (siteBeingEdited) {
                    $('#alert').find('h2').text(sprintf(_pk_translate('SitesManager_OnlyOneSiteAtTime'), '"' + $("<div/>").html(siteBeingEditedName).text() + '"'));
                    piwikHelper.modalConfirm('#alert', {});
                    return;
                }
                siteBeingEdited = true;

                alreadyEdited[idRow] = 1;
                $('tr#' + idRow + ' .editableSite').each(
                    // make the fields editable
                    // change the EDIT button to VALID button
                    function (i, n) {
                        var contentBefore = $(n).html();

                        var idName = $(n).attr('id');
                        if (idName == 'siteName') {
                            siteBeingEditedName = contentBefore;
                            var contentAfter = '<input id="' + idName + '" value="' + piwikHelper.htmlEntities( piwikHelper.htmlDecode(contentBefore))+ '" size="15" />';

                            var inputSave = $('<br/><input style="margin-top:50px" type="submit" class="submit" value="' + _pk_translate('General_Save') + '" />')
                                .click(function () { submitUpdateSite($(this).parent()); });
                            var spanCancel = $('<div><br/>' + sprintf(_pk_translate('General_OrCancel'), "", "") + '</div>')
                                .click(function () { piwikHelper.refreshAfter(0); });
                            $(n)
                                .html(contentAfter)
                                .keypress(submitSiteOnEnter)
                                .append(inputSave)
                                .append(spanCancel);
                        }
                        else if (idName == 'urls') {
                            var keepURLFragmentsForSite = $(this).closest('tr').attr('data-keep-url-fragments');
                            var contentAfter = '<textarea cols="25" rows="3" id="urls">' + contentBefore.replace(/<br *\/? *> */gi, "\n") + '</textarea>';
                            contentAfter += '<br />' + aliasUrlsHelp + keepURLFragmentSelectHTML;
                            $(n).html(contentAfter).find('select').val(keepURLFragmentsForSite);
                        }
                        else if (idName == 'excludedIps') {
                            var contentAfter = '<textarea cols="20" rows="4" id="excludedIps">' + contentBefore.replace(/<br *\/? *>/gi, "\n") + '</textarea>';
                            contentAfter += '<br />' + excludedIpHelp;
                            $(n).html(contentAfter);
                        }
                        else if (idName == 'excludedQueryParameters') {
                            var contentAfter = '<textarea cols="20" rows="4" id="excludedQueryParameters">' + contentBefore.replace(/<br *\/? *>/gi, "\n") + '</textarea>';
                            contentAfter += '<br />' + excludedQueryParametersHelp;
                            $(n).html(contentAfter);
                        }
                        else if (idName == 'excludedUserAgents') {
                            var contentAfter = '<textarea cols="20" rows="4" id="excludedUserAgents">' +
                                contentBefore.replace(/<br *\/? *>/gi, "\n") + '</textarea><br />' + excludedUserAgentsHelp;
                            $(n).html(contentAfter);
                        }
                        else if (idName == 'timezone') {
                            var contentAfter = getTimezoneSelector(contentBefore);
                            contentAfter += '<br />' + timezoneHelp;
                            $(n).html(contentAfter);
                        }
                        else if (idName == 'currency') {
                            var contentAfter = getCurrencySelector(contentBefore);
                            contentAfter += '<br />' + currencyHelp;
                            $(n).html(contentAfter);
                        }
                        else if (idName == 'ecommerce') {
                            var ecommerceActive = contentBefore.indexOf("ecommerceActive") > 0 ? 1 : 0;
                            contentAfter = getEcommerceSelector(ecommerceActive) + '<br />' + ecommerceHelp;
                            $(n).html(contentAfter);
                        }
                        else if (idName == 'sitesearch') {
                            contentAfter = getSitesearchSelector(contentBefore);
                            $(n).html(contentAfter);
                            onClickSiteSearchUseDefault();
                        }
                    }
                );
                $(this)
                    .toggle()
                    .parent()
                    .prepend($('<input type="submit" class="updateSite submit" value="' + _pk_translate('General_Save') + '" />')
                        .click(function () { sendUpdateSiteAJAX($('tr#' + idRow)); })
                    );
            });

        $('#globalSettingsSubmit').click(function () {
            sendGlobalSettingsAJAX();
        });

        $('#defaultTimezone').html(getTimezoneSelector(defaultTimezone));
        $('#defaultCurrency').html(getCurrencySelector(defaultCurrency));

        $('td.editableSite').click(function () { $(this).parent().find('.editSite').click(); });
    };

    function getSitesearchSelector(contentBefore) {
        var globalKeywordParameters = $('input#globalSearchKeywordParameters').val().trim();
        var globalCategoryParameters = $('input#globalSearchCategoryParameters').val().trim();
        if (contentBefore) {
            var enabled = contentBefore.indexOf("sitesearchActive") > 0 ? 1 : 0;
            var spanSearch = $(contentBefore).filter('.sskp');
            var searchKeywordParameters = spanSearch.attr('sitesearch_keyword_parameters').trim();
            var searchCategoryParameters = spanSearch.attr('sitesearch_category_parameters').trim();
            var checked = globalKeywordParameters.length && !searchKeywordParameters.trim().length;
        } else {
            var searchKeywordParameters = globalKeywordParameters;
            var searchCategoryParameters = globalCategoryParameters;
            var enabled = searchKeywordParameters.length || searchCategoryParameters.length; // default is enabled
            var checked = enabled;
        }

        var searchGlobalHasValues = globalKeywordParameters.trim().length;
        var html = '<select id="sitesearch" onchange="return onClickSiteSearchUseDefault();">';
        var selected = ' selected="selected" ';
        html += '<option ' + (enabled ? selected : '') + ' value="1">' + sitesearchEnabled + '</option>';
        html += '<option ' + (enabled ? '' : selected) + ' value="0">' + sitesearchDisabled + '</option>';
        html += '</select>';
        html += '<span style="font-size: 11px;"><br/>';

        if (searchGlobalHasValues) {
            var checkedStr = checked ? ' checked ' : '';
            html += '<span id="sitesearchUseDefault"' + (!enabled ? ' style="display:none" ' : '') + '><input type="checkbox" '
                + checkedStr + ' id="sitesearchUseDefaultCheck" onclick="return onClickSiteSearchUseDefault();"> '
                + sitesearchUseDefault + ' </span>';
                + '</label>';

            html += '<div ' + ((checked && enabled) ? '' : 'style="display-none"') + ' class="searchDisplayParams form-description">'
                + searchKeywordLabel + ' (' + strDefault + ') ' + ': '
                + piwikHelper.htmlEntities( globalKeywordParameters )
                + (globalCategoryParameters.length ? ', ' + searchCategoryLabel + ': ' + piwikHelper.htmlEntities(globalCategoryParameters) : '')
                + '</div>';
        }
        html += '<div id="sitesearchIntro">' + sitesearchIntro + '</div>';

        html += '<div id="searchSiteParameters">';
        html += '<br/><label><div style="margin-bottom:3px">'
                + piwikHelper.htmlEntities(searchKeywordLabel)
                + '</div><input type="text" size="22" id="searchKeywordParameters" value="'
                + piwikHelper.htmlEntities(searchKeywordParameters)
                + '" style="margin-bottom: -10px;font-size:9pt;font-family:monospace"></input>'
                + searchKeywordHelp + '</label>';

        // if custom var plugin is disabled, category tracking not supported
        if (globalCategoryParameters != 'globalSearchCategoryParametersIsDisabled') {
            html += '<br/><label><div style="margin-bottom:3px">' + searchCategoryLabel + '</div><input type="text" size="22" id="searchCategoryParameters" value="' + searchCategoryParameters + '" style="margin-bottom: -10px;font-size:9pt;font-family:monospace"></input>' + searchCategoryHelp + '</label>';
        }
        html += '</div></span>';

        return html;
    }

    function getEcommerceSelector(enabled) {
        var html = '<select id="ecommerce">';
        var selected = ' selected="selected" ';
        html += '<option ' + (enabled ? '' : selected) + ' value="0">' + ecommerceDisabled + '</option>';
        html += '<option ' + (enabled ? selected : '') + ' value="1">' + ecommerceEnabled + '</option>';
        html += '</select>';
        return html;
    }

    function getTimezoneSelector(selectedTimezone) {
        var html = '<select id="timezones">';
        for (var continent in timezones) {
            html += '<optgroup label="' + continent + '">';
            for (var timezoneId in timezones[continent]) {
                var selected = '';
                if (timezoneId == selectedTimezone) {
                    selected = ' selected="selected" ';
                }
                html += '<option ' + selected + ' value="' + timezoneId + '">' + timezones[continent][timezoneId] + '</option>';
            }
            html += "</optgroup>\n";
        }
        html += '</select>';
        return html;
    }


    function getCurrencySelector(selectedCurrency) {
        var html = '<select id="currencies">';
        for (var currency in currencies) {
            var selected = '';
            if (currency == selectedCurrency) {
                selected = ' selected="selected" ';
            }
            html += '<option ' + selected + ' value="' + currency + '">' + currencies[currency] + '</option>';
        }
        html += '</select>';
        return html;
    }

    function submitSiteOnEnter(e) {
        var key = e.keyCode || e.which;
        if (key == 13) {
            submitUpdateSite(this);
            $(this).find('.addsite').click();
        }
    }

    function submitUpdateSite(self) {
        $(self).parent().find('.updateSite').click();
    }
}

function onClickSiteSearchUseDefault() {
    // Site Search enabled
    if ($('select#sitesearch').val() == "1") {
        $('#sitesearchUseDefault').show();

        // Use default is checked
        if ($('#sitesearchUseDefaultCheck').is(':checked')) {
            $('#searchSiteParameters').hide();
            $('#sitesearchIntro').show();
            $('#searchKeywordParameters,#searchCategoryParameters').val('');
            $('.searchDisplayParams').show();
            // Use default is unchecked

        } else {
            $('#sitesearchIntro').hide();
            $('.searchDisplayParams').hide();
            $('#searchSiteParameters').show();
        }
    } else {
        $('.searchDisplayParams').hide();
        $('#sitesearchUseDefault').hide();
        $('#searchSiteParameters').hide();
        $('#sitesearchIntro').show();
    }
}

$(function () {

    // when code element is clicked, select the text
    $('.trackingHelp code').click(function() {
        // credit where credit is due:
        //   http://stackoverflow.com/questions/1173194/select-all-div-text-with-single-mouse-click
        var range;
        if (document.body.createTextRange) // MSIE
        {
            range = document.body.createTextRange();
            range.moveToElementText(this);
            range.select();
        }
        else if (window.getSelection) // others
        {
            range = document.createRange();
            range.selectNodeContents(this);

            var selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
        }
    })
      .click();
});
