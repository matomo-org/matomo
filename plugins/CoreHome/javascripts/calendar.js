/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function ($) {
    // min/max date for picker
    var piwikMinDate = new Date(piwik.minDateYear, piwik.minDateMonth - 1, piwik.minDateDay),
        piwikMaxDate = new Date(piwik.maxDateYear, piwik.maxDateMonth - 1, piwik.maxDateDay);

    piwik.getBaseDatePickerOptions = function (defaultDate) {
        return {
            showOtherMonths: false,
            dateFormat: 'yy-mm-dd',
            firstDay: 1,
            minDate: piwikMinDate,
            maxDate: piwikMaxDate,
            prevText: "",
            nextText: "",
            currentText: "",
            defaultDate: defaultDate,
            changeMonth: true,
            changeYear: true,
            stepMonths: 1,
            // jquery-ui-i18n 1.7.2 lacks some translations, so we use our own
            dayNamesMin: [
                _pk_translate('Intl_Day_Min_StandAlone_7'),
                _pk_translate('Intl_Day_Min_StandAlone_1'),
                _pk_translate('Intl_Day_Min_StandAlone_2'),
                _pk_translate('Intl_Day_Min_StandAlone_3'),
                _pk_translate('Intl_Day_Min_StandAlone_4'),
                _pk_translate('Intl_Day_Min_StandAlone_5'),
                _pk_translate('Intl_Day_Min_StandAlone_6')],
            dayNamesShort: [
                _pk_translate('Intl_Day_Short_StandAlone_7'), // start with sunday
                _pk_translate('Intl_Day_Short_StandAlone_1'),
                _pk_translate('Intl_Day_Short_StandAlone_2'),
                _pk_translate('Intl_Day_Short_StandAlone_3'),
                _pk_translate('Intl_Day_Short_StandAlone_4'),
                _pk_translate('Intl_Day_Short_StandAlone_5'),
                _pk_translate('Intl_Day_Short_StandAlone_6')],
            dayNames: [
                _pk_translate('Intl_Day_Long_StandAlone_7'), // start with sunday
                _pk_translate('Intl_Day_Long_StandAlone_1'),
                _pk_translate('Intl_Day_Long_StandAlone_2'),
                _pk_translate('Intl_Day_Long_StandAlone_3'),
                _pk_translate('Intl_Day_Long_StandAlone_4'),
                _pk_translate('Intl_Day_Long_StandAlone_5'),
                _pk_translate('Intl_Day_Long_StandAlone_6')],
            monthNamesShort: [
                _pk_translate('Intl_Month_Short_StandAlone_1'),
                _pk_translate('Intl_Month_Short_StandAlone_2'),
                _pk_translate('Intl_Month_Short_StandAlone_3'),
                _pk_translate('Intl_Month_Short_StandAlone_4'),
                _pk_translate('Intl_Month_Short_StandAlone_5'),
                _pk_translate('Intl_Month_Short_StandAlone_6'),
                _pk_translate('Intl_Month_Short_StandAlone_7'),
                _pk_translate('Intl_Month_Short_StandAlone_8'),
                _pk_translate('Intl_Month_Short_StandAlone_9'),
                _pk_translate('Intl_Month_Short_StandAlone_10'),
                _pk_translate('Intl_Month_Short_StandAlone_11'),
                _pk_translate('Intl_Month_Short_StandAlone_12')],
            monthNames: [
                _pk_translate('Intl_Month_Long_StandAlone_1'),
                _pk_translate('Intl_Month_Long_StandAlone_2'),
                _pk_translate('Intl_Month_Long_StandAlone_3'),
                _pk_translate('Intl_Month_Long_StandAlone_4'),
                _pk_translate('Intl_Month_Long_StandAlone_5'),
                _pk_translate('Intl_Month_Long_StandAlone_6'),
                _pk_translate('Intl_Month_Long_StandAlone_7'),
                _pk_translate('Intl_Month_Long_StandAlone_8'),
                _pk_translate('Intl_Month_Long_StandAlone_9'),
                _pk_translate('Intl_Month_Long_StandAlone_10'),
                _pk_translate('Intl_Month_Long_StandAlone_11'),
                _pk_translate('Intl_Month_Long_StandAlone_12')]
        };
    };

    piwikHelper.registerShortcut('d', _pk_translate('CoreHome_ShortcutCalendar'), function(event) {
        if (event.altKey) {
            return;
        }
        if (event.preventDefault) {
            event.preventDefault();
        } else {
            event.returnValue = false; // IE
        }
        $('#periodString .title').trigger('click').focus();
    });

}(jQuery));
