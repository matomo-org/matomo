<?php

return array(

    'trackingspam.iprangeproviders' => Piwik\DI::add(array(
        Piwik\DI::get('Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges\Aws'),
        Piwik\DI::get('Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges\Azure'),
        Piwik\DI::get('Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges\DigitalOcean'),
        Piwik\DI::get('Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges\Gcloud'),
        Piwik\DI::get('Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges\Oracle'),
    )),

    'Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges' => Piwik\DI::autowire()
        ->constructorParameter('providers', Piwik\DI::get('trackingspam.iprangeproviders')),
);
