{textformat}
    {assign var='helpMessage' value='CoreUpdater_HelpMessageContent'|translate:'[':']':"\n\n* "|unescape}

    {if $coreError}
        [X] {'CoreUpdater_CriticalErrorDuringTheUpgradeProcess'|translate|unescape}

        {foreach from=$errorMessages item=message}
            * {$message}

        {/foreach}

        {'CoreUpdater_HelpMessageIntroductionWhenError'|translate|unescape}

        * {$helpMessage}


        {'CoreUpdater_ErrorDIYHelp'|translate}

        * {'CoreUpdater_ErrorDIYHelp_1'|translate}

        * {'CoreUpdater_ErrorDIYHelp_2'|translate}

        * {'CoreUpdater_ErrorDIYHelp_3'|translate}

        * {'CoreUpdater_ErrorDIYHelp_4'|translate}

        * {'CoreUpdater_ErrorDIYHelp_5'|translate}

    {else}
        {if count($warningMessages) > 0}
            [!] {'CoreUpdater_WarningMessages'|translate|unescape}

            {foreach from=$warningMessages item=message}
                * {$message}

            {/foreach}
        {/if}

        {if count($errorMessages) > 0}
            [X] {'CoreUpdater_ErrorDuringPluginsUpdates'|translate|unescape}

            {foreach from=$errorMessages item=message}
                * {$message}

            {/foreach}

            {if isset($deactivatedPlugins) && count($deactivatedPlugins) > 0}
                {assign var=listOfDeactivatedPlugins value=$deactivatedPlugins|@implode:', '}
                [!] {'CoreUpdater_WeAutomaticallyDeactivatedTheFollowingPlugins'|translate:$listOfDeactivatedPlugins|unescape}

            {/if}
        {/if}
        {if count($errorMessages) > 0 || count($warningMessages) > 0}
            {'CoreUpdater_HelpMessageIntroductionWhenWarning'|translate|unescape}

            * {$helpMessage}
        {else}
            {'CoreUpdater_PiwikHasBeenSuccessfullyUpgraded'|translate|unescape}

        {/if}
    {/if}
{/textformat}


