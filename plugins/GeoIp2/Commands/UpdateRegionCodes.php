<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\GeoIp2\Commands;

use Piwik\Development;
use Piwik\Http;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2\Php;
use Piwik\Plugins\UserCountry\LocationProvider;

/**
 * This command can be used to update the list of regions and their names that Matomo knows about.
 * A list of iso regions is fetched from the iso-codes project. This list will then be used to update the regions array
 * located in data/isoRegionNames
 * - new regions will be added as current
 * - changed names will be updated (adding the previous name as alternate name)
 * - removed regions will be kept, but marked as not current
 *
 * Additionally, this command can be used to add regions that are returned by DB IP GeoIP database
 * As the DBIP Lite database only contains region names, but no region codes, we try to map the returned name to a known
 * region. As DBIP in some cases returns names, that differ from the official region name, Matomo would be unable to
 * store those regions. To provide a better mapping this command allows to provide the --db-ip-csv option.
 * This option should provide the path to the DB-IP city lite database in CSV format. In addition, the paid DB-IP city
 * (mmdb) database should be configured in Matomo as location provider.
 * The command will then iterate through all IP ranges defined in the CSV database and query a look-up using the
 * location provider. The returned region iso code and region name is then compared with those included in the regions
 * array. Missing regions will be added (as not current), mismatching names will be added as alternate names.
 * This will ensure that regions returned by the lite database should be mapped correctly.
 * Attention: Using this option will take a couple of hours to process.
 */
class UpdateRegionCodes extends ConsoleCommand
{
    public $source = 'https://salsa.debian.org/iso-codes-team/iso-codes/-/raw/main/data/iso_3166-2.json';

    protected function configure()
    {
        $this->setName('usercountry:update-region-codes');
        $this->setDescription("Updates the ISO region names");
        $this->addOptionalValueOption('db-ip-csv', null, 'Uses the provided DB IP CSV database to iterate over all included IP ranges.');
    }

    public function isEnabled()
    {
        return Development::isEnabled();
    }

    /**
     * @return int
     */
    protected function doExecute(): int
    {
        $output = $this->getOutput();
        $input = $this->getInput();

        $regionsFile = __DIR__ . '/../data/isoRegionNames.php';

        $output->setDecorated(true);

        $output->writeln('Starting region codes update');

        $output->write('Fetching region codes from ' . $this->source);

        try {
            $newContent = Http::sendHttpRequest($this->source, 1000);
        } catch (\Exception $e) {
            $output->writeln(' <fg=red>X (Fetching content failed)</>');
            return self::FAILURE;
        }

        $regionData = json_decode($newContent, true);

        if (empty($regionData)) {
            $output->writeln(' <fg=red>X (Content could not be parsed)</>');
            return self::FAILURE;
        }

        $output->writeln(' <fg=green>✓</>');

        $newRegions = [];
        foreach ($regionData['3166-2'] as $region) {
            list($countryCode, $regionCode) = explode('-', $region['code']);
            $newRegions[$countryCode][$regionCode] = [
                'name' => $region['name'],
                'altNames' => [],
                'current' => true
            ];
        }


        ksort($newRegions);

        $currentRegions = include $regionsFile;

        foreach ($currentRegions as $countryCode => $regions) {
            foreach ($regions as $regionCode => $regionData) {
                if (isset($newRegions[$countryCode][$regionCode])) {
                    $newRegions[$countryCode][$regionCode]['altNames'] = $regionData['altNames'];

                    if (
                        $newRegions[$countryCode][$regionCode]['name'] !== $regionData['name']
                        && !in_array($regionData['name'], $newRegions[$countryCode][$regionCode]['altNames'])
                    ) {
                        $newRegions[$countryCode][$regionCode]['altNames'][] = $regionData['name'];
                    }

                    if (($key = array_search($newRegions[$countryCode][$regionCode]['name'], $newRegions[$countryCode][$regionCode]['altNames'])) !== false) {
                        unset($newRegions[$countryCode][$regionCode]['altNames'][$key]);
                        $newRegions[$countryCode][$regionCode]['altNames'] = array_values($newRegions[$countryCode][$regionCode]['altNames']);
                    }
                } else {
                    $newRegions[$countryCode][$regionCode] = $regionData;
                    $newRegions[$countryCode][$regionCode]['current'] = false;
                }
            }
        }

        $dbIpCsvFile = $input->getOption('db-ip-csv');

        if (!empty($dbIpCsvFile)) {
            $this->enrichWithDbIpRegions($dbIpCsvFile, $newRegions);
        }

        if (json_encode($newRegions) === json_encode($currentRegions)) {
            $output->writeln('');
            $output->writeln('Everything already up to date <fg=green>✓</>');
            return self::SUCCESS;
        }

        $content = <<<CONTENT
<?php
// The below list contains all ISO region codes and names known to Matomo
// Format:
// <CountryCode> => [
//     <RegionCode> => [
//         'name' => <CurrentISOName>
//         'altNames' => [
//             // list of previous names or names used by GeoIP providers like db-ip
//         ],
//         'current' => <bool> indicating if the iso code is currently used
//     ]
// ]
return 
CONTENT;

        $content .= var_export($newRegions, true) . ';';

        file_put_contents($regionsFile, $content);

        $output->writeln('File successfully updated <fg=green>✓</>');

        return self::SUCCESS;
    }

    private function enrichWithDbIpRegions(string $dbIpCsvFile, array &$regions)
    {
        $output = $this->getOutput();
        $output->writeln('Start looking through GeoIP database for missing region names');

        $php = new Php();

        $supportedInfo = $php->getSupportedLocationInfo();

        if (empty($supportedInfo[LocationProvider::REGION_CODE_KEY])) {
            $output->writeln(' <fg=red>X Region codes not supported by currently used GeoIP database. Skipping.</>');
            return;
        }

        $output->writeln('Iterating through all IPv4 addresses...');

        $this->initProgressBar(6396645);

        $handle = fopen($dbIpCsvFile, 'r');

        while(!feof($handle)) {
            $csv = str_getcsv(fgets($handle));
            $ip = $csv[0] ?? '';

            $this->advanceProgressBar();

            if (empty($ip)) {
                continue;
            }

            $location = $php->getLocation(['ip' => $ip]);

            $countryCode = $location[LocationProvider::COUNTRY_CODE_KEY] ?? null;
            $regionCode = $location[LocationProvider::REGION_CODE_KEY] ?? null;
            $regionName = $location[LocationProvider::REGION_NAME_KEY] ?? null;

            if (empty($countryCode) || empty($regionCode) || empty($regionName)) {
                continue;
            }

            if (!array_key_exists($countryCode, $regions)) {
                continue;
            }

            if (!array_key_exists($regionCode, $regions[$countryCode])) {
                $output->writeln('');
                $output->writeln("Adding missing region $regionName ($regionCode) for country $countryCode <fg=green>✓</>");
                $regions[$countryCode][$regionCode] = [
                    'name' => $regionName,
                    'altNames' => [],
                    'current' => false,
                ];
            } else {
                if (
                    $regionName !== $regions[$countryCode][$regionCode]['name']
                    && !in_array($regionName, $regions[$countryCode][$regionCode]['altNames'])
                ) {
                    $output->writeln('');
                    $output->writeln("Adding alternate region name $regionName to region {$regions[$countryCode][$regionCode]['name']} ($regionCode) for country $countryCode <fg=green>✓</>");
                    $regions[$countryCode][$regionCode]['altNames'][] = $regionName;
                }
            }
        }

        fclose($handle);
        $this->finishProgressBar();
    }
}
