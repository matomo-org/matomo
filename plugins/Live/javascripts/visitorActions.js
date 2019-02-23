/**
 * Piwik - free/libre analytics platform
 *
 * Actions list in Visitor Log and Profile
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
    $("ol.visitorLog", elem).each(function () {
        var prevelement;
        var prevhtml;
        var duplicateCounter = 0;

        $(this).find("li:not(.pageviewActions):not(.actionsForPageExpander)").each(function () {
            var current = $(this).html();

            if (current == prevhtml) {
                duplicateCounter++;
                $(this).find('>div').prepend($("<span>"+(duplicateCounter+1)+"</span>").attr({'class': 'repeat icon-refresh', 'title': _pk_translate('Live_PageRefreshed')}));
                prevelement.addClass('duplicate').val('').attr('style', '');
            } else {
                duplicateCounter = 0;
            }

            prevhtml = current;
            prevelement = $(this);

            var $this = $(this);
            var tooltipIsOpened = false;

            $('a', $this).on('focus', function () {
                // see https://github.com/piwik/piwik/issues/4099
                if (tooltipIsOpened) {
                    $this.tooltip('close');
                }
            });

        });
    });

    // hide expanders if content collapsing removed enough items
    $("ol.actionList", elem).each(function () {
        var actionsToDisplayCollapsed = +$(this).closest('ol.visitorLog').attr('data-page-view-actions-to-display-collapsed');

        var $items = $(this).find("li:not(.pageviewActions):not(.actionsForPageExpander):not(.duplicate)");
        if ($items.length <= actionsToDisplayCollapsed) {
            $(this).children('.actionsForPageExpander').hide();
        }
    });

    $("ol.visitorLog > li:not(.duplicate)", elem).each(function(){
        if (!$('.icon-refresh', $(this)).length) {
            return;
        }
        $(this).attr('origtitle', $(this).attr('title'));
        $(this).attr('title', _pk_translate('Live_ClickToViewAllActions'));
        $(this).click(function(e){
            e.preventDefault();
            $(this).prevUntil('li:not(.duplicate)').removeClass('duplicate').find('.icon-refresh').hide();
            var elem = $(this);
            window.setTimeout(function() {
                elem.attr('title', elem.attr('origtitle'));
                elem.attr('origtitle', null);
            }, 150);
            $(this).off('click').find('.icon-refresh').hide();
            return false;
        });
    });

    // event handler for content expander/collapser
    elem.on('click', '.collapsed-contents', function () {
        $(this).nextUntil(':not(.content)').toggleClass('duplicate');
    });

    // event handler for action expander/collapser
    elem.on('click', '.show-less-actions,.show-more-actions', function (e) {
        e.preventDefault();

        var actionsToDisplayCollapsed = +$(e.target).closest('ol.visitorLog').attr('data-page-view-actions-to-display-collapsed');

        var $actions = $(e.target).closest('.actionList').find('li:not(.duplicate):not(.actionsForPageExpander)');
        $actions.each(function () {
            if ($actions.index(this) >= actionsToDisplayCollapsed) {
                $(this).toggle({
                    duration: 250
                });
            }
         });

        $(e.target)
            .parent().find('.show-less-actions,.show-more-actions').toggle()
            .closest('li')
            .toggleClass('expanded collapsed');
    });

    elem.find('.show-less-actions:visible').click();
}

