<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\AssetManager;
use Piwik\Development;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugin\Manager;
use Piwik\ProxyHttp;
use Piwik\SettingsPiwik;
use Piwik\Theme;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ComputeJsAssetSize extends ConsoleCommand
{
    private $totals = [];

    protected function configure()
    {
        $this->setName('development:compute-js-asset-size');
        $this->setDescription('Generates production assets and computes the size of the resulting code.');
        $this->addOption('exclude-angular', null, InputOption::VALUE_NONE);
        $this->addOption('no-delete', null, InputOption::VALUE_NONE, 'Do not delete files after creating them.');
        $this->addOption('plugin', null, InputOption::VALUE_REQUIRED, 'For submodule plugins and 3rd party plugins.');
    }

    public function isEnabled()
    {
        return SettingsPiwik::isGitDeployment();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $excludeAngular = $input->getOption('exclude-angular');
        $noDelete = $input->getOption('no-delete');
        $plugin = $input->getOption('plugin');

        $this->checkDevelopmentModeDisabled();

        $this->ensureThirdPartyPluginsActivated($plugin);

        $output->writeln("Building and printing sizes of built JS assets...");

        $fetcher = $this->makeUmdFetcher();

        if ($excludeAngular) {
            $this->excludeAngular($output);
        }

        $this->deleteMergedAssets();
        $this->buildAssets($fetcher);

        $output->writeln("");

        $this->printCurrentGitHashAndBranch($output, $excludeAngular, $plugin);

        $output->writeln("");
        $this->printFilesizes($fetcher, $output);

        if (!$noDelete) {
            $this->deleteMergedAssets();
        }
    }

    private function ensureThirdPartyPluginsActivated($plugin = null)
    {
        $expectedPluginsLoadedAndActivated = [
            "CorePluginsAdmin",
            "CoreAdminHome",
            "CoreHome",
            "WebsiteMeasurable",
            "IntranetMeasurable",
            "Diagnostics",
            "CoreVisualizations",
            "Proxy",
            "API",
            "Widgetize",
            "Transitions",
            "LanguagesManager",
            "Actions",
            "Dashboard",
            "MultiSites",
            "Referrers",
            "UserLanguage",
            "DevicesDetection",
            "Goals",
            "Ecommerce",
            "SEO",
            "Events",
            "UserCountry",
            "GeoIp2",
            "VisitsSummary",
            "VisitFrequency",
            "VisitTime",
            "VisitorInterest",
            "RssWidget",
            "Feedback",
            "Monolog",
            "Login",
            "TwoFactorAuth",
            "UsersManager",
            "SitesManager",
            "Installation",
            "CoreUpdater",
            "CoreConsole",
            "ScheduledReports",
            "UserCountryMap",
            "Live",
            "PrivacyManager",
            "ImageGraph",
            "Annotations",
            "MobileMessaging",
            "Overlay",
            "SegmentEditor",
            "Insights",
            "Morpheus",
            "Contents",
            "TestRunner",
            "BulkTracking",
            "Resolution",
            "DevicePlugins",
            "Heartbeat",
            "Intl",
            "UserId",
            "CustomJsTracker",
            "Tour",
            "PagePerformance",
            "CustomDimensions",
            "TagManager",
            "AbTesting",
            "ActivityLog",
            "Bandwidth",
            "Cohorts",
            "CustomAlerts",
            "CustomReports",
            "CustomVariables",
            "DeviceDetectorCache",
            "FormAnalytics",
            "Funnels",
            "GoogleAnalyticsImporter",
            "InvalidateReports",
            "MarketingCampaignsReporting",
            "MediaAnalytics",
            "MultiChannelConversionAttribution",
            "QueuedTracking",
            "RollUpReporting",
            "SearchEngineKeywordsPerformance",
            "UsersFlow",
            "VisitorGenerator",
            "WhiteLabel",
            "WooCommerceAnalytics",
            "AdvertisingConversionExport",
            "AnonymousPiwikUsageMeasurement",
        ];

        if ($plugin) {
            $expectedPluginsLoadedAndActivated[] = $plugin;
        }

        if (is_file(PIWIK_INCLUDE_PATH . '/plugins/CoreVue/plugin.json')) {
            $expectedPluginsLoadedAndActivated[] = "CoreVue";
        }

        $expectedPluginsLoadedAndActivated = array_unique($expectedPluginsLoadedAndActivated);

        $pluginsLoadedAndActivated = Manager::getInstance()->getPluginsLoadedAndActivated();
        $pluginsLoadedAndActivated = array_map(function (Plugin $p) { return $p->getPluginName(); }, $pluginsLoadedAndActivated);

        $missingPlugins = array_diff($expectedPluginsLoadedAndActivated, $pluginsLoadedAndActivated);
        if (!empty($missingPlugins)) {
            throw new \Exception("Activate the following plugins before running this command: " . implode(", ", $missingPlugins));
        }
    }

    private function excludeAngular(OutputInterface $output)
    {
        Piwik::addAction('AssetManager.getJavaScriptFiles', function (&$files) use ($output) {
            $newFiles = [];
            foreach ($files as $filePath) {
                if (strpos($filePath, 'node_modules/angular') !== false) {
                    if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                        $output->writeln("<comment>  Excluding angular file $filePath.</comment>");
                    }
                    continue;
                }

                $newFiles[] = $filePath;
            }
            $files = $newFiles;
        });
    }

    private function buildAssets(AssetManager\UIAssetFetcher\PluginUmdAssetFetcher $fetcher)
    {
        AssetManager::getInstance()->getMergedCoreJavaScript();
        AssetManager::getInstance()->getMergedNonCoreJavaScript();

        $chunks = $fetcher->getChunkFiles();
        foreach ($chunks as $chunk) {
            AssetManager::getInstance()->getMergedJavaScriptChunk($chunk->getChunkName());
        }
    }

    private function deleteMergedAssets()
    {
        AssetManager::getInstance()->removeMergedAssets();
    }

    private function printFilesizes(AssetManager\UIAssetFetcher\PluginUmdAssetFetcher $fetcher, OutputInterface $output)
    {
        $fileSizes = [];

        $mergedCore = AssetManager::getInstance()->getMergedCoreJavaScript();
        $fileSizes[] = $this->getFileSizeRow($mergedCore);

        $mergedNonCore = AssetManager::getInstance()->getMergedNonCoreJavaScript();
        $fileSizes[] = $this->getFileSizeRow($mergedNonCore);

        $chunks = $fetcher->getChunkFiles();
        foreach ($chunks as $chunk) {
            $chunkAsset = AssetManager::getInstance()->getMergedJavaScriptChunk($chunk->getChunkName());
            $fileSizes[] = $this->getFileSizeRow($chunkAsset);
        }

        $fileSizes[] = [];
        $fileSizes[] = ['Total', $this->getFormattedSize($this->totals['merged']), $this->getFormattedSize($this->totals['gzip'])];

        $table = new Table($output);
        $table->setHeaders(['File', 'Size', 'Size (gzipped)'])->setRows($fileSizes);
        $table->render();
    }

    private function getFileSize($fileLocation, $type)
    {
        $size = filesize($fileLocation);
        $this->totals[$type] = ($this->totals[$type] ?? 0) + $size;
        return $this->getFormattedSize($size);
    }

    private function getFormattedSize($size)
    {
        $formatter = new Formatter();
        $size = $formatter->getPrettySizeFromBytes($size, 'K', 2);
        return $size;
    }

    private function checkDevelopmentModeDisabled()
    {
        if (Development::isEnabled()) {
            throw new \Exception("This command is to estimate production build sizes, so development mode must be disabled for it.");
        }
    }

    private function getGzippedFileSize($path)
    {
        $data = file_get_contents($path);
        $data = ProxyHttp::gzencode($data);

        if (false === $data) {
            throw new \Exception('compressing file '.$path.' failed');
        }

        $compressedPath = dirname($path) . '/' . basename($path) . '.gz';
        file_put_contents($compressedPath, $data);
        return $this->getFileSize($compressedPath, 'gzip');
    }

    private function printCurrentGitHashAndBranch(OutputInterface $output, $excludeAngular, $plugin = null)
    {
        $branchName = trim(`git rev-parse --abbrev-ref HEAD`);
        $lastCommit = trim(`git log --pretty=format:'%h' -n 1`);

        $pluginSuffix = '';
        if ($plugin) {
            $prefix = 'cd "' . addslashes(PIWIK_INCLUDE_PATH . '/plugins/' . $plugin) . '"; ';

            $pluginBranchName = trim(`$prefix git rev-parse --abbrev-ref HEAD`);
            $pluginLastCommit = trim(`$prefix git log --pretty=format:'%h' -n 1`);

            $pluginSuffix = " [$plugin: $pluginBranchName ($pluginLastCommit)]";
        }

        $output->writeln("<info>$branchName ($lastCommit)$pluginSuffix</info> <comment>"
            . ($excludeAngular ? '(without angularjs)' : '') . "</comment>");
    }

    private function makeUmdFetcher()
    {
        $plugins = Manager::getInstance()->getPluginsLoadedAndActivated();
        $pluginNames = array_map(function ($p) { return $p->getPluginName(); }, $plugins);

        $theme = Manager::getInstance()->getThemeEnabled();
        if (!empty($theme)) {
            $theme = new Theme();
        }

        $fetcher = new AssetManager\UIAssetFetcher\PluginUmdAssetFetcher($pluginNames, $theme, null);
        return $fetcher;
    }

    private function getFileSizeRow(AssetManager\UIAsset $asset)
    {
        return [$asset->getRelativeLocation(), $this->getFileSize($asset->getAbsoluteLocation(), 'merged'), $this->getGzippedFileSize($asset->getAbsoluteLocation())];
    }
}
