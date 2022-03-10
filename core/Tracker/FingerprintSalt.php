<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker;

use Piwik\Common;
use Piwik\Date;
use Piwik\Option;

class FingerprintSalt
{
    const OPTION_PREFIX = 'fingerprint_salt_';
    const DELETE_FINGERPRINT_OLDER_THAN_SECONDS = 432000; // 5 days in seconds

    public function generateSalt()
    {
        return Common::getRandomString(32);
    }

    public function deleteOldSalts()
    {
        // we want to make sure to delete salts that were created more than three days ago as they are likely not in
        // use anymore. We should delete them to ensure the fingerprint is truly random for each day because if we used
        // eg the regular salt then it would technically still be possible to try and regenerate the fingerprint based
        // on certain information.
        // Typically, only the salts for today and yesterday are used. However, if someone was to import historical data
        // for the same day and this takes more than five days, then it could technically happen that we delete a
        // fingerprint that is still in use now and as such after deletion a few visitors would have a new configId
        // within one visit and such a new visit would be created. That should be very much edge case though.
        $deleteSaltsCreatedBefore = Date::getNowTimestamp() - self::DELETE_FINGERPRINT_OLDER_THAN_SECONDS;
        $options = Option::getLike(self::OPTION_PREFIX . '%');
        $deleted = array();
        foreach ($options as $name => $value) {
            $value = $this->decode($value);
            if (empty($value['time']) || $value['time'] < $deleteSaltsCreatedBefore) {
                Option::delete($name);
                $deleted[] = $name;
            }
        }

        return $deleted;
    }

    public function getDateString(Date $date, $timezone)
    {
        $dateString = Date::factory($date->getTimestampUTC(), $timezone)->toString();
        return $dateString;
    }

    private function encode($value)
    {
        return json_encode($value);
    }

    private function decode($value)
    {
        return @json_decode($value, true);
    }

    public function getSalt($dateString, $idSite)
    {
        $fingerprintSaltKey = self::OPTION_PREFIX . (int) $idSite . '_' . $dateString;
        $salt = Option::get($fingerprintSaltKey);
        if (!empty($salt)) {
            $salt = $this->decode($salt);
        }
        if (empty($salt['value'])) {
            $salt = array(
                'value' => $this->generateSalt(),
                'time' => Date::getNowTimestamp()
            );
            Option::set($fingerprintSaltKey, $this->encode($salt));
        }
        return $salt['value'];
    }
}
