<?php

namespace Piwik\Plugins\JsTrackerInstallCheck\NonceOption;

use Piwik\Piwik;

class JsTrackerInstallCheckNonceData
{
    protected $time;

    protected $url;

    protected $isSuccessful;

    public function __construct(int $time, string $url, bool $isSuccessful)
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \Exception(Piwik::translate('SitesManager_ExceptionInvalidUrl', [$url]));
        }

        $this->time = $time;
        $this->url = $url;
        $this->isSuccessful = $isSuccessful;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }

    public function toArray(): array
    {
        return [
            'time' => $this->time,
            'url' => $this->url,
            'isSuccessful' => $this->isSuccessful,
        ];
    }
}
