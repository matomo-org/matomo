<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Intl\Data\Provider;

/**
 * Provides top level domains.
 */
class TopLevelDomainProvider
{
    /**
     * @var array
     */
    private $tldList;

    /**
     * Returns the list of all valid top level domains
     *
     * @return array
     *
     * @api
     */
    public function getTopLevelDomainList()
    {
        if ($this->tldList === null) {
            $this->tldList = require __DIR__ . '/../Resources/tlds.php';
        }

        return $this->tldList;
    }
}
