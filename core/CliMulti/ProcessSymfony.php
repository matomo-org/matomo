<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\CliMulti;

use Piwik\Process;

/**
 * Wrapper for Symfony Process class
 */
class ProcessSymfony extends Process
{
    /**
     * @var string|null
     */
    private $commandId;

    public function getCommandId(): ?string
    {
        return $this->commandId;
    }

    public function setCommandId(string $commandId): void
    {
        $this->commandId = $commandId;
    }
}
