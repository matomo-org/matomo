<p>{'PrivacyManager_CurrentDBSize'|translate}: {$dbStats.currentSize}</p>
{if isset($dbStats.sizeAfterPurge)}
<p>{'PrivacyManager_EstimatedDBSizeAfterPurge'|translate}: <b>{$dbStats.sizeAfterPurge}</b></p>
{/if}
{if isset($dbStats.spaceSaved)}
<p>{'PrivacyManager_EstimatedSpaceSaved'|translate}: {$dbStats.spaceSaved}</p>
{/if}
