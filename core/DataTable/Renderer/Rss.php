<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Renderer;

use Exception;
use Piwik\Archive;
use Piwik\Common;
use Piwik\DataTable\Renderer;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\SettingsPiwik;

/**
 * RSS Feed.
 * The RSS renderer can be used only on Set that are arrays of DataTable.
 * A RSS feed contains one dataTable per element in the Set.
 *
 */
class Rss extends Renderer
{
    /**
     * Computes the dataTable output and returns the string/binary
     *
     * @return string
     */
    public function render()
    {
        return $this->renderTable($this->table);
    }

    /**
     * Computes the output for the given data table
     *
     * @param DataTable $table
     * @return string
     * @throws Exception
     */
    protected function renderTable($table)
    {
        if (!($table instanceof DataTable\Map)
            || $table->getKeyName() != 'date'
        ) {
            throw new Exception("RSS feeds can be generated for one specific website &idSite=X." .
                "\nPlease specify only one idSite or consider using &format=XML instead.");
        }

        $idSite = Common::getRequestVar('idSite', 1, 'int');
        $period = Common::getRequestVar('period');

        $piwikUrl = SettingsPiwik::getPiwikUrl()
            . "?module=CoreHome&action=index&idSite=" . $idSite . "&period=" . $period;
        $out = "";
        $moreRecentFirst = array_reverse($table->getDataTables(), true);
        foreach ($moreRecentFirst as $date => $subtable) {
            /** @var DataTable $subtable */
            $timestamp = $subtable->getMetadata(Archive\DataTableFactory::TABLE_METADATA_PERIOD_INDEX)->getDateStart()->getTimestamp();
            $site = $subtable->getMetadata(Archive\DataTableFactory::TABLE_METADATA_SITE_INDEX);

            $pudDate = date('r', $timestamp);

            $dateInSiteTimezone = Date::factory($timestamp)->setTimezone($site->getTimezone())->toString('Y-m-d');
            $thisPiwikUrl = Common::sanitizeInputValue($piwikUrl . "&date=$dateInSiteTimezone");
            $siteName = $site->getName();
            $title = $siteName . " on " . $date;

            $out .= "\t<item>
		<pubDate>$pudDate</pubDate>
		<guid>$thisPiwikUrl</guid>
		<link>$thisPiwikUrl</link>
		<title>$title</title>
		<author>http://piwik.org</author>
		<description>";

            $out .= Common::sanitizeInputValue($this->renderDataTable($subtable));
            $out .= "</description>\n\t</item>\n";
        }

        $header = $this->getRssHeader();
        $footer = $this->getRssFooter();

        return $header . $out . $footer;
    }

    /**
     * Returns the RSS file footer
     *
     * @return string
     */
    protected function getRssFooter()
    {
        return "\t</channel>\n</rss>";
    }

    /**
     * Returns the RSS file header
     *
     * @return string
     */
    protected function getRssHeader()
    {
        $generationDate = date('r');
        $header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<rss version=\"2.0\">
  <channel>
    <title>piwik statistics - RSS</title>
    <link>http://piwik.org</link>
    <description>Piwik RSS feed</description>
    <pubDate>$generationDate</pubDate>
    <generator>piwik</generator>
    <language>en</language>
    <lastBuildDate>$generationDate</lastBuildDate>";
        return $header;
    }

    /**
     * @param DataTable $table
     *
     * @return string
     */
    protected function renderDataTable($table)
    {
        if ($table->getRowsCount() == 0) {
            return "<strong><em>Empty table</em></strong><br />\n";
        }

        $i = 1;
        $tableStructure = array();

        /*
         * table = array
         * ROW1 = col1 | col2 | col3 | metadata | idSubTable
         * ROW2 = col1 | col2 (no value but appears) | col3 | metadata | idSubTable
         * 		subtable here
         */
        $allColumns = array();
        foreach ($table->getRows() as $row) {
            foreach ($row->getColumns() as $column => $value) {
                // for example, goals data is array: not supported in export RSS
                // in the future we shall reuse ViewDataTable for html exports in RSS anyway
                if (is_array($value)) {
                    continue;
                }
                $allColumns[$column] = true;
                $tableStructure[$i][$column] = $value;
            }
            $i++;
        }
        $html = "\n";
        $html .= "<table border=1 width=70%>";
        $html .= "\n<tr>";
        foreach ($allColumns as $name => $toDisplay) {
            if ($toDisplay !== false) {
                if ($this->translateColumnNames) {
                    $name = $this->translateColumnName($name);
                }
                $html .= "\n\t<td><strong>$name</strong></td>";
            }
        }
        $html .= "\n</tr>";

        foreach ($tableStructure as $row) {
            $html .= "\n\n<tr>";
            foreach ($allColumns as $columnName => $toDisplay) {
                if ($toDisplay !== false) {
                    $value = "-";
                    if (isset($row[$columnName])) {
                        $value = urldecode($row[$columnName]);
                    }

                    $html .= "\n\t<td>$value</td>";
                }
            }
            $html .= "</tr>";
        }
        $html .= "\n\n</table>";
        return $html;
    }
}
