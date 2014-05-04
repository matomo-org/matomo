$(function () {
    $('#toFade').fadeOut(4000, function () { $(this).show().css({visibility: 'hidden'}); });
    $('input:first').focus();
    $('#progressbar').progressbar({
        value: parseInt($('#progressbar').attr('data-progress'))
    });
    $('code').click(function () { $(this).select(); });

    // Focus the first input field in the form
    $('form:not(.filter) :input:visible:enabled:first').focus();
});