$(document).ready(function () {
    $('input').iCheck({
        checkboxClass: 'form-checkbox',
        radioClass: 'form-radio',
        checkedClass: 'checked',
        hoverClass: 'form-hover',
    });

    $('body').on('ifClicked', 'input', function () {
        $(this).triggerHandler('click');
    }).on('ifChanged', 'input', function () {
        $(this).triggerHandler('change');
    });
});