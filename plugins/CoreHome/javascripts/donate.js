/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function ($) {

    $(document).ready(function () {

        var donateAmounts = [0, 30, 60, 90, 120];

        // returns the space between each donation amount in the donation slider
        var getTickWidth = function (slider) {
            var effectiveSliderWidth = $('.slider-range', slider).width() - $('.slider-position', slider).width();
            return effectiveSliderWidth / (donateAmounts.length - 1);
        };

        // returns the position index on a slider based on a x coordinate value
        var getPositionFromPageCoord = function (slider, pageX) {
            return Math.round((pageX - $('.slider-range', slider).offset().left) / getTickWidth(slider));
        };

        // set's the correct amount text & smiley face image based on the position of the slider
        var setSmileyFaceAndAmount = function (slider, pos) {
            // set text yearly amount
            $('.slider-donate-amount', slider).text('$' + donateAmounts[pos] + '/' + _pk_translate('General_YearShort'));

            // set the right smiley face
            $('.slider-smiley-face').attr('src', 'plugins/Morpheus/images/smileyprog_' + pos + '.png');

            // set the hidden option input for paypal
            var option = Math.max(1, pos);
            $('.piwik-donate-call input[name=os0]').val("Option " + option);
        };

        // move's a slider's position to a specific spot
        var moveSliderPosition = function (slider, to) {
            // make sure 'to' is valid
            if (to < 0) {
                to = 0;
            }
            else if (to >= donateAmounts.length) {
                to = donateAmounts.length - 1;
            }

            // set the slider position
            var left = to * getTickWidth(slider);
            if (left == 0) {
                left = -1; // at position 0 we move one pixel left to cover up some of slider bar
            }

            $('.slider-position', slider).css({
                left: left + 'px'
            });

            // reset the smiley face & amount based on the new position
            setSmileyFaceAndAmount(slider, to);
        };

        // when a slider is clicked, set the amount & smiley face appropriately
        $('body').on('click', '.piwik-donate-slider>.slider-range', function (e) {
            var slider = $(this).parent(),
                currentPageX = $('.slider-position', this).offset().left,
                currentPos = getPositionFromPageCoord(slider, currentPageX),
                pos = getPositionFromPageCoord(slider, e.pageX);

            // if the closest position is the current one, use the other position since
            // the user obviously wants to move the slider.
            if (currentPos == pos) {
                // if click is to right, go forward one, else backwards one
                if (e.pageX > currentPageX) {
                    ++pos;
                }
                else {
                    --pos;
                }
            }

            moveSliderPosition(slider, pos);
        });

        // when the smiley icon is clicked, move the position up one to demonstrate how to use the slider
        $('body').on('click', '.piwik-donate-slider .slider-smiley-face,.piwik-donate-slider .slider-donate-amount',
            function (e) {
                var slider = $(this).closest('.piwik-donate-slider'),
                    currentPageX = $('.slider-position', slider).offset().left,
                    currentPos = getPositionFromPageCoord(slider, currentPageX);

                moveSliderPosition(slider, currentPos + 1);
            }
        );

        // stores the current slider being dragged
        var draggingSlider = false;

        // start dragging on mousedown for a slider's position bar
        $('body').on('mousedown', '.piwik-donate-slider .slider-position', function () {
            draggingSlider = $(this).parent().parent();
        });

        // move the slider position if currently dragging when the mouse moves anywhere over the entire page
        $('body').on('mousemove', function (e) {
            if (draggingSlider) {
                var slider = draggingSlider.find('.slider-range'),
                    sliderPos = slider.find('.slider-position'),
                    left = e.pageX - slider.offset().left;

                // only move slider if the mouse x-coord is still on the slider (w/ some padding for borders)
                if (left <= slider.width() - sliderPos.width() + 2
                    && left >= -2) {
                    sliderPos.css({left: left + 'px'});

                    var closestPos = Math.round(left / getTickWidth(draggingSlider));
                    setSmileyFaceAndAmount(draggingSlider, closestPos);
                }
            }
        });

        // stop dragging and normalize a slider's position on mouseup over the entire page
        $('body').on('mouseup', function () {
            if (draggingSlider) {
                var sliderPos = $('.slider-position', draggingSlider),
                    slider = sliderPos.parent();

                if (sliderPos.length) {
                    // move the slider to the nearest donation amount position
                    var pos = getPositionFromPageCoord(draggingSlider, sliderPos.offset().left);
                    moveSliderPosition(draggingSlider, pos);
                }

                draggingSlider = false; // stop dragging
            }
        });

        // event for programatically changing the position
        $('body').on('piwik:changePosition', '.piwik-donate-slider', function (e, data) {
            moveSliderPosition(this, data.position);
        });
    });

}(jQuery));
