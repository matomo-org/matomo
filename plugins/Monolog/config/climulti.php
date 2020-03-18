<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Monolog\Logger;
use Piwik\Plugins\Monolog\Handler\EchoHandler;
use Psr\Container\ContainerInterface;

return [
    'log.handlers' => \DI\decorate(function ($previous, ContainerInterface $c) {
        if ($c->get('ini.log.enable_fingers_crossed_handler')) {
            $handler = new EchoHandler();

            $passthruLevel = $handler->getLevel();
            $handler->setLevel(Logger::DEBUG);

            $handler = new \Monolog\Handler\FingersCrossedHandler($handler, $activationStrategy = null, $bufferSize = 0,
                $bubble = true, false, $passthruLevel);

            $previous = array_merge([$handler], $previous ?: []);
        }
        return $previous;
    }),
];