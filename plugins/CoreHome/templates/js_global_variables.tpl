<script type="text/javascript">
var piwik = new Array();

piwik.token_auth = "{$token_auth}";
{if isset($idSite)}piwik.idSite = "{$idSite}";{/if}
{if isset($period)}piwik.period = "{$period}";{/if}
{if isset($date)}piwik.currentDateString = "{$date}";{/if}
{if isset($minDateYear)}piwik.minDateYear = {$minDateYear};{/if}
{if isset($minDateMonth)}piwik.minDateMonth = {$minDateMonth};{/if}
{if isset($minDateDay)}piwik.minDateDay = {$minDateDay};{/if}
</script>
