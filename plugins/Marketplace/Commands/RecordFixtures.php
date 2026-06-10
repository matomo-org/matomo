<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\Commands;

use Piwik\Http;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\Marketplace\Api\Service;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\FixtureRepository;

/**
 * marketplace:record-fixtures
 *
 * Fetches a canonical set of Marketplace requests against the live plugins
 * marketplace and writes the responses into plugins/Marketplace/tests/resources/,
 * updating manifest.json. Used by developers to refresh the fixtures CI relies on.
 *
 * This command intentionally bypasses PIWIK_TEST_MODE so it can hit the real
 * marketplace; do not run inside a test process.
 */
class RecordFixtures extends ConsoleCommand
{
    /**
     * Each entry: ['action' => string, 'params' => array, 'fixture' => string, 'status' => ?int].
     * Mirrors what System tests exercise plus anything the manifest needs.
     */
    private const RECORDINGS = [
        ['action' => 'info', 'params' => [], 'fixture' => 'v2.0_info.json'],
        ['action' => 'plugins', 'params' => [], 'fixture' => 'v2.0_plugins.json'],
        ['action' => 'themes', 'params' => [], 'fixture' => 'v2.0_themes.json'],
        ['action' => 'plugins', 'params' => ['keywords' => 'login'], 'fixture' => 'system_v2.0_plugins-keywords-login.json'],
        ['action' => 'plugins', 'params' => ['purchase_type' => 'free'], 'fixture' => 'system_v2.0_plugins-purchase_type-free.json'],
        ['action' => 'plugins', 'params' => ['purchase_type' => 'paid'], 'fixture' => 'v2.0_plugins-purchase_type-paid-access_token-notexistingtoken.json'],
        ['action' => 'plugins/Barometer/info', 'params' => [], 'fixture' => 'v2.0_plugins_Barometer_info.json'],
        ['action' => 'plugins/TreemapVisualization/info', 'params' => [], 'fixture' => 'v2.0_plugins_TreemapVisualization_info.json'],
        ['action' => 'plugins/PaidPlugin1/info', 'params' => [], 'fixture' => 'v2.0_plugins_PaidPlugin1_info.json'],
        ['action' => 'plugins/SecurityInfo/info', 'params' => [], 'fixture' => 'system_v2.0_plugins_SecurityInfo_info.json'],
        ['action' => 'plugins/FormAnalytics/info', 'params' => [], 'fixture' => 'system_v2.0_plugins_FormAnalytics_info.json'],
        ['action' => 'plugins/NotExistingPlugIn/info', 'params' => [], 'fixture' => 'system_v2.0_plugins_NotExistingPlugIn_info.json', 'status' => 404],
        ['action' => 'consumer', 'params' => [], 'fixture' => 'v2.0_consumer-access_token-notexistingtoken.json', 'status' => 401],
        ['action' => 'consumer/validate', 'params' => [], 'fixture' => 'v2.0_consumer_validate-access_token-notexistingtoken.json'],
    ];

    protected function configure()
    {
        $this->setName('marketplace:record-fixtures');
        $this->setDescription('Record live Marketplace responses to fixture files used by CI tests.');
        $this->addOptionalValueOption(
            'domain',
            null,
            'Marketplace domain to record against',
            'https://plugins.matomo.org'
        );
        $this->addOptionalValueOption(
            'only',
            null,
            'Comma-separated list of actions to record (record all if omitted)',
            ''
        );
        $this->addOptionalValueOption(
            'manifest',
            null,
            'Path to manifest.json',
            PIWIK_INCLUDE_PATH . '/plugins/Marketplace/tests/resources/manifest.json'
        );
    }

    protected function doExecute(): int
    {
        $output = $this->getOutput();
        $input = $this->getInput();

        if (defined('PIWIK_TEST_MODE') && PIWIK_TEST_MODE) {
            $output->writeln('<error>Refusing to record from inside PIWIK_TEST_MODE — would hit the fixture interceptor instead of the live marketplace.</error>');
            return self::FAILURE;
        }

        $domain = rtrim((string) $input->getOption('domain'), '/');
        $manifestPath = (string) $input->getOption('manifest');
        $onlyRaw = (string) $input->getOption('only');
        $only = $onlyRaw === '' ? null : array_map('trim', explode(',', $onlyRaw));

        $repository = new FixtureRepository(dirname($manifestPath));
        $service = new Service($domain);
        $manifest = $this->loadManifest($manifestPath);

        foreach (self::RECORDINGS as $recording) {
            if ($only !== null && !in_array($recording['action'], $only, true)) {
                continue;
            }

            $action = $recording['action'];
            $params = $recording['params'];
            $fixture = $recording['fixture'];
            $expectedStatus = $recording['status'] ?? 200;

            $output->writeln(sprintf('  → recording %s %s', $action, $params ? Http::buildQuery($params) : ''));

            try {
                $response = $service->fetch($action, $params, null, true, false);
            } catch (\Exception $e) {
                $output->writeln(sprintf('    <error>FAILED: %s</error>', $e->getMessage()));
                continue;
            }

            $rawData = $response['data'] ?? null;
            if (!is_string($rawData)) {
                $rawData = json_encode($rawData);
            }

            $targetPath = dirname($manifestPath) . '/' . $fixture;
            file_put_contents($targetPath, $rawData . "\n");

            $key = $repository->buildCanonicalKey(
                sprintf('%s/api/2.0/%s?%s', $domain, $action, Http::buildQuery($params)),
                null
            );

            $entry = $fixture;
            $status = $response['status'] ?? $expectedStatus;
            if ($status >= 400) {
                $entry = ['file' => $fixture, 'status' => (int) $status];
            }

            $manifest[$key] = $entry;
            $output->writeln(sprintf('    saved %s (status %d)', $fixture, $status));
        }

        $this->saveManifest($manifestPath, $manifest);
        $output->writeln('<info>Done. Review the diff, run tests, and commit fixture changes.</info>');

        return self::SUCCESS;
    }

    /**
     * @return array<string, string|array>
     */
    private function loadManifest(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }
        $decoded = json_decode(file_get_contents($path), true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, string|array> $manifest
     */
    private function saveManifest(string $path, array $manifest): void
    {
        ksort($manifest);
        $json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($path, $json . "\n");
    }
}
