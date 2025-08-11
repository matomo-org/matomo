<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ScheduledReports;

use Piwik\Container\StaticContainer;
use Piwik\Http;

class ScheduleReportSlack
{
    private $filename;

    private $fileContents;

    private $channel;

    private $fileID;

    private $token;

    private const SLACK_UPLOAD_URL_EXTERNAL = 'https://slack.com/api/files.getUploadURLExternal';
    private const SLACK_COMPLETE_UPLOAD_EXTERNAL = 'https://slack.com/api/files.completeUploadExternal';

    private const SLACK_TIMEOUT = 5000;

    public function send(string $fileName, string $fileContents, string $channel)
    {
        $this->filename = $fileName;
        $this->fileContents = $fileContents;
        $this->channel = $channel;

        $settings = StaticContainer::get(SystemSettings::class);
        $this->token = $settings->slackOauthToken->getValue();

        $uploadURL = $this->getUploadURLExternal();
        if (!empty($uploadURL) && $this->sendFile($uploadURL)) {
            return $this->completeUploadExternal();
        }

        return false;
    }

    private function getUploadURLExternal(): string
    {
        try {
            $response = $this->sendHTTPRequest(
                self::SLACK_UPLOAD_URL_EXTERNAL,
                self::SLACK_TIMEOUT,
                [
                    'token' => $this->token,
                    'filename' => $this->filename,
                    'length' => strlen($this->fileContents),
                ],
                ['Content-Type' => 'multipart/form-data']
            );
        } catch (\Exception $e) {
            // Add logging
            return '';
        }

        $data = json_decode($response, true);
        if (!empty($data) && !empty($data['upload_url']) && !empty($data['file_id'])) {
            $this->fileID = $data['file_id'];

            return $data['upload_url'];
        }

        return '';
    }

    private function sendFile(string $uploadURL): bool
    {
        try {
            $response = $this->sendHTTPRequest(
                $uploadURL,
                self::SLACK_TIMEOUT,
                [$this->fileContents],
                [],
                true
            );
        } catch (\Exception $e) {
            // Add logging
            return false;
        }

        return stripos($response, 'OK') !== false;
    }

    private function completeUploadExternal(): bool
    {
        try {
            $response = $this->sendHTTPRequest(
                self::SLACK_COMPLETE_UPLOAD_EXTERNAL,
                self::SLACK_TIMEOUT,
                [
                    'token' => $this->token,
                    'files' => json_encode([['id' => $this->fileID]]),
                    'channel_id' => $this->channel,
                    'initial_comment' => 'Here’s your file!'
                ],
                ['Content-Type' => 'multipart/form-data']
            );
        } catch (\Exception $e) {
            // Add logging
            return false;
        }

        $data = json_decode($response, true);

        return !empty($data['ok']);
    }

    private function sendHTTPRequest(string $url, int $timeout, array $requestBody, array $additionalHeaders = [], $requestBodyAsString = false)
    {
        if ($requestBodyAsString && !empty($requestBody[0])) {
            $requestBody = $requestBody[0];
        }

        return Http::sendHttpRequestBy(
            Http::getTransportMethod(),
            $url,
            $timeout,
            $userAgent = null,
            $destinationPath = null,
            $file = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $acceptInvalidSslCertificate = false,
            $byteRange = false,
            $getExtendedInfo = false,
            $httpMethod = 'POST',
            $httpUsername = null,
            $httpPassword = null,
            $requestBody,
            $additionalHeaders
        );
    }
}