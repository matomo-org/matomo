<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Handler;

use Monolog\Handler\StreamHandler;
use Piwik\Exception\MissingFilePermissionException;
use Piwik\Filechecks;

/**
 * Writes log to file.
 *
 * Extends StreamHandler to be able to have a custom exception message.
 */
class FileHandler extends StreamHandler
{
    protected function write(array $record)
    {
        try {
            parent::write($record);
        } catch (\UnexpectedValueException $e) {
            $ex = new MissingFilePermissionException(
                Filechecks::getErrorMessageMissingPermissions($this->url)
            );
            $ex->setIsHtmlMessage();
            throw $ex;
        }
    }
}
