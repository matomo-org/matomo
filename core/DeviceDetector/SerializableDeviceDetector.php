<?php

namespace Piwik\DeviceDetector;

use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;

class SerializableDeviceDetector extends DeviceDetector
{
    public function __construct(DeviceDetector $detector, string $userAgent = '', ?ClientHints $clientHints = null)
    {
        parent::__construct($userAgent, $clientHints);

        $this->clientHints = $detector->clientHints;
        $this->bot = $detector->bot;
        $this->client = $detector->client;
        $this->device = $detector->device;
        $this->os = $detector->os;
        $this->brand = $detector->brand;
        $this->model = $detector->model;
    }
}
