<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Piwik\Option;

class LicenseKey
{
    public function get()
    {
        return Option::get('marketplace_license_key');
    }

    public function has()
    {
        $key = $this->get();

        return isset($key) && $key !== false && $key !== '';
    }

    /**
     * @param string|null|false $licenseKey `null` or `false` will delete an existing a license key
     */
    public function set($licenseKey)
    {
        if (!isset($licenseKey) || $licenseKey === false) {
            $this->delete();
        } else {
            Option::set('marketplace_license_key', (string) $licenseKey);
        }
    }

    private function delete()
    {
        Option::delete('marketplace_license_key');
    }

}
