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

    // show refresh icon for duplicate page views in a row
    $("ol.visitorLog", elem).each(function () {
        var prevelement;
        var prevhtml;
        var counter = 0, duplicateCounter = 0;

        $(this).find("li:not(.pageviewActions):not(.actionsForPageExpander)").each(function () {
            var current = $(this).html();

            if (current == prevhtml) {
                duplicateCounter++;
                $(this).find('>div').prepend($("<span>"+(duplicateCounter+1)+"</span>").attr({'class': 'repeat icon-refresh', 'title': _pk_translate('Live_PageRefreshed')}));
                prevelement.addClass('duplicate').val('').attr('style', '');
            } else {
                duplicateCounter = 0;
                counter++;
            }

            $(this).css({ 'counter-reset': 'item ' + (counter - 1) }).val(counter - 1);

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

    // collapse adjacent content interactions
    var $actions = $("ol.visitorLog li", elem);
    $actions.each(function ($li, index) {
        if (!$li.is('.content')) {
            return;
        }

        var $collapsedContents = $li.prev('.cop');

        if (!$actions[index - 1]
            || !$actions[index - 2]
        ) {
            return;
        }

        // TODO
        var $previous = $li.prev('li');
        if (!$previous.length) {
            return;
        }

        if ($previous.is('.content')) {
            if (!$previous.is('.collapsed-contents')) {

                $collapsedContents = $('<piwik-content-actions-list/>');
                $collapsedContents.insertBefore($previous);
                piwikHelper.compileAngularComponents($collapsedContents);
                // TODO: create .collapsed-contents
                $previous = $collapsedContents;
            }

            $collapsedContents.element().scope().addContentItem($li); // TODO
        }
    });
/*
<li class="content collapsed-contents"
    title="{{ 'Live_ClickToSeeAllContents'|translate }}">
    <div>
        <span class="content-impressions"></span> content impressions <span class="conent-interactions"></span> interaction
    </div>
</li>
*/

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

    elem.find('.show-less-actions').click();
}

