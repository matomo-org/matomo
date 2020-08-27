<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\GeoIp2\Commands;

use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Option;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertRegionCodesToIso extends ConsoleCommand
{
    const OPTION_NAME = 'regioncodes_converted';
    const MAPPING_TABLE_NAME = 'fips2iso';

    protected function configure()
    {
        $this->setName('usercountry:convert-region-codes');
        $this->setDescription("Convert FIPS region codes saved by GeoIP legacy provider to ISO.");
    }

    public function isEnabled()
    {
        return (LocationProvider::getCurrentProvider() instanceof GeoIp2);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // chick if option is set to disable second run
        if (Option::get(self::OPTION_NAME)) {
            $output->writeln('Converting region codes already done.');
            return;
        }

        $output->setDecorated(true);

        $output->write('Creating mapping table in database');

        Db::query('DROP table if exists ' . self::MAPPING_TABLE_NAME);

        DbHelper::createTable(self::MAPPING_TABLE_NAME,
            "`country_code` VARCHAR(2) NOT NULL,
                           `fips_code` VARCHAR(2) NOT NULL,
                           `iso_code` VARCHAR(4) NULL DEFAULT NULL,
                           PRIMARY KEY (`country_code`, `fips_code`)");

        $output->writeln(' <fg=green>✓</>');

        $mappings = include __DIR__ . '/../data/regionMapping.php';

        $output->write('Inserting mapping data ');

        $counter = 0;
        foreach ($mappings as $country => $regionMapping) {
            foreach ($regionMapping as $fips => $iso) {
                if ($fips == $iso) {
                    continue; // nothing needs to be changed, so ignore the mapping
                }

                Db::query('INSERT INTO `'.Common::prefixTable(self::MAPPING_TABLE_NAME).'` VALUES (?, ?, ?)', [$country, $fips, $iso]);
                $counter++;
                if ($counter%50 == 0) {
                    $output->write('.');
                }
            }
        }

        $output->writeln(' <fg=green>✓</>');

        $output->writeln('Updating Matomo log tables:');

        $activationTime = Option::get(GeoIp2::SWITCH_TO_ISO_REGIONS_OPTION_NAME);
        $activationDateTime = date('Y-m-d H:i:s', $activationTime);

        // fix country and region of tibet so it will be updated correctly afterwards
        $tibetFixQuery = 'UPDATE %s SET location_country = "cn", location_region = "14" WHERE location_country = "ti"';

        // replace invalid country codes used by GeoIP Legacy
        $fixInvalidCountriesQuery = 'UPDATE %s SET location_country = "" WHERE location_country IN("AP", "EU", "A1", "A2")';

        $query = "UPDATE %s INNER JOIN %s ON location_country = country_code AND location_region = fips_code SET location_region = iso_code
                  WHERE `%s` < ?";

        $logTables = ['log_visit' => 'visit_first_action_time', 'log_conversion' => 'server_time'];

        foreach ($logTables as $logTable => $dateField) {
            $output->write('- Updating ' . $logTable);

            Db::query(sprintf($tibetFixQuery, Common::prefixTable($logTable)));
            Db::query(sprintf($fixInvalidCountriesQuery, Common::prefixTable($logTable)));

            $sql = sprintf($query, Common::prefixTable($logTable), Common::prefixTable(self::MAPPING_TABLE_NAME), $dateField);
            Db::query($sql, $activationDateTime);

            $output->writeln(' <fg=green>✓</>');
        }

        $output->write('Removing mapping table from database ');
        Db::dropTables(Common::prefixTable(self::MAPPING_TABLE_NAME));
        $output->writeln(' <fg=green>✓</>');

        // save option to prevent a second run
        Option::set(self::OPTION_NAME, true);

        $output->writeln('All region codes converted.');
    }


}
