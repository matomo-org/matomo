$(function () {

    function isActionsModule(params)
    {
        return params.module == 'Actions';
    }

    function isPageUrlReport(params) {
        var action = params.action;

        return isActionsModule(params) &&
            (action == 'getPageUrls' || action == 'getEntryPageUrls' || action == 'getExitPageUrls' || action == 'getPageUrlsFollowingSiteSearch');
    };

    function isPageTitleReport(params) {
        var action = params.action;

        return isActionsModule(params) && (action == 'getPageTitles' || action == 'getPageTitlesFollowingSiteSearch');
    };

    function getLinkForTransitionAndOverlayPopover(tr)
    {
        tr = getRealRowIfComparisonRow(tr);

        var link = tr.find('> td:first > a').attr('href');
        // replace all &, that are not part of a named character reference with a tailing semicolon, with a &amp;
        // otherwise named character references without a tailing , (like &reg) would be replaced
        link = link.replace(/&([a-z]+[^a-z;])/, '&amp;$1');
        link = $('<textarea>').html(link).val(); // remove html entities
        return link;
    }

    if (window.DataTable_RowActions_Transitions) {
        DataTable_RowActions_Transitions.registerReport({
            isAvailableOnReport: function (dataTableParams) {
                return isPageUrlReport(dataTableParams);
            },
            isAvailableOnRow: function (dataTableParams, tr) {
                tr = getRealRowIfComparisonRow(tr);
                return isPageUrlReport(dataTableParams) && tr.find('> td:first span.label').parent().is('a')
            },
            trigger: function (tr, e, subTableLabel, originalRow) {
                var overrideParams = $.extend({}, $(originalRow || tr).data('param-override'));
                if (typeof overrideParams !== 'object') {
                    overrideParams = {};
                }

                tr = getRealRowIfComparisonRow(tr);

                var link = getLinkForTransitionAndOverlayPopover(tr);
                var popoverUrl = 'url:' + link;

                Object.keys(overrideParams).forEach(function (paramName) {
                    if (!overrideParams[paramName]) {
                        return;
                    }

                    popoverUrl += ':' + encodeURIComponent(paramName) + ':' + encodeURIComponent(overrideParams[paramName]);
                });

                this.openPopover(popoverUrl);
            }
        });

        DataTable_RowActions_Transitions.registerReport({
            isAvailableOnReport: function (dataTableParams) {
                return isPageTitleReport(dataTableParams);
            },
            isAvailableOnRow: function (dataTableParams, tr) {
                return isPageTitleReport(dataTableParams);
            },
            trigger: function (tr, e, subTableLabel) {
                DataTable_RowAction.prototype.trigger.apply(this, [tr, e, subTableLabel]);
            }
        });
    }

    if (window.DataTable_RowActions_Overlay) {
        DataTable_RowActions_Overlay.registerReport({
            isAvailableOnReport: function (dataTableParams) {
                return isPageUrlReport(dataTableParams);
            },
            onClick: function (actionA, tr, e) {
                return {
                    link: getLinkForTransitionAndOverlayPopover(tr),
                    segment: null
                }
            }
        });
    }

    function getRealRowIfComparisonRow(tr) {
        if (tr.is('.comparisonRow')) {
            var prevUntil = tr.prevUntil('.parentComparisonRow').prev();
            return prevUntil.length ? prevUntil : tr.prev();
        }
        return tr;
    }
});