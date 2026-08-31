<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders;

use Exception;
use Piwik\Piwik;
use Piwik\Plugin\ControllerAdmin;
use Piwik\Plugins\AIProviders\Model\Configuration;

class Controller extends ControllerAdmin
{
    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        parent::__construct();

        $this->configuration = $configuration;
    }

    public function index(): string
    {
        Piwik::checkUserHasSuperUserAccess();

        /**
         * In a managed environment the provider is
         * forced from configuration and there is nothing to configure, so the
         * settings page is intentionally unavailable (the menu entry is hidden
         * too). This guards against direct URL access.
         */
        if ($this->configuration->isManaged()) {
            throw new Exception('AI provider settings are managed and cannot be changed on this instance.');
        }

        return $this->renderTemplate('index');
    }
}
