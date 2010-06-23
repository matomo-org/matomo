{assign var='helpMessage' value='CoreUpdater_HelpMessageContent'|translate:'[':']':"\n\n* "|unescape}
{textformat}
*** {'CoreUpdater_UpdateTitle'|translate|unescape} ***

{if $coreError}
	[X] {'CoreUpdater_CriticalErrorDuringTheUpgradeProcess'|translate|unescape}

	{foreach from=$errorMessages item=message}
		* {$message}

	{/foreach}

	{'CoreUpdater_HelpMessageIntroductionWhenError'|translate|unescape}

	* {$helpMessage}

{else}
	{if $coreToUpdate || count($pluginNamesToUpdate) > 0}
		{'CoreUpdater_DatabaseUpgradeRequired'|translate|unescape}

		{'CoreUpdater_YourDatabaseIsOutOfDate'|translate|unescape}

		{if $coreToUpdate}
			{'CoreUpdater_PiwikWillBeUpgradedFromVersionXToVersionY'|translate:$current_piwik_version:$new_piwik_version|unescape}

		{/if}
		{if count($pluginNamesToUpdate) > 0}
			{assign var=listOfPlugins value=$pluginNamesToUpdate|@implode:', '}
			{'CoreUpdater_TheFollowingPluginsWillBeUpgradedX'|translate:$listOfPlugins|unescape}

		{/if}
		{'CoreUpdater_TheUpgradeProcessMayTakeAWhilePleaseBePatient'|translate|unescape}

	{/if}
{/if}
{/textformat}
