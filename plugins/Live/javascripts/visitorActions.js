/**
 * Matomo - free/libre analytics platform
 *
 * Actions list in Visitor Log and Profile
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function initializeVisitorActions(elem) {

    var tooltipIsOpened = false;

    $('a', elem).on('focus', function () {
        // see https://github.com/piwik/piwik/issues/4099
        if (tooltipIsOpened) {
            elem.tooltip('close');
        }
    });

    elem.tooltip({
        items: '[title],.visitorLogIconWithDetails',
        track: true,
        show: false,
        hide: false,
        content: function() {
            if ($(this).hasClass('visitorLogIconWithDetails')) {
                return $('<ul>').html($('ul', $(this)).html());
            }
            var title = $(this).attr('title');
            return $('<a>').text( title ).html().replace(/\n/g, '<br />');
        },
        tooltipClass: 'small',
        open: function() { tooltipIsOpened = true; },
        close: function() { tooltipIsOpened = false; }
    });

    // collapse adjacent content interactions
    $("ol.visitorLog", elem).each(function () {
        var $actions = $(this).find("li");
        $actions.each(function (index) {
            var $li = $(this);
            if (!$li.is('.content')) {
                return;
            }

            if (!$actions[index - 1]
                || !$($actions[index - 1]).is('.content')
                || !$actions[index - 2]
                || !$($actions[index - 2]).is('.content')
            ) {
                return;
            }

            var $collapsedContents = $li;
            while ($collapsedContents.prev().is('.content')) {
                $collapsedContents = $collapsedContents.prev();
            }

            if (!$collapsedContents.is('.collapsed-contents')) {
                $collapsedContents = makeCollapsedContents();
                $collapsedContents.insertBefore($($actions[index - 2]));

                addContentItem($collapsedContents, $($actions[index - 2]));
                addContentItem($collapsedContents, $($actions[index - 1]));
            }

            addContentItem($collapsedContents, $li);

            function makeCollapsedContents() {
                var $li = $('<li/>')
                    .attr('class', 'content collapsed-contents')
                    .attr('title', _pk_translate('Live_ClickToSeeAllContents'));

                $('<div>')
                    .html('<img src="plugins/Morpheus/images/contentimpression.svg" class="action-list-action-icon"/>' +
                        ' <span class="content-impressions">0</span> content impressions <span class="content-interactions">0</span> interactions')
                    .appendTo($li);

                return $li;
            }

            function addContentItem($collapsedContents, $otherLi) {
                if ($otherLi.find('.content-interaction').length) {
                    var $interactions = $collapsedContents.find('.content-interactions');
                    $interactions.text(parseInt($interactions.text()) + 1);
                } else {
                    var $impressions = $collapsedContents.find('.content-impressions');
                    $impressions.text(parseInt($impressions.text()) + 1);
                }

                $otherLi.addClass('duplicate').addClass('collapsed-content-item').val('').attr('style', '');
            }
        });
    });

    // show refresh icon for duplicate page views in a row
    $("li.pageviewActions", elem).each(function () {
        var $divider = $(this).find('.refresh-divider');
        $divider.prevUntil().addClass('duplicate');
        $divider.remove();

        var viewCount = +$(this).attr('data-view-count');
        if (viewCount <= 1
            || isNaN(viewCount)
        ) {
            return;
        }

        var $pageviewAction = $(this).prev();
        $pageviewAction.find('>div').prepend($("<span>"+viewCount+"</span>").attr({'class': 'repeat icon-refresh', 'title': _pk_translate('Live_PageRefreshed')}));

        var actionsCount = +$(this).attr('data-actions-on-page');
        if (actionsCount === 0) {
            $pageviewAction.addClass('noPageviewActions');
        }

        $('a', $(this)).on('focus', function () {
            // see https://github.com/piwik/piwik/issues/4099
            if (tooltipIsOpened) {
                $(this).tooltip('close');
            }
        });

        var $this = $(this);
        $pageviewAction.attr('origtitle', $pageviewAction.attr('title'));
        $pageviewAction.attr('title', _pk_translate('Live_ClickToViewAllActions'));
        $pageviewAction.click(function (e) {
            e.preventDefault();
            e.stopPropagation();

            $pageviewAction.addClass('refreshesExpanded');
            $this.children('.actionList').children().first().removeClass('duplicate').nextUntil('li:not(.duplicate)').removeClass('duplicate');

            window.setTimeout(function() {
                $pageviewAction.attr('title', $pageviewAction.attr('origtitle'));
                $pageviewAction.attr('origtitle', null);
            }, 150);

            $pageviewAction.off('click').find('.icon-refresh').hide();
            $pageviewAction.triggerHandler('mouseleave'); // close tooltip so the title will replace
        });
    });

    // must be here before the logic to toggle the expanders so if plugins collapse items, the actions will
    // be correctly counted
    window.CoreHome.Matomo.postEvent('Live.initializeVisitorActions', elem);

    // hide expanders if content collapsing removed enough items
    $("ol.actionList", elem).each(function () {
        var actionsToDisplayCollapsed = +piwik.visitorLogActionsToDisplayCollapsed;

        var $items = $(this).find("li:not(.pageviewActions):not(.actionsForPageExpander):not(.duplicate)");
        var hasMoreItemsThanLimit = $items.length > actionsToDisplayCollapsed;

        $(this).children('.actionsForPageExpander')
            .toggle(hasMoreItemsThanLimit)
            .find('.show-actions-count').text($items.length - actionsToDisplayCollapsed);

        // add last-action class to the last action in each list
        setLastActionClass($(this));
    });

    // event handler for content expander/collapser
    elem.on('click', '.collapsed-contents', function () {
        $(this).nextUntil(':not(.content)').toggleClass('duplicate');
        setLastActionClass($(this).closest('ol.actionList'));
    });

    // event handler for action expander/collapser
    elem.on('click', '.show-less-actions,.show-more-actions', function (e) {
        e.preventDefault();

        var actionsToDisplayCollapsed = +piwik.visitorLogActionsToDisplayCollapsed;

        var $actions = $(e.target).closest('.actionList').find('li:not(.duplicate):not(.actionsForPageExpander)');
        $actions.each(function () {
            if ($actions.index(this) >= actionsToDisplayCollapsed) {
                $(this).toggle({
                    duration: 250
                });
            }
         });

        $(e.target)
            .parent().find('.show-less-actions,.show-more-actions').toggle();
        $(e.target)
            .closest('li')
            .toggleClass('expanded collapsed');
    });

    elem.find('.show-less-actions:visible').click();

    function setLastActionClass($list) {
        $list.children(':not(.actionsForPageExpander):not(.duplicate)').removeClass('last-action').last().addClass('last-action');
    }
}
