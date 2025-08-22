<?php

declare(strict_types=1);

namespace Piwik\Policy\Compliance;

interface PolicySettingValue {
    
    public function compare(?PolicySettingValue $setting);
}
