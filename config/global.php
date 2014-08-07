<?php

use Interop\Container\ContainerInterface;
use Piwik\Db\Adapter;
use Piwik\Db\DbFactory;

return array(

    'Piwik\Db\Db' => DI\factory(function (ContainerInterface $c) {
        /** @var DbFactory $dbFactory */
        $dbFactory = $c->get('Piwik\Db\DbFactory');
        return $dbFactory->createDb();
    }),

);
