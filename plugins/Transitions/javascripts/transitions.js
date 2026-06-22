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
    this.transitions = null;
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

    if (this.transitions === null) {
        this.transitions = new Piwik_Transitions(actionType, actionName, this, overrideParams);
    } else {
        this.transitions.reset(actionType, actionName, segment);
    }
    this.transitions.showPopover();
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

//
// TRANSITIONS IMPLEMENTATION
//

function Piwik_Transitions(actionType, actionName, rowAction, overrideParams) {
    this.reset(actionType, actionName, overrideParams);
    this.rowAction = rowAction;

    this.ajax = new Piwik_Transitions_Ajax();
    this.model = new Piwik_Transitions_Model(this.ajax);

    this.leftGroups = ['previousPages', 'previousSiteSearches', 'searchEngines', 'socialNetworks', 'aiAssistants', 'websites', 'campaigns'];
    this.rightGroups = ['followingPages', 'followingSiteSearches', 'downloads', 'outlinks'];
}

Piwik_Transitions.prototype.reset = function (actionType, actionName, overrideParams) {
    this.actionType = actionType;
    this.actionName = actionName;
    this.overrideParams = overrideParams;

    this.popover = null;
    this.centerBox = null;
    this.container = null;
    this.ribbons = null;
    this.ribbonRows = {left: [], right: []};

    this.leftOpenGroup = 'previousPages';
    this.rightOpenGroup = 'followingPages';

    this.highlightedGroup = false;
    this.highlightedGroupSide = false;
    this.highlightedGroupCenterEl = false;
};

/** Open the popover */
Piwik_Transitions.prototype.showPopover = function (showEmbeddedInReport) {
    var self = this;
    this.showEmbeddedInReport = showEmbeddedInReport;
    Piwik_Transitions.currentInstance = this;

    $('#transitions_report .popoverContainer').hide();

    if (showEmbeddedInReport) {
        this.popover = $('#transitions_report');
        $('#Transitions_Error_Container').hide();
        $('#transitions_inline_loading').show();
    } else {
        this.popover = Piwik_Popover.showLoading('Transitions', self.actionName, 1180);
        Piwik_Popover.addHelpButton(_pk_externalRawLink('https://matomo.org/docs/transitions'));
    }

    var bothLoaded = function () {
        if (showEmbeddedInReport) {
            $('#transitions_inline_loading').hide();
        }

        self.restorePopoverContent();
        self.model.htmlLoaded();
        self.renderPopover();
    };

    // load the popover HTML (only done once)
    var callbackForHtml = false;
    if (typeof Piwik_Transitions.popoverHtml == 'undefined') {
        this.ajax.callTransitionsController('renderPopover', function (html) {
            Piwik_Transitions.popoverHtml = html;
            if (callbackForHtml !== false) {
                callbackForHtml();
            }
        });
    }

    // load the data
    self.model.loadData(self.actionType, self.actionName, self.overrideParams, function () {
        if (typeof Piwik_Transitions.popoverHtml == 'undefined') {
            // html not there yet
            callbackForHtml = bothLoaded;
        } else {
            // html already loaded
            bothLoaded();
        }
    });
};

/** Prepare the popover with the basic DOM to add data later. */
Piwik_Transitions.prototype.preparePopover = function () {
    var self = this;

    this.container = self.popover.find('#Transitions_Container');
    self.centerBox = self.popover.find('#Transitions_CenterBox');

    // collect references to the DOM containers used while rendering the columns
    var prepareSide = function (columnSelector) {
        var column = self.container.find(columnSelector);
        var openGroup = column.find('.Transitions_OpenGroup');
        var otherSources = column.find('.Transitions_OtherSources');
        return {
            openTitle: openGroup.find('.Transitions_SectionTitle'),
            openCount: openGroup.find('.Transitions_SectionCount'),
            openRows: openGroup.find('.Transitions_Rows'),
            otherHead: otherSources.find('.Transitions_SectionHead'),
            otherCount: otherSources.find('.Transitions_SectionCount'),
            otherRows: otherSources.find('.Transitions_Rows')
        };
    };

    this.dom = {
        left: prepareSide('.Transitions_LeftColumn'),
        right: prepareSide('.Transitions_RightColumn')
    };

    // set the icon of the center box badge depending on the action type
    self.centerBox.find('.Transitions_PageBadgeIcon')
        .addClass(self.actionType == 'url' ? 'icon-outlink' : 'icon-document');

    // ribbon helper draws the flow connections between the rows and the center box
    this.ribbons = new Piwik_Transitions_Ribbons(self.popover.find('#Transitions_Ribbons'), this.container);

    var title = self.actionName;
    if (self.actionType == 'url') {
        title = Piwik_Transitions_Util.shortenUrl(title, true);
    }
    var h2 = self.centerBox.find('h2');
    var textContainer = h2;
    if (self.actionType == 'url') {
        var a = $(document.createElement('a'));
        a.attr('href', self.actionName);
        a.attr('rel', 'noreferrer noopener');
        a.attr('target', '_blank');
        h2.append(a);
        textContainer = a;
    }

    textContainer.addClass('Transitions_ApplyTextAndTruncate')
        .data('text', title)
        .data('maxLines', 3)
        .html(piwikHelper.addBreakpointsToUrl(title));

    var element = textContainer.add(self.popover.find('p.Transitions_Pageviews'));

    element.tooltip({
        track: true,
        content: function () {
            var totalNbPageviews = self.model.getTotalNbPageviews();
            if (totalNbPageviews > 0) {

                var share = NumberFormatter.formatPercent(Math.round(self.model.pageviews / totalNbPageviews * 1000) / 10);

                var text = Piwik_Transitions_Translations.ShareOfAllPageviews;
                text = sprintf(text, NumberFormatter.formatNumber(self.model.pageviews), share);
                text += '<br /><em>' + Piwik_Transitions_Translations.DateRange + ' ' + self.model.date + '</em>';

                var title = '<h3>' + piwikHelper.addBreakpointsToUrl(self.actionName) + '</h3>';

                return title + text;
            }
            return false;
        },
        items:        '*',
        tooltipClass: 'Transitions_Tooltip_Small',
        show: false,
        hide: false
    });

    if (!Piwik_Transitions.resizeHandlerBound) {
        Piwik_Transitions.resizeHandlerBound = true;
        $(window).on('resize.transitions', function () {
            if (Piwik_Transitions.currentInstance) {
                Piwik_Transitions.currentInstance.drawRibbons();
            }
        });
    }
};

/** Render the popover content */
Piwik_Transitions.prototype.render = function () {
    this.ribbonRows = {left: [], right: []};

    this.renderCenterBox();

    this.renderLeftSide();
    this.renderRightSide();

    this.renderLoops();

    this.drawRibbons();

    window.CoreHome.Matomo.postEvent('Transitions.dataChanged', {'actionType': this.actionType, 'actionName': this.actionName});
};

Piwik_Transitions.prototype.restorePopoverContent = function () {
    if (this.showEmbeddedInReport) {
        this.popover.find('.popoverContainer').html(Piwik_Transitions.popoverHtml).show();
    } else {
        Piwik_Popover.setContent(Piwik_Transitions.popoverHtml);
    }
};

Piwik_Transitions.prototype.renderPopover = function () {
    this.preparePopover();
    this.render();

    // redraw the ribbons once the popover layout has fully settled (fonts, animations, ...)
    var self = this;
    setTimeout(function () {
        self.drawRibbons();
    }, 60);
};

Piwik_Transitions.prototype.refreshTheme = function () {
    if (!this.popover || !this.popover.length
        || !$.contains(document.documentElement, this.popover[0])
        || typeof Piwik_Transitions.popoverHtml == 'undefined') {
        return;
    }

    this.restorePopoverContent();
    this.renderPopover();
};

/** Render left side: referrer groups & direct entries */
Piwik_Transitions.prototype.renderLeftSide = function () {
    this.clearSide('left');
    this.renderGroups(this.leftGroups, this.leftOpenGroup, 'left');
    this.renderEntries();
    this.renderOtherSourcesCount('left', this.leftGroups, this.leftOpenGroup, this.model.directEntries);
};

/** Render right side: following pages & exits */
Piwik_Transitions.prototype.renderRightSide = function () {
    this.clearSide('right');
    this.renderGroups(this.rightGroups, this.rightOpenGroup, 'right');
    this.renderExits();
    this.renderOtherSourcesCount('right', this.rightGroups, this.rightOpenGroup, this.model.exits);
};

/** Show the total pageviews of all collapsed "other" rows in the section heading */
Piwik_Transitions.prototype.renderOtherSourcesCount = function (side, groups, openGroup, extra) {
    var total = extra || 0;
    for (var i = 0; i < groups.length; i++) {
        if (groups[i] !== openGroup) {
            total += this.model[groups[i] + 'NbTransitions'] || 0;
        }
    }
    this.dom[side].otherCount.html(total > 0 ? this.getCountHtml('previousPages', total) : '');
};

/** Helper method to render open and closed groups for both sides */
Piwik_Transitions.prototype.renderGroups = function (groups, openGroup, side) {
    for (var i = 0; i < groups.length; i++) {
        var groupName = groups[i];
        if (groupName == openGroup) {
            this.renderOpenGroup(groupName, side);
        } else {
            this.renderClosedGroup(groupName, side);
        }
    }
};

/** Empty the DOM rows of one side and reset its ribbon bookkeeping */
Piwik_Transitions.prototype.clearSide = function (side) {
    this.ribbonRows[side] = [];
    this.dom[side].openRows.empty();
    this.dom[side].otherRows.empty();
    this.dom[side].openTitle.text('');
    this.dom[side].openCount.text('');
};

/**
 * Map a group (or the "direct"/"exits" pseudo groups) to an icon font class.
 */
Piwik_Transitions.prototype.getGroupIcon = function (groupName) {
    var icons = {
        previousPages: 'icon-document',
        followingPages: 'icon-document',
        previousSiteSearches: 'icon-search',
        followingSiteSearches: 'icon-search',
        searchEngines: 'icon-search',
        socialNetworks: 'icon-users',
        aiAssistants: 'icon-ai',
        websites: 'icon-outlink',
        campaigns: 'icon-dollar-sign',
        directEntries: 'icon-sign-in',
        outlinks: 'icon-outlink',
        downloads: 'icon-download',
        exits: 'icon-sign-out'
    };
    return icons[groupName] || 'icon-document';
};

/**
 * Build a single pill row element and register it for the ribbon drawing.
 *
 * params (object):
 *   side, group, icon, label, labelTooltip, count, percentage, share, target, onClick
 */
Piwik_Transitions.prototype.buildRow = function (params) {
    var self = this;

    var row = $(document.createElement('div')).addClass('Transitions_Row');
    row.attr('data-group', params.group);

    if (params.icon) {
        $(document.createElement('span')).addClass('Transitions_RowIcon ' + params.icon).appendTo(row);
    }

    var body = $(document.createElement('div')).addClass('Transitions_RowBody').appendTo(row);

    var labelEl = $(document.createElement('div')).addClass('Transitions_RowLabel');
    labelEl.html(piwikHelper.addBreakpointsToUrl(params.label));
    if (params.labelTooltip) {
        labelEl.attr('title', params.labelTooltip);
    }
    body.append(labelEl);

    if (typeof params.count !== 'undefined' && params.count !== false) {
        $(document.createElement('div')).addClass('Transitions_RowMeta')
            .html(params.count).appendTo(body);
    }

    if (typeof params.percentage !== 'undefined' && params.percentage !== false) {
        $(document.createElement('div')).addClass('Transitions_RowPercentage')
            .text(params.percentage).appendTo(row);
    }

    if (params.onClick) {
        row.addClass('Transitions_RowClickable');
        if (typeof params.onClick === 'function') {
            row.on('click', params.onClick);
        } else {
            // open external link in a new tab
            (function (href) {
                row.on('click', function () {
                    window.open(href, '_blank', 'noopener,noreferrer');
                });
            })(params.onClick);
        }
    }

    row.hover(function () {
        self.highlightGroup(params.group, params.side);
    }, function () {
        self.unHighlightGroup(params.group, params.side);
    });

    (params.target === 'other' ? this.dom[params.side].otherRows : this.dom[params.side].openRows).append(row);

    if (params.share > 0) {
        this.ribbonRows[params.side].push({
            el: row,
            share: params.share,
            group: params.group
        });
    }

    return row;
};

/** Render the center box with the main metrics */
Piwik_Transitions.prototype.renderCenterBox = function () {
    var box = this.centerBox;

    Piwik_Transitions_Util.replacePlaceholderInHtml(
        box.find('.Transitions_Pageviews'), NumberFormatter.formatNumber(this.model.pageviews));

    var self = this;
    var showMetric = function (cssClass, modelProperty, highlightCurveOnSide, groupCanBeExpanded) {
        var el = box.find('.Transitions_' + cssClass);
        Piwik_Transitions_Util.replacePlaceholderInHtml(el, NumberFormatter.formatNumber(self.model[modelProperty]));

        if (self.model[modelProperty] == 0) {
            el.addClass('Transitions_Value0');
        } else {
            self.addTooltipShowingPercentageOfAllPageviews(el, modelProperty);
            var groupName = cssClass.charAt(0).toLowerCase() + cssClass.slice(1);
            // the AI assistants group keeps both letters capitalised in its css class
            if (cssClass === 'AIAssistants') {
                groupName = 'aiAssistants';
            }
            el.hover(function () {
                self.highlightGroup(groupName, highlightCurveOnSide);
            }, function () {
                self.unHighlightGroup(groupName, highlightCurveOnSide);
            });
            if (groupCanBeExpanded) {
                el.click(function () {
                    self.openGroup(highlightCurveOnSide, groupName);
                }).css('cursor', 'pointer');
            }
        }
    };

    showMetric('DirectEntries', 'directEntries', 'left', false);
    showMetric('PreviousSiteSearches', 'previousSiteSearchesNbTransitions', 'left', true);
    showMetric('PreviousPages', 'previousPagesNbTransitions', 'left', true);
    showMetric('SearchEngines', 'searchEnginesNbTransitions', 'left', true);
    showMetric('SocialNetworks', 'socialNetworksNbTransitions', 'left', true);
    showMetric('AIAssistants', 'aiAssistantsNbTransitions', 'left', true);
    showMetric('Websites', 'websitesNbTransitions', 'left', true);
    showMetric('Campaigns', 'campaignsNbTransitions', 'left', true);

    showMetric('FollowingPages', 'followingPagesNbTransitions', 'right', true);
    showMetric('FollowingSiteSearches', 'followingSiteSearchesNbTransitions', 'right', true);
    showMetric('Outlinks', 'outlinksNbTransitions', 'right', true);
    showMetric('Downloads', 'downloadsNbTransitions', 'right', true);
    showMetric('Exits', 'exits', 'right', false);

    var m = this.model;
    var incomingTotal = m.previousPagesNbTransitions + m.previousSiteSearchesNbTransitions
        + m.searchEnginesNbTransitions + m.socialNetworksNbTransitions + m.aiAssistantsNbTransitions
        + m.websitesNbTransitions + m.campaignsNbTransitions + m.directEntries;
    var outgoingTotal = m.followingPagesNbTransitions + m.followingSiteSearchesNbTransitions
        + m.downloadsNbTransitions + m.outlinksNbTransitions + m.exits;

    box.find('.Transitions_IncomingTotal').text(NumberFormatter.formatNumber(incomingTotal));
    box.find('.Transitions_OutgoingTotal').text(NumberFormatter.formatNumber(outgoingTotal));

    box.find('.Transitions_CenterBoxMetrics').show();
};

Piwik_Transitions.prototype.addTooltipShowingPercentageOfAllPageviews = function (element, metric) {
    var tip = Piwik_Transitions_Translations.XOfAllPageviews;
    var percentage = this.model.getPercentage(metric, true);
    tip = sprintf(tip, '<strong>' + percentage + '</strong>');

    element.tooltip({
        track: true,
        content: tip,
        items: '*',
        tooltipClass: 'Transitions_Tooltip_Small',
        show: false,
        hide: false
    });
};

/** Render the loops (i.e. page reloads) as a footer line of the center box */
Piwik_Transitions.prototype.renderLoops = function () {
    var loops = this.popover.find('#Transitions_Loops');
    if (this.model.loops == 0) {
        loops.hide();
        return;
    }

    loops.show();
    Piwik_Transitions_Util.replacePlaceholderInHtml(
        loops.find('.Transitions_LoopsText'), NumberFormatter.formatNumber(this.model.loops));

    this.addTooltipShowingPercentageOfAllPageviews(loops, 'loops');
};

/** Translation template ("%s pageviews"/"%s downloads"/...) for a group's row count */
Piwik_Transitions.prototype.getCountTemplate = function (groupName) {
    if (groupName == 'downloads') {
        return Piwik_Transitions_Translations.downloadsInline;
    }
    if (groupName == 'outlinks') {
        return Piwik_Transitions_Translations.outlinksInline;
    }
    return Piwik_Transitions_Translations.pageviewsInline;
};

/** "232 pageviews" style count for a row */
Piwik_Transitions.prototype.getCountHtml = function (groupName, value) {
    return sprintf(this.getCountTemplate(groupName), NumberFormatter.formatNumber(value));
};

/** "247 pageviews (14%)" style meta shown on collapsed "other" rows */
Piwik_Transitions.prototype.getOtherMetaHtml = function (groupName, value) {
    var pctVal = this.model.pageviews == 0 ? 0 : this.model.roundPercentage(value / this.model.pageviews);
    return this.getCountHtml(groupName, value) + ' (' + NumberFormatter.formatPercent(pctVal) + ')';
};

Piwik_Transitions.prototype.renderEntries = function () {
    if (this.model.directEntries > 0) {
        this.buildRow({
            side: 'left',
            target: 'other',
            group: 'directEntries',
            icon: this.getGroupIcon('directEntries'),
            label: Piwik_Transitions_Translations.directEntries,
            percentage: this.getOtherMetaHtml('directEntries', this.model.directEntries),
            share: this.model.getPercentage('directEntries')
        });
    }
};

Piwik_Transitions.prototype.renderExits = function () {
    if (this.model.exits > 0) {
        this.buildRow({
            side: 'right',
            target: 'other',
            group: 'exits',
            icon: this.getGroupIcon('exits'),
            label: Piwik_Transitions_Translations.exits,
            percentage: this.getOtherMetaHtml('exits', this.model.exits),
            share: this.model.getPercentage('exits')
        });
    }
};

/** Render the open group with the detailed data as a list of pill rows */
Piwik_Transitions.prototype.renderOpenGroup = function (groupName, side) {
    var self = this;

    // get data from the model
    var nbTransitionsVarName = groupName + 'NbTransitions';
    var nbTransitions = self.model[nbTransitionsVarName];

    // headline of the open group
    this.dom[side].openTitle.text(self.model.getGroupTitle(groupName));
    this.dom[side].openCount.html(nbTransitions > 0 ? this.getCountHtml(groupName, nbTransitions) : '');

    if (nbTransitions == 0) {
        return;
    }

    var details = self.model.getDetailsForGroup(groupName);

    // build detail rows
    for (var i = 0; i < details.length; i++) {
        var data = details[i];
        var label = (typeof data.url != 'undefined' ? data.url : data.label);
        label = (typeof label != 'undefined' && label !== null ? label : '');
        var isOthers = (label == 'Others');
        var onClick = false;
        if (!isOthers && (groupName == 'previousPages' || groupName == 'followingPages')) {

            if (this.showEmbeddedInReport) {
                onClick = (function (url) {
                    return function () {
                        window.CoreHome.Matomo.postEvent('Transitions.switchTransitionsUrl', {
                            url: url,
                        });
                    };
                })(label);
            } else {
                onClick = (function (url) {
                    return function () {
                        if (self.actionType == 'url') {
                            url = url.replace(/^(?!http)/, 'http://');
                        }
                        self.reloadPopover(url);
                    };
                })(label);
            }

        } else if (!isOthers && (groupName == 'outlinks' || groupName == 'websites' || groupName == 'downloads')) {
            onClick = label
        }

        var fullLabel = label;
        var shortened = false;
        if ((this.actionType == 'url' && (groupName == 'previousPages' || groupName == 'followingPages'))
            || groupName == 'downloads') {
            // remove http + www + domain for internal URLs
            label = Piwik_Transitions_Util.shortenUrl(label, true);
            shortened = true;
        } else if (groupName == 'outlinks' || groupName == 'websites') {
            // remove http + www for external URLs
            label = Piwik_Transitions_Util.shortenUrl(label);
            shortened = true;
        }

        this.buildRow({
            side: side,
            target: 'open',
            group: groupName,
            icon: this.getGroupIcon(isOthers ? 'previousPages' : groupName),
            label: isOthers ? Piwik_Transitions_Translations.others || label : label,
            labelTooltip: isOthers || !shortened ? false : fullLabel,
            count: this.getCountHtml(groupName, data.referrals),
            percentage: NumberFormatter.formatPercent(data.percentage),
            share: this.model.pageviews == 0 ? 0 : data.referrals / this.model.pageviews,
            onClick: onClick
        });
    }
};

/** Render a collapsed group as a single "other sources" row that can be expanded */
Piwik_Transitions.prototype.renderClosedGroup = function (groupName, side) {
    var self = this;

    var nbTransitionsVarName = groupName + 'NbTransitions';
    var nbTransitions = self.model[nbTransitionsVarName];

    if (nbTransitions == 0) {
        return;
    }

    this.buildRow({
        side: side,
        target: 'other',
        group: groupName,
        icon: this.getGroupIcon(groupName),
        label: self.model.getGroupTitle(groupName),
        percentage: this.getOtherMetaHtml(groupName, nbTransitions),
        share: self.model.getPercentage(nbTransitionsVarName),
        onClick: function () {
            self.unHighlightGroup(groupName, side);
            self.openGroup(side, groupName);
        }
    });
};

/** Reload the entire popover for a different URL */
Piwik_Transitions.prototype.reloadPopover = function (url) {
    if (this.rowAction) {
        this.rowAction.openPopover(this.actionType + ':' + url);
    } else {
        this.reset(this.actionType, url);
        this.showPopover();
    }
};

/** Redraw the left or right side with a different group opened */
Piwik_Transitions.prototype.openGroup = function (side, groupName) {
    if (side == 'left') {
        this.leftOpenGroup = groupName;
        this.renderLeftSide();
    } else {
        this.rightOpenGroup = groupName;
        this.renderRightSide();
    }

    this.drawRibbons();
};

/** Highlight a group: emphasize its ribbon(s), row(s) and the metric in the center box */
Piwik_Transitions.prototype.highlightGroup = function (groupName, side) {
    if (this.highlightedGroup == groupName) {
        return;
    }
    if (this.highlightedGroup !== false) {
        this.unHighlightGroup(this.highlightedGroup, this.highlightedGroupSide);
    }

    this.highlightedGroup = groupName;
    this.highlightedGroupSide = side;

    var cssSuffix = groupName === 'aiAssistants'
        ? 'AIAssistants'
        : groupName.charAt(0).toUpperCase() + groupName.slice(1);
    var cssClass = 'Transitions_' + cssSuffix;
    this.highlightedGroupCenterEl = this.container.find('.Transitions_CenterBoxMetrics .' + cssClass);
    this.highlightedGroupCenterEl.addClass('Transitions_Highlighted');

    this.container.find('.Transitions_Row[data-group="' + groupName + '"]').addClass('Transitions_Highlighted');

    if (this.ribbons) {
        this.ribbons.highlight(groupName, true);
    }
};

/** Remove highlight after using highlightGroup() */
Piwik_Transitions.prototype.unHighlightGroup = function (groupName, side) {
    if (this.highlightedGroup === false) {
        return;
    }

    var highlighted = this.highlightedGroup;

    this.highlightedGroupCenterEl.removeClass('Transitions_Highlighted');
    this.container.find('.Transitions_Row[data-group="' + highlighted + '"]').removeClass('Transitions_Highlighted');

    if (this.ribbons) {
        this.ribbons.highlight(highlighted, false);
    }

    this.highlightedGroup = false;
    this.highlightedGroupSide = false;
    this.highlightedGroupCenterEl = false;
};

// --------------------------------------
// RIBBONS
// --------------------------------------

/**
 * Draws the SVG flow ribbons that connect the side rows to the center box.
 */
function Piwik_Transitions_Ribbons(svg, container) {
    this.svg = svg;
    this.svgEl = svg[0];
    this.container = container;
}

Piwik_Transitions_Ribbons.prototype.clear = function () {
    $(this.svgEl).find('path').remove();
};

Piwik_Transitions_Ribbons.prototype.resize = function () {
    var w = this.container.prop('scrollWidth') || this.container.innerWidth();
    var h = this.container.prop('scrollHeight') || this.container.innerHeight();
    this.width = w;
    this.height = h;
    this.svgEl.setAttribute('width', w);
    this.svgEl.setAttribute('height', h);
    this.svgEl.setAttribute('viewBox', '0 0 ' + w + ' ' + h);
};

/**
 * Append a single filled bezier ribbon.
 *
 * @param x0,y0   start point (vertical center of the ribbon at the start edge)
 * @param x1,y1   end point (vertical center of the ribbon at the end edge)
 * @param t0      ribbon thickness at the start
 * @param t1      ribbon thickness at the end
 * @param side    left|right (defines the gradient/color used)
 * @param group   group name (used to highlight matching ribbons)
 */
Piwik_Transitions_Ribbons.prototype.addRibbon = function (x0, y0, x1, y1, t0, t1, side, group) {
    var top0 = y0 - t0 / 2, bot0 = y0 + t0 / 2;
    var top1 = y1 - t1 / 2, bot1 = y1 + t1 / 2;
    var cx = (x0 + x1) / 2;

    var d = 'M' + x0 + ',' + top0
        + ' C' + cx + ',' + top0 + ' ' + cx + ',' + top1 + ' ' + x1 + ',' + top1
        + ' L' + x1 + ',' + bot1
        + ' C' + cx + ',' + bot1 + ' ' + cx + ',' + bot0 + ' ' + x0 + ',' + bot0
        + ' Z';

    var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    path.setAttribute('d', d);
    path.setAttribute('class', 'Transitions_Ribbon Transitions_Ribbon_' + side);
    path.setAttribute('data-group', group);
    this.svgEl.appendChild(path);
};

/** Toggle the highlighted state for all ribbons of a group */
Piwik_Transitions_Ribbons.prototype.highlight = function (group, on) {
    $(this.svgEl).find('path[data-group="' + group + '"]')
        .toggleClass('Transitions_Highlighted', !!on);
};

/** Draw all ribbons (both sides) based on the rows registered while rendering */
Piwik_Transitions.prototype.drawRibbons = function () {
    if (!this.ribbons || !this.container || !this.container.length) {
        return;
    }
    this.ribbons.resize();
    this.ribbons.clear();
    this.drawRibbonsForSide('left');
    this.drawRibbonsForSide('right');
};

Piwik_Transitions.prototype.drawRibbonsForSide = function (side) {
    var rows = this.ribbonRows && this.ribbonRows[side];
    if (!rows || !rows.length) {
        return;
    }

    var containerEl = this.container[0];
    var centerEl = this.centerBox[0];
    if (!containerEl || !centerEl) {
        return;
    }

    var containerRect = containerEl.getBoundingClientRect();
    var cardRect = centerEl.getBoundingClientRect();

    var cardLeftX = cardRect.left - containerRect.left;
    var cardRightX = cardRect.right - containerRect.left;
    var cardTopY = cardRect.top - containerRect.top;

    // vertical band on the card edge that the ribbons connect to
    var bandHeight = Math.max(cardRect.height * 0.8, 1);
    var bandTop = cardTopY + (cardRect.height - bandHeight) / 2;

    var totalShare = 0;
    for (var i = 0; i < rows.length; i++) {
        totalShare += rows[i].share;
    }
    if (totalShare <= 0) {
        return;
    }

    var scale = bandHeight / totalShare;
    var cursor = bandTop;

    for (i = 0; i < rows.length; i++) {
        var row = rows[i];
        var rowEl = row.el[0];
        if (!rowEl) {
            continue;
        }
        var rowRect = rowEl.getBoundingClientRect();
        var rowMidY = rowRect.top - containerRect.top + rowRect.height / 2;

        var thickCenter = Math.max(row.share * scale, 1.5);
        var thickRow = Math.min(thickCenter, rowRect.height - 6);
        thickRow = Math.max(thickRow, 1.5);

        var centerY = cursor + thickCenter / 2;
        cursor += thickCenter;

        if (side == 'left') {
            var startX = rowRect.right - containerRect.left - 2;
            this.ribbons.addRibbon(startX, rowMidY, cardLeftX, centerY, thickRow, thickCenter, side, row.group);
        } else {
            var endX = rowRect.left - containerRect.left + 2;
            this.ribbons.addRibbon(cardRightX, centerY, endX, rowMidY, thickCenter, thickRow, side, row.group);
        }
    }
};

window.addEventListener('themeModeChange', function () {
    if (Piwik_Transitions.currentInstance) {
        Piwik_Transitions.currentInstance.refreshTheme();
    }
});

// --------------------------------------
// MODEL
// --------------------------------------

function Piwik_Transitions_Model(ajax) {
    this.ajax = ajax;

    this.groupTitles = {};
}

Piwik_Transitions_Model.prototype.htmlLoaded = function () {
    this.groupTitles.previousPages = Piwik_Transitions_Translations.fromPreviousPages;
    this.groupTitles.previousSiteSearches = Piwik_Transitions_Translations.fromPreviousSiteSearches;
    this.groupTitles.followingPages = Piwik_Transitions_Translations.toFollowingPages;
    this.groupTitles.followingSiteSearches = Piwik_Transitions_Translations.toFollowingSiteSearches;
    this.groupTitles.outlinks = Piwik_Transitions_Translations.outlinks;
    this.groupTitles.downloads = Piwik_Transitions_Translations.downloads;

    this.shareInGroupTexts = {
        previousPages: Piwik_Transitions_Translations.fromPreviousPagesInline,
        previousSiteSearches: Piwik_Transitions_Translations.fromPreviousSiteSearchesInline,
        followingPages: Piwik_Transitions_Translations.toFollowingPagesInline,
        followingSiteSearches: Piwik_Transitions_Translations.toFollowingSiteSearchesInline,
        searchEngines: Piwik_Transitions_Translations.fromSearchEnginesInline,
        socialNetworks: Piwik_Transitions_Translations.fromSocialNetworksInline,
        aiAssistants: Piwik_Transitions_Translations.fromAIAssistantsInline,
        websites: Piwik_Transitions_Translations.fromWebsitesInline,
        campaigns: Piwik_Transitions_Translations.fromCampaignsInline,
        outlinks: Piwik_Transitions_Translations.outlinksInline,
        downloads: Piwik_Transitions_Translations.downloadsInline
    };
};

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

Piwik_Transitions_Model.prototype.getShareInGroupTooltip = function (share, groupName) {
    var tip = this.shareInGroupTexts[groupName];
    return sprintf(tip, share);
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

Piwik_Transitions_Ajax.prototype.callTransitionsController = function (action, callback) {
    var ajaxRequest = new ajaxHelper();
    ajaxRequest.addParams({
        module: 'Transitions',
        action: action
    }, 'get');
    ajaxRequest.setCallback(callback);
    ajaxRequest.setFormat('html');
    ajaxRequest.send();
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
                var errorName = result.message;
                var showError = function () {
                    var errorTitle, errorMessage, errorBack;
                    if (typeof Piwik_Transitions_Translations[errorName] == 'undefined') {
                        errorTitle = 'Exception';
                        errorMessage = errorName;
                        errorBack = '<<<';
                    } else {
                        errorTitle = Piwik_Transitions_Translations[errorName];
                        errorMessage = Piwik_Transitions_Translations[errorName + 'Details'];
                        errorBack = Piwik_Transitions_Translations[errorName + 'Back'];
                    }

                    if (typeof params.actionName != 'undefined') {
                        var url = params.actionName;
                        url = piwikHelper.addBreakpointsToUrl(url);
                        errorTitle = sprintf(errorTitle, '<span>' + url + '</span>');
                    }

                    errorMessage = sprintf(errorMessage, '<br />');
                    var inlineErrorNode = $('#Transitions_Error_Container');
                    if (inlineErrorNode.length) {
                        // viewing it as report, not popover
                        inlineErrorNode.html('');
                        var theContentNode = $(document.createElement('div')).addClass('Piwik_Popover_Error');

                        var p = $(document.createElement('p')).addClass('Piwik_Popover_Error_Title');
                        theContentNode.append(p.html(errorTitle));

                        if (errorMessage) {
                            p = $(document.createElement('p')).addClass('Piwik_Popover_Error_Message');
                            theContentNode.append(p.html(errorMessage));
                        }
                        inlineErrorNode.append(theContentNode);
                        inlineErrorNode.show();
                        $('#transitions_report .popoverContainer').hide();
                    } else {
                        Piwik_Popover.showError(errorTitle, errorMessage, errorBack);
                    }

                  $('#transitions_inline_loading').hide();
                };

                if (typeof Piwik_Transitions_Translations == 'undefined') {
                    self.callApi('Transitions.getTranslations', {}, function (response) {
                        if (typeof response == 'object') {
                            Piwik_Transitions_Translations = response;
                        } else {
                            Piwik_Transitions_Translations = {};
                        }
                        showError();
                    });
                } else {
                    showError();
                }
            }
            else {
                callback(result);
            }
        }
    );
    ajaxRequest.send();
};

// --------------------------------------
// STATIC UTIL FUNCTIONS
// --------------------------------------

Piwik_Transitions_Util = {

    /**
     * Removes protocol, www and trailing slashes from a URL.
     * If removeDomain is set, the domain is removed as well.
     */
    shortenUrl: function (url, removeDomain) {
        if (url == 'Others') {
            return url;
        }

        var urlBackup = url;
        url = url.replace(/http(s)?:\/\/(www\.)?/, '');

        if (urlBackup == url) {
            return url;
        }

        if (removeDomain) {
            url = url.replace(/[^\/]*/, '');
            if (url == '/') {
                url = urlBackup;
            }
        }

        url = url.replace(/\/$/, '');

        return url;
    },

    /**
     * Replaces a %s placeholder in the HTML.
     * The special feature is that it can be called multiple times, replacing the already
     * replaced placeholder again. It creates a span that can be assigned a class using the
     * spanClass parameter. The default class is 'Transitions_Metric'.
     */
    replacePlaceholderInHtml: function (container, value, spanClass) {
        var span = container.find('span');
        if (!span.length) {
            var html = container.html().replace(/%s/, '<span></span>');
            span = container.html(html).find('span');
            if (!spanClass) {
                spanClass = 'Transitions_Metric';
            }
            span.addClass(spanClass);
        }
        span.html(value);
    }

};
