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
        var link = tr.find('> td:first > a').attr('href');
        link = $('<textarea>').html(link).val(); // remove html entities
        return link;
    }

    if (window.DataTable_RowActions_Transitions) {
        DataTable_RowActions_Transitions.registerReport({
            isAvailableOnReport: function (dataTableParams) {
                return isPageUrlReport(dataTableParams);
            },
            isAvailableOnRow: function (dataTableParams, tr) {
                return isPageUrlReport(dataTableParams) && tr.find('> td:first span.label').parent().is('a')
            },
            trigger: function (tr, e, subTableLabel) {
                var link = getLinkForTransitionAndOverlayPopover(tr);
                this.openPopover('url:' + link);
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

});