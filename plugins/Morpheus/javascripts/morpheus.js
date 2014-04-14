$(document).ready(function () {
    // do not apply on the Login page
    if($('#loginPage').length) {
        return;
    }

    function initICheck()
    {
        $('input').iCheck({
            checkboxClass: 'form-checkbox',
            radioClass: 'form-radio',
            checkedClass: 'checked',
            hoverClass: 'form-hover'
        });
    }

    initICheck();
    $(document).bind('ScheduledReport.edit', initICheck);

    $('body').on('ifClicked', 'input', function () {
        $(this).trigger('click');
    }).on('ifChanged', 'input', function () {
        $(this).trigger('change');
    });
});