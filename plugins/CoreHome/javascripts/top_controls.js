/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function initTopControls() {
    function getOverlap(element1, element2)
    {
        if (!element1 || !element1.getBoundingClientRect || !element2 || !element2.getBoundingClientRect) {
            return 0;
        }

        var rect1 = element1.getBoundingClientRect();
        var rect2 = element2.getBoundingClientRect();

        var doOverlap = !(rect1.right < rect2.left || rect1.left > rect2.right);

        if (doOverlap) {
            return rect1.left - rect2.right;
        }

        return 0;
    }

    var $topControlsContainer = $('.top_controls');

    var allRendered = true;

    if ($topControlsContainer.length) {
        $topControlsContainer.find('.piwikTopControl').each(function () {
            var $control = $(this);
            if ($control.css('display') == 'none') {
                return;
            }

            var width = $control.outerWidth(true);

            var isControlFullyRendered = width >= 30;
            if (!isControlFullyRendered) {
                allRendered = false;
            }
        });

        if (allRendered) {
            // we make top controls visible only after all selectors are rendered
            $('.top_controls').css('visibility', 'visible');
            $('.top_controls').css('opacity', '1');
        }

    }
}

//Keyboard controls for Top Controls Calendar through tab and enter. 
$( document ).ready(function() {
    $('.periodSelector').keydown(function(e){
        toggleCalendar(e);
    })

    blockPropegation();

    $('.periodSelector .form-radio').keydown(function(e){
        e.stopPropagation();
        if(e.which==13){
            selectPeriodRadioButton($(this));
        }
    })
});

//Keyboard controls for Top Controls Calendar through tab and enter. 
$( document ).ready(function() {
    $('.periodSelector').keydown(function(e){
        toggleCalendar(e);
    })

    blockPropegation();

    $('.periodSelector .form-radio').keydown(function(e){
        e.stopPropagation();
        if(e.which==13){
            selectPeriodRadioButton($(this));
        }
    })
});

function toggleCalendar(e){
    var calendarOpen = $('.periodSelector').hasClass('expanded');
    
    if(e.which==13){
        if(calendarOpen){
            $('.periodSelector').removeClass('expanded');
        }else{
            $('.periodSelector').addClass('expanded');
        }
    }
}

function selectPeriodRadioButton(button){
    $('.periodSelector .form-radio').removeClass('checked');
    button.addClass('checked');
    button.find('input').click();

    blockPropegation();
}

function blockPropegation(){
    $('.ui-datepicker-month, .ui-datepicker-year, .periodSelector td a').keydown(function(e){
        e.stopPropagation();
    })
}