{* untrusted host warning *}
{if isset($isValidHost) && isset($invalidHostMessage) && !$isValidHost}
    <div class="ajaxSuccess" style='clear:both;width:800px'>
        <a style="float:right" href="http://piwik.org/faq/troubleshooting/#faq_171" target="_blank"><img src="themes/default/images/help_grey.png"/></a>
        <strong>{'General_Warning'|translate}:&nbsp;</strong>{$invalidHostMessage}
    </div>
{/if}

