<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

class AmazonCloudFront extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return 'Amazon CloudFront';
    }

    public static function getIcon(): string
    {
        return './plugins/Morpheus/icons/dist/brand/Amazon.png';
    }

    public static function getContentType(): int
    {
        return self::TYPE_CMS;
    }

    /**
     * @param array<string>|null $headers
     */
    public function isDetected(?string $data = null, ?array $headers = null): bool
    {
        if (!is_array($headers)) {
            return false;
        }

        return (
            $this->headerContains($headers, 'server', 'cloudfront')
            || $this->headerContains($headers, 'via', 'cloudfront')
            || $this->headerContains($headers, 'x-cache', 'cloudfront')
            || $this->headerExists($headers, 'x-amz-cf-id')
            || $this->headerExists($headers, 'x-amz-cf-pop')
        );
    }

    /**
     * @param array<string> $headers
     */
    private function headerContains(array $headers, string $header, string $value): bool
    {
        $headerValues = $this->findHeaderValues($headers, $header);

        foreach ($headerValues as $headerValue) {
            if (false !== stripos($headerValue, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string> $headers
     */
    private function headerExists(array $headers, string $header): bool
    {
        return [] !== $this->findHeaderValues($headers, $header);
    }

    /**
     * @param array<string> $headers
     *
     * @return array<string>
     */
    private function findHeaderValues(array $headers, string $header): array
    {
        $values = [];

        foreach ($headers as $name => $value) {
            if (strcasecmp($name, $header) === 0) {
                $values[] = $value;
            }
        }

        return $values;
    }
}
