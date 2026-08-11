<?php

return array(

    'trackingspam.iprangeproviders' => array(
        new \Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges\VariableRange(['10.10.0.0/21', '200.200.0.0/21']),
    ),
);
