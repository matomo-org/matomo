<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UserCountry\Commands;

use Piwik\Development;
use Piwik\Http;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console Command to generate ISO region names for Piwik
 *
 * This script uses the master data of debian/iso-codes repository to fetch available iso data
 */
class GenerateIsoRegions extends ConsoleCommand
{
    public function isEnabled()
    {
        return Development::isEnabled();
    }

    protected function configure()
    {
        $this->setName('translations:generate-iso-regions')
             ->setDescription('Generates ISO region names for Piwik');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $regionDataUrl = 'https://salsa.debian.org/debian/iso-codes/raw/master/data/iso_3166-2.json';

        $allRegions = [];

        try {
            $regionData = Http::fetchRemoteFile($regionDataUrl);
            $regionData = json_decode($regionData, true);
            $regionData = $regionData['3166-2'];


            foreach ($regionData as $region) {
                $regionCode = $region['code'];
                $regionName = $region['name'];

                $allRegions[$regionCode] = $regionName;
            }

        } catch (\Exception $e) {
            $output->writeln('Unable to import region names');
            return;
        }

        $content = "<?php\n// Generated file containing all ISO region codes and names\nreturn " . var_export($allRegions, true) . ";";

        file_put_contents(__DIR__ . '/../data/IsoRegionNames.php', $content);

        $output->writeln('Region names saved.');
    }
}
