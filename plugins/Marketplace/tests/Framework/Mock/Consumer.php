<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Framework\Mock;

use Piwik\Plugins\Marketplace\Consumer as ActualConsumer;
use Piwik\Plugins\Marketplace\Input\PurchaseType;

class Consumer
{
    public static function build($service)
    {
        $client = Client::build($service);
        return new ActualConsumer($client);
    }

    public static function buildNoLicense()
    {
        $service = new Service();
        $service->setOnDownloadCallback(function ($action, $params) use ($service) {
            if ($action === 'info') {
                return $service->getFixtureContent('v2.0_info.json');
            } elseif ($action === 'consumer') {
                return $service->getFixtureContent('v2.0_consumer-access_token-notexistingtoken.json');
            } elseif ($action === 'consumer/validate') {
                return $service->getFixtureContent('v2.0_consumer_validate-access_token-notexistingtoken.json');
            } elseif ($action === 'plugins' && !empty($params['purchase_type']) && $params['purchase_type'] === PurchaseType::TYPE_PAID) {
                return $service->getFixtureContent('v2.0_plugins-purchase_type-paid-access_token-notexistingtoken.json');
            }
        });
        return static::build($service);
    }

    public static function buildValidLicense()
    {
        $service = new Service();
        $service->setOnDownloadCallback(function ($action, $params) use ($service) {
            if ($action === 'info') {
                return $service->getFixtureContent('v2.0_info.json');
            } elseif ($action === 'consumer') {
                return $service->getFixtureContent('v2.0_consumer-access_token-consumer2_paid1.json');
            } elseif ($action === 'consumer/validate') {
                return $service->getFixtureContent('v2.0_consumer_validate-access_token-consumer2_paid1.json');
            } elseif ($action === 'plugins' && !empty($params['purchase_type']) && $params['purchase_type'] === PurchaseType::TYPE_PAID) {
                return $service->getFixtureContent('v2.0_plugins-purchase_type-paid-access_token-consumer2_paid1.json');
            }
        });
        return static::build($service);
    }

    public static function buildExceededLicense()
    {
        $service = new Service();
        $service->setOnDownloadCallback(function ($action, $params) use ($service) {
            if ($action === 'info') {
                return $service->getFixtureContent('v2.0_info.json');
            } elseif ($action === 'consumer') {
                return $service->getFixtureContent('v2.0_consumer-num_users-201-access_token-consumer1_paid2_custom1.json');
            } elseif ($action === 'consumer/validate') {
                return $service->getFixtureContent('v2.0_consumer_validate-access_token-consumer1_paid2_custom1.json');
            } elseif ($action === 'plugins' && !empty($params['purchase_type']) && $params['purchase_type'] === PurchaseType::TYPE_PAID) {
                return $service->getFixtureContent('v2.0_plugins-purchase_type-paid-num_users-201-access_token-consumer1_paid2_custom1.json');
            }
        });

        return static::build($service);
    }

    public static function buildExpiredLicense()
    {
        $service = new Service();
        $service->setOnDownloadCallback(function ($action, $params) use ($service) {
            if ($action === 'info') {
                return $service->getFixtureContent('v2.0_info.json');
            } elseif ($action === 'consumer') {
                return $service->getFixtureContent('v2.0_consumer-access_token-consumer3_paid1_custom2.json');
            } elseif ($action === 'consumer/validate') {
                return $service->getFixtureContent('v2.0_consumer_validate-access_token-consumer3_paid1_custom2.json');
            } elseif ($action === 'plugins' && !empty($params['purchase_type']) && $params['purchase_type'] === PurchaseType::TYPE_PAID) {
                return $service->getFixtureContent('v2.0_plugins-purchase_type-paid-access_token-consumer1_paid2_custom1.json');
            }
        });
        return static::build($service);
    }
}
