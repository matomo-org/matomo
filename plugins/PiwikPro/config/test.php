<?php

use Piwik\Tests\Framework\Mock\PiwikPro\Advertising;
use Piwik\Plugins\PiwikPro\tests\Framework\Mock\Promo;

return array(
    'Piwik\PiwikPro\Advertising' => function () {
        return new Advertising();
    },
    'Piwik\Plugins\PiwikPro\Promo' => function () {
        return new Promo();
    }
);
