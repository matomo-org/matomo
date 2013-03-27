{include file="CoreUpdater/templates/header.tpl"}
{assign var='helpMessage' value='CoreUpdater_HelpMessageContent'|translate:'<a target="_blank" href="?module=Proxy&action=redirect&url=http://piwik.org/faq/">':'</a>':'</li><li>'}

{if $coreError}
    <br/>
    <br/>
    <div class="error">
        <img src="themes/default/images/error_medium.png"/> {'CoreUpdater_CriticalErrorDuringTheUpgradeProcess'|translate}
        {foreach from=$errorMessages item=message}
            <pre>{$message}</pre>
            <br/>
        {/foreach}
    </div>
    <br/>
    <p>{'CoreUpdater_HelpMessageIntroductionWhenError'|translate}
    <ul>
        <li>{$helpMessage}</li>
    </ul>
    </p>
    <p>{'CoreUpdater_ErrorDIYHelp'|translate}
    <ul>
        <li>{'CoreUpdater_ErrorDIYHelp_1'|translate}</li>
        <li>{'CoreUpdater_ErrorDIYHelp_2'|translate}</li>
        <li>{'CoreUpdater_ErrorDIYHelp_3'|translate}</li>
        <li>{'CoreUpdater_ErrorDIYHelp_4'|translate}</li>
        <li>{'CoreUpdater_ErrorDIYHelp_5'|translate}</li>
    </ul>
    </p>
{else}

    {if count($warningMessages) > 0}
        <div class="warning">
            <p><img src="themes/default/images/warning_medium.png"/> {'CoreUpdater_WarningMessages'|translate}</p>
            {foreach from=$warningMessages item=message}
                <pre>{$message}</pre>
                <br/>
            {/foreach}
        </div>
    {/if}

    {if count($errorMessages) > 0}
        <div class="warning">
            <p><img src="themes/default/images/error_medium.png"/> {'CoreUpdater_ErrorDuringPluginsUpdates'|translate}</p>
            {foreach from=$errorMessages item=message}
                <pre>{$message}</pre>
                <br/>
            {/foreach}

            {if isset($deactivatedPlugins) && count($deactivatedPlugins) > 0}
                {assign var=listOfDeactivatedPlugins value=$deactivatedPlugins|@implode:', '}
                <p style="color:red"><img
                            src="themes/default/images/error_medium.png"/> {'CoreUpdater_WeAutomaticallyDeactivatedTheFollowingPlugins'|translate:$listOfDeactivatedPlugins}
                </p>
            {/if}
        </div>
    {/if}

    {if count($errorMessages) > 0 || count($warningMessages) > 0}
        <br/>
        <p>{'CoreUpdater_HelpMessageIntroductionWhenWarning'|translate}
        <ul>
            <li>{$helpMessage}</li>
        </ul>
        </p>
    {else}
        <p class="success">{'CoreUpdater_PiwikHasBeenSuccessfullyUpgraded'|translate}</p>
        <div id="donate-form-container">
            {include file="CoreHome/templates/donate.tpl"}
        </div>
    {/if}
    <form action="index.php">
        <input type="submit" class="submit" value="{'CoreUpdater_ContinueToPiwik'|translate}"/>
    </form>
{/if}

{include file="CoreUpdater/templates/footer.tpl"}
