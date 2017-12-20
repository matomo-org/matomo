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
        $(this).find("> li").each(function () {
            counter++;
            $(this).val(counter);
            var current = $(this).html();

            if (current == prevhtml) {
                duplicateCounter++;
                $(this).find('>div').prepend($("<span>"+(duplicateCounter+1)+"</span>").attr({'class': 'repeat icon-refresh', 'title': _pk_translate('Live_PageRefreshed')}));
                prevelement.addClass('duplicate');

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
}

