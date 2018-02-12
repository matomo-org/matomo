<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Intl\Commands;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Development;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Unzip;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console Command to generate region name translations for Piwik
 *
 * This script downloads and parses maxminds city location file as CSV
 */
class CreateRegionData extends ConsoleCommand
{
    const GEOLITE2_DOWNLOAD_URI_CSV = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City-CSV.zip';
    const LOCATION_FILE_GLOB = 'GeoLite2-City-Locations-';
    const TABLE_NAME = 'geoip2locations';

    public function isEnabled()
    {
        return Development::isEnabled();
    }

    protected function configure()
    {
        $this->setName('translations:generate-region-data')
            ->setDescription('Generates Region-data for Piwik');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = StaticContainer::get('path.tmp') . '/regions';
        Filesystem::mkdir($path);

        $zipFile = $path . '/GeoLite2-City-CSV.zip';

        try {
            $output->writeln('Downloading GeoLite2-City-CSV.zip');
            $this->downloadGeoIPDatabase($zipFile);
            $output->writeln('Unzipping GeoLite2-City-CSV.zip');
            $locationFiles = $this->unzipLocationFilesFromGeoIpDatabase($zipFile, $path);
            foreach ($locationFiles as $locationFile) {
                $output->writeln('Converting ' . $locationFile);
                $language = strtolower(str_replace(self::LOCATION_FILE_GLOB, '', basename($locationFile, '.csv')));
                $regionData = $this->extractRegionsFromLocationFile($locationFile);
                $this->writeRegionDataToFile($language, $regionData);
                $output->writeln('Imported ' . count($regionData) . ' subdivisions for language ' . $language);
            }
            $this->dropTemporaryTable();

        } catch (Exception $e) {
            $output->writeln('Updating region names failed: ' . $e->getMessage());
        }
    }

    protected function writeRegionDataToFile($langCode, $regions)
    {
        $writePath = Filesystem::getPathToPiwikRoot() . '/plugins/Intl/lang/%s.json';

        $translations = json_decode(file_get_contents(sprintf($writePath, $langCode)), true);

        foreach ($translations['Intl'] as $key => $translation) {
            if (strpos('Region_', $key) === 0) {
                unset($translations['Intl'][$key]);
            }
        }

        foreach ($regions AS $code => $regionName) {
            $key = 'Region_' . $code;
            $translations['Intl'][$key] = $regionName;
        }

        file_put_contents(sprintf($writePath, $langCode), json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    protected function downloadGeoIPDatabase($target)
    {
        if (file_exists($target)) {
            return;
        }

        $url = self::GEOLITE2_DOWNLOAD_URI_CSV;
        try {
            $success = Http::sendHttpRequest($url, $timeout = 3600, $userAgent = null, $target);
        } catch (Exception $ex) {
            throw new Exception("failed to download '$url' to '$target': " . $ex->getMessage());
        }

        if ($success !== true) {
            throw new Exception("failed to download '$url' to '$target'! (Unknown error)");
        }
    }

    protected function unzipLocationFilesFromGeoIpDatabase($source, $target)
    {
        $locationFiles = [];
        try {
            $unzip = Unzip::factory('PclZip', $source);
            $files = $unzip->extract($target);

            foreach ($files as $file) {
                if (strpos($file['filename'], self::LOCATION_FILE_GLOB) > 0) {
                    $dir = dirname($file['stored_filename']);
                    if (!empty($dir)) {
                        Filesystem::copy($file['filename'], $target . '/' . basename($file['stored_filename']));
                    }
                    $locationFiles[] = $target . '/' . basename($file['stored_filename']);
                }
            }
            if (!empty($dir)) {
                Filesystem::unlinkRecursive($target .'/'. $dir, true);
            }

        } catch (Exception $e) {
            throw new Exception("failed to unzip '$source' to '$target': " . $e->getMessage());
        }

        #Filesystem::remove($source); // remove downloaded zip

        return $locationFiles;
    }

    protected function extractRegionsFromLocationFile($csvFile)
    {
        $this->createTemporaryTableIfNotExists();

        Db::query("LOAD DATA LOW_PRIORITY LOCAL INFILE '$csvFile' INTO TABLE `".self::TABLE_NAME."` CHARACTER SET utf8 FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\n' IGNORE 1 LINES (`geoname_id`, `locale_code`, `continent_code`, `continent_name`, `country_iso_code`, `country_name`, `subdivision_1_iso_code`, `subdivision_1_name`, `subdivision_2_iso_code`, `subdivision_2_name`, `city_name`, `metro_code`, `time_zone`);");

        $regiondata = DB::fetchAll("SELECT DISTINCT country_iso_code, subdivision_1_iso_code, subdivision_1_name 
                                   FROM geoip2locations 
                                   WHERE subdivision_1_iso_code != '' and subdivision_1_name != '' 
                                   ORDER BY country_iso_code, subdivision_1_iso_code;");

        $regions = [];
        foreach ($regiondata as $region) {
            $key = strtoupper($region['country_iso_code'].'_'.$region['subdivision_1_iso_code']);
            $regions[$key] = $region['subdivision_1_name'];
        }

        return $regions;
    }


    private function createTemporaryTableIfNotExists()
    {
        Db::query('CREATE TABLE IF NOT EXISTS `'.self::TABLE_NAME.'` (
                            `geoname_id` VARCHAR(10) NULL DEFAULT NULL,
                            `locale_code` VARCHAR(10) NULL DEFAULT NULL,
                            `continent_code` VARCHAR(10) NULL DEFAULT NULL,
                            `continent_name` VARCHAR(20) NULL DEFAULT NULL,
                            `country_iso_code` VARCHAR(255) NULL DEFAULT NULL,
                            `country_name` VARCHAR(10) NULL DEFAULT NULL,
                            `subdivision_1_iso_code` VARCHAR(10) NULL DEFAULT NULL,
                            `subdivision_1_name` VARCHAR(50) NULL DEFAULT NULL,
                            `subdivision_2_iso_code` VARCHAR(10) NULL DEFAULT NULL,
                            `subdivision_2_name` VARCHAR(50) NULL DEFAULT NULL,
                            `city_name` VARCHAR(100) NULL DEFAULT NULL,
                            `metro_code` VARCHAR(10) NULL DEFAULT NULL,
                            `time_zone` VARCHAR(50) NULL DEFAULT NULL
                        )
                        COLLATE=\'utf8mb4_general_ci\'
                        ENGINE=InnoDB;');

        Db::query('TRUNCATE TABLE `'.self::TABLE_NAME.'`');
    }

    private function dropTemporaryTable()
    {
        Db::query('DROP TABLE `'.self::TABLE_NAME.'`');
    }
}
