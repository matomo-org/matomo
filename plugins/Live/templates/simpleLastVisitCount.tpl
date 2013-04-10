{literal}
<script type="text/javascript">
    $(document).ready(function () {
        var refreshWidget = function (element, refreshAfterXSecs) {
            // if the widget has been removed from the DOM, abort
            if ($(element).parent().length == 0) {
                return;
            }

            var lastMinutes = $(element).attr('data-last-minutes') || 3,
                    translations = JSON.parse($(element).attr('data-translations'));

            var ajaxRequest = new ajaxHelper();
            ajaxRequest.addParams({
                module: 'API',
                method: 'Live.getCounters',
                format: 'json',
                lastMinutes: lastMinutes
            }, 'get');
            ajaxRequest.setFormat('json');
            ajaxRequest.setCallback(function (data) {
                data = data[0];

                // set text and tooltip of visitors count metric
                var visitors = data['visitors'];
                if (visitors == 1) {
                    var visitorsCountMessage = translations['one_visitor'];
                }
                else {
                    var visitorsCountMessage = translations['visitors'].replace('%s', visitors);
                }
                $('.simple-realtime-visitor-counter', element)
                        .attr('title', visitorsCountMessage)
                        .find('div').text(visitors);

                // set text of individual metrics spans
                var metrics = $('.simple-realtime-metric', element);

                var visitsText = data['visits'] == 1
                        ? translations['one_visit'] : translations['visits'].replace('%s', data['visits']);
                $(metrics[0]).text(visitsText);

                var actionsText = data['actions'] == 1
                        ? translations['one_action'] : translations['actions'].replace('%s', data['actions']);
                $(metrics[1]).text(actionsText);

                var lastMinutesText = lastMinutes == 1
                        ? translations['one_minute'] : translations['minutes'].replace('%s', lastMinutes);
                $(metrics[2]).text(lastMinutesText);

                // schedule another request
                setTimeout(function () { refreshWidget(element, refreshAfterXSecs); }, refreshAfterXSecs * 1000);
            });
            ajaxRequest.send(true);
        };

        var initSimpleRealtimeVisitorWidget = function (refreshAfterXSecs) {
            $('.simple-realtime-visitor-widget').each(function () {
                var self = this;
                if ($(self).attr('data-inited')) {
                    return;
                }

                $(self).attr('data-inited', 1);

                setTimeout(function () { refreshWidget(self, refreshAfterXSecs); }, refreshAfterXSecs * 1000);
            });
        };

        initSimpleRealtimeVisitorWidget({/literal}{$refreshAfterXSecs}{literal});
    });
</script>
    <style>
        .simple-realtime-visitor-widget {
            text-align: center;
        }

        .simple-realtime-visitor-counter {
            background-color: #F1F0EB;

            -moz-border-radius: 10px;
            -webkit-border-radius: 10px;
            border-radius: 10px;
            display: inline-block;
            margin: 2em 0 1em 0;
        }

        .simple-realtime-visitor-counter > div {
            font-size: 4.0em;
            padding: .25em .5em .25em .5em;
            color: #444;
        }

        .simple-realtime-metric {
            font-style: italic;
            font-weight: bold;
            color: #333;
        }

        .simple-realtime-elaboration {
            margin: 1em 2em 1em 2em;
            color: #666;
            display: inline-block;
        }
    </style>
{/literal}
<div class='simple-realtime-visitor-widget' data-last-minutes="{$lastMinutes}" data-translations="{$translations|@json_encode|escape:'html'}">
    <div class='simple-realtime-visitor-counter' title="{if $visitors eq 1}{'Live_NbVisitor'|translate}{else}{'Live_NbVisitors'|translate:$visitors}{/if}">
        <div>{$visitors}</div>
    </div>
    <br/>

    <div class='simple-realtime-elaboration'>
        {capture assign="visitsMessage"}<span class="simple-realtime-metric"
                                              data-metric="visits">{if $visits eq 1}{'General_OneVisit'|translate}{else}{'General_NVisits'|translate:$visits}{/if}</span>{/capture}
        {capture assign="actionsMessage"}<span class="simple-realtime-metric"
                                               data-metric="actions">{if $actions eq 1}{'General_OneAction'|translate}{else}{'VisitsSummary_NbActionsDescription'|translate:$actions}{/if}</span>{/capture}
        {capture assign="minutesMessage"}<span class="simple-realtime-metric"
                                               data-metric="minutes">{if $lastMinutes eq 1}{'General_OneMinute'|translate}{else}{'General_NMinutes'|translate:$lastMinutes}{/if}</span>{/capture}

        {'Live_SimpleRealTimeWidget_Message'|translate:$visitsMessage:$actionsMessage:$minutesMessage}
    </div>
</div>
