$(function () {
    $('#toFade').fadeOut(4000, function () { $(this).show().css({visibility: 'hidden'}); });
    $('input:first').focus();
    $('#progressbar').progressbar({
        value: parseInt($('#progressbar').attr('data-progress'))
    });
    $('code').click(function () { $(this).select(); });
});