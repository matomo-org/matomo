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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $regionsFile = __DIR__ . '/../data/isoRegionNames.php';

        $output->setDecorated(true);

        $output->writeln('Starting region codes update');

        $output->write('Fetching region codes from ' . $this->source);

        try {
            $newContent = Http::sendHttpRequest($this->source, 1000);
        } catch (\Exception $e) {
            $output->writeln(' <fg=red>X (Fetching content failed)</>');
            return 1;
        }

        $regionData = json_decode($newContent, true);

        if (empty($regionData)) {
            $output->writeln(' <fg=red>X (Content could not be parsed)</>');
            return 1;
        }

        $output->writeln(' <fg=green>✓</>');

        $newRegions = [];
        foreach ($regionData['3166-2'] as $region) {

            // some fixes of incorrect region codes
            if ($region['code'] === 'SS-EE8') {
                $region['code'] = 'SS-EE';
            }
            if ($region['code'] === 'ML-BK0') {
                $region['code'] = 'ML-BKO';
            }
            if ($region['code'] === 'IQ-SW') {
                $region['code'] = 'IQ-SU';
            }
            if ($region['code'] === 'MU-RP') {
                $region['code'] = 'MU-RR';
            }

            list($countryCode, $regionCode) = explode('-', $region['code']);
            $newRegions[$countryCode][$regionCode] = $region['name'];
        }

        $currentRegions = include $regionsFile;

        // regions for Saint Lucia missing in iso-codes
        if (empty($newRegions['LC']) && !empty($currentRegions['LC'])) {
            $newRegions['LC'] = $currentRegions['LC'];
        }

        // regions for Republic of Côte d'Ivoire still outdated in iso-codes
        $newRegions['CI'] = $currentRegions['CI'];

        // regions missing in iso-codes
        $isoCodesMissing = [
            'AR-F', 'BI-MY', 'DO-31', 'DO-32', 'DO-33', 'DO-34', 'DO-35', 'DO-36', 'DO-37', 'DO-38', 'DO-39', 'DO-40', 'DO-41', 'DO-42',
            'EG-LX', 'HT-NI', 'IQ-KI', 'IR-32', 'KG-GO', 'KZ-BAY', 'LR-GP', 'LR-RG', 'MK-85', 'QA-SH', 'SD-GK', 'SI-212', 'SI-213',
            'TH-38', 'TJ-DU', 'TJ-RA', 'TT-MRC', 'TT-TOB', 'YE-HU'
        ];

        foreach ($isoCodesMissing as $isoCode) {
            list($countryCode, $regionCode) = explode('-', $isoCode);

            if (!empty($newRegions[$countryCode][$regionCode])) {
                continue; // skip if it was already icnluded
            }

            $newRegions[$countryCode][$regionCode] = $currentRegions[$countryCode][$regionCode];
            ksort($newRegions[$countryCode], SORT_NATURAL);
        }

        ksort($newRegions);

        if (json_encode($newRegions) === json_encode($currentRegions)) {
            $output->writeln('Everything already up to date <fg=green>✓</>');
            return 0;
        }

        $content = <<<CONTENT
<?php
// Generated file containing all ISO region codes and names
return 
CONTENT;

        $content .= var_export($newRegions, true) . ';';

        file_put_contents($regionsFile, $content);

        $output->writeln('File successfully updated <fg=green>✓</>');
        return 0;

    }


}
