/**
 * Matomo - free/libre analytics platform
 *
 * Diff generator
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

resemble.outputSettings({
    errorColor: {
        red: 255,
        green: 0,
        blue: 0,
        alpha: 125
    },
    errorType: 'movement',
    transparency: 0.3,
    largeImageThreshold: 20000
});

function compareImages(expected, expectedGithub, processed)
{
    var resembleControl = resemble(processed).compareTo(expected).onComplete(function(data){

        var info = 'Mismatch percentage: ' + data.misMatchPercentage + '%';

        if (data.dimensionDifference && !data.isSameDimensions) {
            info += ' Dimension difference width: ' + data.dimensionDifference.width + ' height: ' + data.dimensionDifference.height;
        }

        $('.info').text(info);
        $('.diff').attr('src', data.getImageDataUrl());
    });

    $('.processed').attr('src', encodeURI(processed));
    $('.expected').attr('src', encodeURI(expected));
    $('.expectedGithub').attr('src', 'https://raw.githubusercontent.com/piwik/piwik-ui-tests/master/' + encodeURI(expectedGithub));

    return resembleControl;
}

function getUrlQueryParam(sParam) {
    var query     = window.location.search.substring(1);
    var variables = query.split('&');

    for (var index = 0; index < variables.length; index++) {

        var paramName = variables[index].split('=');
        if (paramName[0] == sParam) {
            return paramName[1];
        }
    }
}

$(function () {
    var processed = getUrlQueryParam('processed');
    var expected  = getUrlQueryParam('expected');
    var github    = getUrlQueryParam('github');
    var resembleControl = compareImages(expected, github, processed);
    resembleControl.ignoreNothing();

    $('#toggleAliasing').click(function () {
        resembleControl.ignoreAntialiasing();
    });

    $('#original').attr('src', expected);
    $('#modified').attr('src', processed);
});