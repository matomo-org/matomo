<?php

use Interop\Container\ContainerInterface;

return array(

    'path.root' => PIWIK_USER_PATH,

    'path.tmp' => DI\factory(function (ContainerInterface $c) {
        $root = $c->get('path.root');

        // TODO remove that special case and instead have plugins override 'path.tmp' to add the instance id
        if ($c->has('old_config.General.instance_id')) {
            $instanceId = $c->get('old_config.General.instance_id');
            $instanceId = $instanceId ? '/' . $instanceId : '';
        } else {
            $instanceId = '';
        }

        return $root . '/tmp' . $instanceId;
    }),


    // Log
    'Piwik\Log' => DI\factory(array('Piwik\Log\LoggerFactory', 'createLogger')),
    'log.format' => DI\factory(function (ContainerInterface $c) {
        if ($c->has('old_config.log.string_message_format')) {
            return $c->get('old_config.log.string_message_format');
        }
        return '%level% %tag%[%datetime%] %message%';
    }),
    'log.processors' => array(
        DI\link('Piwik\Log\Processor\SprintfProcessor'),
    ),

);
