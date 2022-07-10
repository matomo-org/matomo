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

class UpdateRegionCodes extends ConsoleCommand
{
    public $source = 'https://salsa.debian.org/iso-codes-team/iso-codes/-/raw/main/data/iso_3166-2.json';

    protected function configure()
    {
        $this->setName('usercountry:update-region-codes');
        $this->setDescription("Updates the ISO region names");
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
                } else {
                    $newRegions[$countryCode][$regionCode] = $regionData;
                    $newRegions[$countryCode][$regionCode]['current'] = false;
                }
            }
        }


        if (json_encode($newRegions) === json_encode($currentRegions)) {
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
//             // list of previous names or names in other languages
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
}
