<script>

    function updateEvolutionGraphParameterVisibility() {ldelim}
        var evolutionGraphParameterInput = $('.report_evolution_graph');
        var nonApplicableDisplayFormats = ['1', '4'];
        $.inArray($('#display_format').find('option:selected').val(), nonApplicableDisplayFormats) != -1 ?
                evolutionGraphParameterInput.hide() : evolutionGraphParameterInput.show();
        {rdelim
    }

    $(function () {ldelim}

        resetReportParametersFunctions ['{$reportType}'] =
                function () {ldelim}

                    var reportParameters = {ldelim}
                        'displayFormat': '{$defaultDisplayFormat}',
                        'emailMe': {$defaultEmailMe},
                        'evolutionGraph': {$defaultEvolutionGraph},
                        'additionalEmails': null
                        {rdelim};

                    updateReportParametersFunctions['{$reportType}'](reportParameters);
                    {rdelim
                };

        updateReportParametersFunctions['{$reportType}'] =
                function (reportParameters) {ldelim}

                    if (reportParameters == null) return;

                    $('#display_format').find('option[value=' + reportParameters.displayFormat + ']').prop('selected', 'selected');
                    updateEvolutionGraphParameterVisibility();

                    if (reportParameters.emailMe === true)
                        $('#report_email_me').prop('checked', 'checked');
                    else
                        $('#report_email_me').removeProp('checked');

                    if (reportParameters.evolutionGraph === true)
                        $('#report_evolution_graph').prop('checked', 'checked');
                    else
                        $('#report_evolution_graph').removeProp('checked');

                    if (reportParameters.additionalEmails != null)
                        $('#report_additional_emails').text(reportParameters.additionalEmails.join('\n'));
                    else
                        $('#report_additional_emails').html('');
                    {rdelim
                };

        getReportParametersFunctions['{$reportType}'] =
                function () {ldelim}

                    var parameters = Object();

                    parameters.displayFormat = $('#display_format').find('option:selected').val();
                    parameters.emailMe = $('#report_email_me').prop('checked');
                    parameters.evolutionGraph = $('#report_evolution_graph').prop('checked');

                    var additionalEmails = $('#report_additional_emails').val();
                    parameters.additionalEmails =
                            additionalEmails != '' ? additionalEmails.split('\n') : [];

                    return parameters;
                    {rdelim
                };

        $('#display_format').change(updateEvolutionGraphParameterVisibility);

        {rdelim
    });
</script>

<tr class='{$reportType}'>
    <td style='width:240px;' class="first">{'PDFReports_SendReportTo'|translate}
    </td>
    <td>
        <input type="checkbox" id="report_email_me"/>
        <label for="report_email_me">{'PDFReports_SentToMe'|translate} (<i>{$currentUserEmail}</i>) </label>
        <br/><br/>
        {'PDFReports_AlsoSendReportToTheseEmails'|translate}<br/>
        <textarea cols="30" rows="3" id="report_additional_emails" class="inp"></textarea>
    </td>
</tr>
<tr class='{$reportType}'>
    <td class="first">
        {*PDFReports_AggregateReportsFormat should be named PDFReports_DisplayFormat*}
        {'PDFReports_AggregateReportsFormat'|translate}
    </td>
    <td>
        <select id="display_format">
            {foreach from=$displayFormats key=formatValue item=formatLabel}
                <option {if $formatValue==1}selected{/if} value="{$formatValue}">{$formatLabel}</option>
            {/foreach}
        </select>

        <div class='report_evolution_graph'>
            <br/>
            <input type="checkbox" id="report_evolution_graph"/>
            <label for="report_evolution_graph"><i>{'PDFReports_EvolutionGraph'|translate:5}</i></label>
        </div>
    </td>
</tr>
