<?php

use Piwik\Tests\Framework\Mock\ProfessionalServices\Advertising;
use Piwik\Plugins\ProfessionalServices\tests\Framework\Mock\Promo;

return array(
    'Piwik\ProfessionalServices\Advertising' => function () {
        return new Advertising();
    },
    'Piwik\Plugins\ProfessionalServices\Promo' => function () {
        return new Promo();
    }
);
