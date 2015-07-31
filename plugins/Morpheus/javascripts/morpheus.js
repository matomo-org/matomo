$(document).ready(function () {
    // do not apply on the Login page
    if($('#loginPage').length) {
        return;
    }

    function initICheck()
    {
        $('input').filter(function () {
            return !$(this).parent().is('.form-radio')
                && !$(this).hasClass('no-icheck');
        }).iCheck({
            checkboxClass: 'form-checkbox',
            radioClass: 'form-radio',
            checkedClass: 'checked',
            hoverClass: 'form-hover'
        });
    }

    initICheck();
    $(document).bind('ScheduledReport.edit', initICheck);
    $(document).bind('Goals.edit', initICheck);
    $(broadcast).bind('locationChangeSuccess', initICheck);
    $(broadcast).bind('updateICheck', initICheck);

    $('body').on('ifClicked', 'input', function () {
        $(this).trigger('click');
    }).on('ifChanged', 'input', function () {
        if(this.type != 'radio' || this.checked) {
            $(this).trigger('change');
        }
    });
});
