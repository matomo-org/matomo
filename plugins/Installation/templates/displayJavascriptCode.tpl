{if isset($displayfirstWebsiteSetupSuccess)}
    <span id="toFade" class="success">
	{'Installation_SetupWebsiteSetupSuccess'|translate:$displaySiteName}
        <img src="themes/default/images/success_medium.png"/>
</span>
{/if}

{$trackingHelp}
<br/><br/>
<h2>{'Installation_LargePiwikInstances'|translate}</h2>
{'Installation_JsTagArchivingHelp1'|translate:'<a target="_blank" href="http://piwik.org/docs/setup-auto-archiving/">':'</a>'} {'General_ReadThisToLearnMore'|translate:'<a target="_blank" href="http://piwik.org/docs/optimize/">':'</a>'}

{literal}
    <style type="text/css">
        code {
            font-size: 80%;
        }
    </style>
    <script>
        $(document).ready(function () {
            $('code').click(function () { $(this).select(); });
        });
    </script>
{/literal}
