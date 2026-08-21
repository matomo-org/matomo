/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

//
// TRANSITIONS ROW ACTION FOR DATA TABLES
//

function DataTable_RowActions_Transitions(dataTable) {
    this.dataTable = dataTable;
}

DataTable_RowActions_Transitions.prototype = new DataTable_RowAction;

/** Static helper method to launch transitions from anywhere */
DataTable_RowActions_Transitions.launchForUrl = function (url, segment) {
    var value = 'Transitions:url:' + url;
    if (segment) {
        value += ':segment:' + segment;
    }
    broadcast.propagateNewPopoverParameter('RowAction', value);
};

DataTable_RowActions_Transitions.isPageUrlReport = function (module, action) {
    return module == 'Actions' &&
        (action == 'getPageUrls' || action == 'getEntryPageUrls' || action == 'getExitPageUrls' || action == 'getPageUrlsFollowingSiteSearch');
};

DataTable_RowActions_Transitions.isPageTitleReport = function (module, action) {
    return module == 'Actions' && (action == 'getPageTitles' || action == 'getPageTitlesFollowingSiteSearch');
};

DataTable_RowActions_Transitions.registeredReports = [];
DataTable_RowActions_Transitions.registerReport = function (handler) {
    DataTable_RowActions_Transitions.registeredReports.push(handler);
}

DataTable_RowActions_Transitions.prototype.trigger = function (tr, e, subTableLabel) {
    var i = 0;
    for (i; i < DataTable_RowActions_Transitions.registeredReports.length; i++) {
        var report = DataTable_RowActions_Transitions.registeredReports[i];
        if (report
            && report.trigger
            && report.isAvailableOnReport
            && report.isAvailableOnReport(this.dataTable.param)) {
            report.trigger.apply(this, arguments);
            return;
        }
    }

    alert('Transitions can\'t be used on this report.');
};

DataTable_RowActions_Transitions.prototype.performAction = function (label, tr, e) {
    var separator = ' > '; // LabelFilter::SEPARATOR_RECURSIVE_LABEL
    var labelParts = label.split(separator);
    for (var i = 0; i < labelParts.length; i++) {
        var labelPart = labelParts[i].replace('@', '');
        labelParts[i] = $.trim(decodeURIComponent(labelPart));
    }
    var delimiter = piwik.config.action_url_category_delimiter;
    if(this.dataTable.param.action.indexOf('PageTitles') !== false) {
        delimiter = piwik.config.action_title_category_delimiter;
    }
    label = labelParts.join(delimiter);
    this.openPopover('title:' + label);
};

DataTable_RowActions_Transitions.prototype.doOpenPopover = function (link) {
    var ALLOWED_OVERRIDE_PARAMS = ['segment', 'date', 'period', 'idSite'];

    var parts = link.split(':');

    var overrideParams = {};

    var i = 0;
    while (i < parts.length) {
        var paramName = '';

        try {
            paramName = decodeURIComponent(parts[i]);
        } catch (e) {
            // invalid parameter
        }

        if (ALLOWED_OVERRIDE_PARAMS.indexOf(paramName) === -1) {
            i += 1;
            continue;
        }

        overrideParams[paramName] = decodeURIComponent(parts[i + 1]);
        parts.splice(i, 2);
    }

    if (parts.length < 2) {
        return;
    }

    var actionType = parts[0];
    parts.shift();
    var actionName = parts.join(':');

    this.openTransitionsPopover(actionType, actionName, overrideParams);
};

/** The export control below the popover. TransitionSwitcher renders its own for the embedded report. */
DataTable_RowActions_Transitions.prototype.buildExportControl = function () {
    var wrapper = document.createElement('div');
    wrapper.className = 'dataTableWrapper';
    wrapper.innerHTML = '<div class="dataTableFeatures">'
        + '<div class="dataTableFooterNavigation">'
        + '<div class="dataTableControls">'
        + '<div class="row" vue-entry="Transitions.TransitionExporterLink"></div>'
        + '</div></div></div>';

    return wrapper;
};

/** Re-encodes the override params into a row action link, so they survive a popover reload. */
DataTable_RowActions_Transitions.prototype.buildPopoverLink = function (actionType, actionName, overrideParams) {
    var link = '';

    Object.keys(overrideParams || {}).forEach(function (name) {
        link += encodeURIComponent(name) + ':' + encodeURIComponent(overrideParams[name]) + ':';
    });

    return link + actionType + ':' + actionName;
};

/** Opens the row action popover and mounts the Vue renderer into it. */
DataTable_RowActions_Transitions.prototype.openTransitionsPopover = function (actionType, actionName, overrideParams) {
    var self = this;

    var container = Piwik_Popover.showLoading('Transitions', actionName, 550);
    Piwik_Popover.addHelpButton(_pk_externalRawLink('https://matomo.org/docs/transitions'));

    // Piwik_Popover wipes innerHTML *before* running its close callback, so keep a wrapper we can
    // dispatch the destroy on ahead of the wipe.
    var wrapper = document.createElement('div');
    wrapper.className = 'transitionsPopover';

    var entry = document.createElement('div');
    entry.setAttribute('vue-entry', 'Transitions.TransitionsReport');
    // compileVueEntryComponents JSON.parses every attribute, so a value that is itself valid JSON
    // ('2024.10', 'null') would arrive coerced. Encoding makes that parse a round trip.
    // Both come from the popover URL, so both need it.
    entry.setAttribute('action-type', JSON.stringify(actionType));
    entry.setAttribute('action-name', JSON.stringify(actionName));
    entry.setAttribute('override-params', JSON.stringify(overrideParams || {}));
    entry.setAttribute('context', 'popover');
    wrapper.appendChild(entry);

    // Nothing to export until the report is on screen, so wait for Transitions.dataChanged.
    var exportControl = this.buildExportControl();
    exportControl.style.display = 'none';
    wrapper.appendChild(exportControl);

    var onDataChanged = function () {
        exportControl.style.display = '';
    };

    var onReloadPopover = function (params) {
        if (!params || !params.url) {
            return;
        }

        var url = params.url;
        if (actionType == 'url') {
            url = url.replace(/^(?!http)/, 'http://');
        }

        self.openPopover(self.buildPopoverLink(actionType, url, overrideParams));
    };

    var destroyed = false;
    var destroyReport = function () {
        if (destroyed) {
            return;
        }

        destroyed = true;
        window.CoreHome.Matomo.off('Transitions.reloadPopover', onReloadPopover);
        window.CoreHome.Matomo.off('Transitions.dataChanged', onDataChanged);
        piwikHelper.destroyVueComponent(wrapper);
    };

    window.CoreHome.Matomo.on('Transitions.reloadPopover', onReloadPopover);
    window.CoreHome.Matomo.on('Transitions.dataChanged', onDataChanged);

    // Piwik_Popover.setContent() compiles the vue-entry itself, so do not compile it again here.
    Piwik_Popover.setContent(wrapper);

    // setContent() covers a reload; dialogbeforeclose covers the user closing it.
    Piwik_Popover.onClose(destroyReport);
    container.off('dialogbeforeclose.transitions')
        .on('dialogbeforeclose.transitions', destroyReport);
};

DataTable_RowActions_Registry.register({

    name: 'Transitions',

    dataTableIcon: 'icon-transition',

    order: 20,

    dataTableIconTooltip: [
        _pk_translate('General_TransitionsRowActionTooltipTitle'),
        _pk_translate('General_TransitionsRowActionTooltip')
    ],

    createInstance: function (dataTable) {
        return new DataTable_RowActions_Transitions(dataTable);
    },

    isAvailableOnReport: function (dataTableParams) {

        if (piwik.transitionsMaxPeriodAllowed && dataTableParams['period']) {

            if (dataTableParams['period'] === 'range') {

                var piwikPeriods = window.CoreHome.Periods;
                if (piwikPeriods) {
                    var range = piwikPeriods.parse(dataTableParams['period'], dataTableParams['date']);
                    if (range) {
                        var rangeDays = range.getDayCount();
                        if ((piwik.transitionsMaxPeriodAllowed === 'day' && rangeDays > 1) ||
                            (piwik.transitionsMaxPeriodAllowed === 'week' && rangeDays > 7) ||
                            (piwik.transitionsMaxPeriodAllowed === 'month' && rangeDays > 31) ||
                            (piwik.transitionsMaxPeriodAllowed === 'year' && rangeDays > 365))
                        {
                          return false;
                        }
                    }
                }
            } else {
                if (piwik.transitionsMaxPeriodAllowed === 'day' && dataTableParams['period'] !== 'day') {
                    return false;
                }
                if (piwik.transitionsMaxPeriodAllowed === 'week' && dataTableParams['period'] !== 'day'
                    && dataTableParams['period'] !== 'week') {
                    return false;
                }
                if (piwik.transitionsMaxPeriodAllowed === 'month' && dataTableParams['period'] !== 'day'
                    && dataTableParams['period'] !== 'week' && dataTableParams['period'] !== 'month') {
                    return false;
                }
                if (piwik.transitionsMaxPeriodAllowed === 'year' && dataTableParams['period'] !== 'day'
                    && dataTableParams['period'] !== 'week' && dataTableParams['period'] !== 'month'
                    && dataTableParams['period'] !== 'year'
                ) {
                    return false;
                }
            }
        }
        var i = 0;
        for (i; i < DataTable_RowActions_Transitions.registeredReports.length; i++) {
            var report = DataTable_RowActions_Transitions.registeredReports[i];
            if (report
                && report.isAvailableOnReport
                && report.isAvailableOnReport(dataTableParams)) {
                return true;
            }
        }

        return false;
    },

    isAvailableOnRow: function (dataTableParams, tr) {
        if (tr.hasClass('subDataTable') || tr.hasClass('totalsRow')) {
            // not available on groups (i.e. folders)
            return false;
        }

        var i = 0;
        for (i; i < DataTable_RowActions_Transitions.registeredReports.length; i++) {
            var report = DataTable_RowActions_Transitions.registeredReports[i];
            if (report
                && report.isAvailableOnRow
                && report.isAvailableOnReport
                && report.isAvailableOnReport(dataTableParams)) {
                return report.isAvailableOnRow(dataTableParams, tr);
            }
        }

        return true;
    }

});

// --------------------------------------
// MODEL
// --------------------------------------

function Piwik_Transitions_Model(ajax) {
    this.ajax = ajax;

    this.groupTitles = {};
}

Piwik_Transitions_Model.prototype.loadData = function (actionType, actionName, overrideParams, callback) {
    var self = this;

    this.pageviews = 0;
    this.exits = 0;
    this.loops = 0;

    this.directEntries = 0;

    this.searchEnginesNbTransitions = 0;
    this.searchEngines = [];

    this.socialNetworksNbTransitions = 0;
    this.socialNetworks = [];

    this.aiAssistantsNbTransitions = 0;
    this.aiAssistants = [];

    this.websitesNbTransitions = 0;
    this.websites = [];

    this.campaignsNbTransitions = 0;
    this.campaigns = [];

    this.previousPagesNbTransitions = 0;
    this.previousPages = [];

    this.followingPagesNbTransitions = 0;
    this.followingPages = [];

    this.downloadsNbTransitions = 0;
    this.downloads = [];

    this.outlinksNbTransitions = 0;
    this.outlinks = [];

    this.previousSiteSearchesNbTransitions = 0;
    this.previousSiteSearches = [];

    this.followingSiteSearchesNbTransitions = 0;
    this.followingSiteSearches = [];

    this.date = '';

    var params = {
        actionType: actionType,
        actionName: actionName,
        expanded: 1
    };
    if (overrideParams) {
        $.extend(params, overrideParams);
    }

    this.ajax.callApi('Transitions.getTransitionsForAction', params,
        function (report) {
            self.date = report.date;

            // load page metrics
            self.pageviews = report.pageMetrics.pageviews;
            self.loops = report.pageMetrics.loops;
            self.exits = report.pageMetrics.exits;

            // load referrers: split direct entries and others
            for (var i = 0; i < report.referrers.length; i++) {
                var referrer = report.referrers[i];
                if (referrer.shortName == 'direct') {
                    self.directEntries = referrer.visits;
                } else if (referrer.shortName == 'search') {
                    self.searchEnginesNbTransitions = referrer.visits;
                    self.searchEngines = referrer.details;
                    self.groupTitles.searchEngines = referrer.label;
                } else if (referrer.shortName == 'social') {
                    self.socialNetworksNbTransitions = referrer.visits;
                    self.socialNetworks = referrer.details;
                    self.groupTitles.socialNetworks = referrer.label;
                } else if (referrer.shortName == 'ai') {
                    self.aiAssistantsNbTransitions = referrer.visits;
                    self.aiAssistants = referrer.details;
                    self.groupTitles.aiAssistants = referrer.label;
                } else if (referrer.shortName == 'website') {
                    self.websitesNbTransitions = referrer.visits;
                    self.websites = referrer.details;
                    self.groupTitles.websites = referrer.label;
                } else if (referrer.shortName == 'campaign') {
                    self.campaignsNbTransitions = referrer.visits;
                    self.campaigns = referrer.details;
                    self.groupTitles.campaigns = referrer.label;
                }
            }

            self.loadAndSumReport(report, 'previousPages');
            self.loadAndSumReport(report, 'previousSiteSearches');
            self.loadAndSumReport(report, 'followingPages');
            self.loadAndSumReport(report, 'followingSiteSearches');
            self.loadAndSumReport(report, 'downloads');
            self.loadAndSumReport(report, 'outlinks');

            if (typeof Piwik_Transitions_Model.totalNbPageviews == 'undefined') {
                Piwik_Transitions_Model.totalNbPageviews = false;
                self.ajax.loadTotalNbPageviews(function (nbPageviews) {
                    Piwik_Transitions_Model.totalNbPageviews = nbPageviews;
                    self.notifyTotalNbPageviewsLoaded(nbPageviews);
                });
            }

            callback();
        });
};

Piwik_Transitions_Model.prototype.loadAndSumReport = function (apiData, reportName) {
    var data = this[reportName] = apiData[reportName];
    var sumVarName = reportName + 'NbTransitions';

    this[sumVarName] = 0;
    for (var i = 0; i < data.length; i++) {
        this[sumVarName] += data[i].referrals;
    }
};

Piwik_Transitions_Model.totalNbPageviewsCallbacks = [];

/**
 * Whether the total has resolved, whatever the answer. Separate from the value because the value can
 * resolve falsy -- Actions.get may answer without one, or fail -- and the request is fired only once
 * per page, so "no total" must not read as "not back yet" or later callers queue forever.
 */
Piwik_Transitions_Model.totalNbPageviewsSettled = false;

/**
 * Calls back with the site's total pageviews, now or once it arrives. The request is fired in
 * parallel with the first report, so on that report it is still in flight. `false` means the total
 * resolved without a value.
 */
Piwik_Transitions_Model.prototype.whenTotalNbPageviewsLoaded = function (callback) {
    if (Piwik_Transitions_Model.totalNbPageviewsSettled) {
        callback(this.getTotalNbPageviews());
        return;
    }

    Piwik_Transitions_Model.totalNbPageviewsCallbacks.push(callback);
};

/** Marks the total resolved and drains its waiters. Call on every path, failure included. */
Piwik_Transitions_Model.prototype.notifyTotalNbPageviewsLoaded = function (nbPageviews) {
    Piwik_Transitions_Model.totalNbPageviewsSettled = true;

    var waiting = Piwik_Transitions_Model.totalNbPageviewsCallbacks;
    Piwik_Transitions_Model.totalNbPageviewsCallbacks = [];

    for (var i = 0; i < waiting.length; i++) {
        waiting[i](nbPageviews);
    }
};

Piwik_Transitions_Model.prototype.getTotalNbPageviews = function () {
    if (typeof Piwik_Transitions_Model.totalNbPageviews == 'undefined') {
        return false;
    }
    return Piwik_Transitions_Model.totalNbPageviews;
};

Piwik_Transitions_Model.prototype.getGroupTitle = function (groupName) {
    if (typeof this.groupTitles[groupName] != 'undefined') {
        return this.groupTitles[groupName];
    }
    return groupName;
};

Piwik_Transitions_Model.prototype.getDetailsForGroup = function (groupName) {
    return this.addPercentagesToData(this[groupName]);
};

Piwik_Transitions_Model.prototype.getPercentage = function (metric, formatted) {
    var percentage = (this.pageviews == 0 ? 0 : this[metric] / this.pageviews);

    if (formatted) {

        percentage = this.roundPercentage(percentage);
        return NumberFormatter.formatPercent(percentage);
    }

    return percentage;
};

Piwik_Transitions_Model.prototype.addPercentagesToData = function (data) {
    var total = 0;

    for (var i = 0; i < data.length; i++) {
        total += parseInt(data[i].referrals, 10);
    }

    for (i = 0; i < data.length; i++) {
        data[i].percentage = this.roundPercentage(data[i].referrals / total);
    }

    return data;
};

Piwik_Transitions_Model.prototype.roundPercentage = function (value) {
    if (value < .1) {
        return Math.round(value * 1000) / 10.0;
    } else {
        return Math.round(value * 100);
    }
};

// --------------------------------------
// AJAX
// --------------------------------------

function Piwik_Transitions_Ajax() {
}

Piwik_Transitions_Ajax.prototype.loadTotalNbPageviews = function (callback) {
    this.callApi('Actions.get', {
        columns: 'nb_pageviews'
    }, function (response) {
        var value = typeof response.value != 'undefined' ? response.value : false;
        callback(value);
    });
};

/**
 * Receives an API error as (exception name, request params). The renderer sets this because it
 * shows errors itself; without one an error is dropped and the request's callback never runs.
 */
Piwik_Transitions_Ajax.prototype.setErrorCallback = function (callback) {
    this.errorCallback = callback;
};

Piwik_Transitions_Ajax.prototype.callApi = function (method, params, callback) {
    var self = this;

    params.format = 'JSON';
    params.module = 'API';
    params.method = method;
    params.filter_limit = '-1';

    var ajaxRequest = new ajaxHelper();
    ajaxRequest.addParams(params, 'get');
    ajaxRequest.useCallbackInCaseOfError();
    ajaxRequest.setCallback(
        function (result) {
            if (typeof result.result != 'undefined' && result.result == 'error') {
                if (typeof self.errorCallback == 'function') {
                    self.errorCallback(result.message, params);
                }
                return;
            }

            callback(result);
        }
    );
    ajaxRequest.send();
};
