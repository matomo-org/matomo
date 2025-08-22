<?php

declare(strict_types=1);

namespace Piwik\Policy;

use Piwik\Policy\UnifiedSettingsAccess\UnifiedSettingsAccess;

$unifiedSettingsAccess = new UnifiedSettingsAccess();
// setting name isnt right
$retentionPeriod = $unifiedSettingsAccess->getSetting('PrivacyManager.ReportRetentionPeriod');
