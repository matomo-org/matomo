$(document).ready(function () {
    $('.ui-inline-help:contains(UTC time is)').hide();

    $('[notification-id=ControllerAdmin_HttpIsUsed]').hide();

    $.fx.off = true;

    // disable materialize animations (Materialize version > 1)
    if (typeof M !== 'undefined' && M.anime) {
        var oldAnime = M.anime;
        M.anime = function (params) {
            if (!params) {
                params = {};
            }
            params.duration = 0;
            return oldAnime(params);
        };
    } else if ($.Velocity) {
        $.Velocity.mock = true;
    }
});