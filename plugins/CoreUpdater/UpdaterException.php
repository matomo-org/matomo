<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater;

use Exception;

/**
 * Exception during the updating of Piwik to a new version.
 */
class UpdaterException extends Exception
{
    /**
     * @var string[]
     */
    private $updateLogMessages;

    public function __construct(Exception $exception, array $updateLogMessages)
    {
        parent::__construct($exception->getMessage(), 0, $exception);

        $this->updateLogMessages = $updateLogMessages;
    }

    /**
     * @return string[]
     */
    public function getUpdateLogMessages()
    {
        return $this->updateLogMessages;
    }
}
