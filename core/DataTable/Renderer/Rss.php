<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * RSS Feed.
 * The RSS renderer can be used only on Piwik_DataTable_Array that are arrays of Piwik_DataTable.
 * A RSS feed contains one dataTable per element in the Piwik_DataTable_Array.
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Renderer_Rss extends Piwik_DataTable_Renderer
{
    /**
     * Computes the dataTable output and returns the string/binary
     *
     * @return string
     */
    function render()
    {
        $this->renderHeader();
        return $this->renderTable($this->table);
    }

    /**
     * Computes the exception output and returns the string/binary
     *
     * @return string
     */
    function renderException()
    {
        header('Content-type: text/plain');
        $exceptionMessage = $this->getExceptionMessage();
        return 'Error: ' . $exceptionMessage;
    }

    /**
     * Computes the output for the given data table
     *
     * @param Piwik_DataTable $table
     * @return string
     * @throws Exception
     */
    protected function renderTable($table)
    {
        if (!($table instanceof Piwik_DataTable_Array)
            || $table->getKeyName() != 'date'
        ) {
            throw new Exception("RSS feeds can be generated for one specific website &idSite=X." .
                "\nPlease specify only one idSite or consider using &format=XML instead.");
        }

        $idSite = Piwik_Common::getRequestVar('idSite', 1, 'int');
        $period = Piwik_Common::getRequestVar('period');

        $piwikUrl = Piwik_Url::getCurrentUrlWithoutFileName()
            . "?module=CoreHome&action=index&idSite=" . $idSite . "&period=" . $period;
        $out = "";
        $moreRecentFirst = array_reverse($table->getArray(), true);
        foreach ($moreRecentFirst as $date => $subtable) {
            $timestamp = $subtable->getMetadata('timestamp');
            $site = $subtable->getMetadata('site');

            $pudDate = date('r', $timestamp);

            $dateInSiteTimezone = Piwik_Date::factory($timestamp)->setTimezone($site->getTimezone())->toString('Y-m-d');
            $thisPiwikUrl = Piwik_Common::sanitizeInputValue($piwikUrl . "&date=$dateInSiteTimezone");
            $siteName = $site->getName();
            $title = $siteName . " on " . $date;

            $out .= "\t<item>
		<pubDate>$pudDate</pubDate>
		<guid>$thisPiwikUrl</guid>
		<link>$thisPiwikUrl</link>
		<title>$title</title>
		<author>http://piwik.org</author>
		<description>";

            $out .= Piwik_Common::sanitizeInputValue($this->renderDataTable($subtable));
            $out .= "</description>\n\t</item>\n";
        }

        $header = $this->getRssHeader();
        $footer = $this->getRssFooter();

        return $header . $out . $footer;
    }

    /**
     * Sends the xml file http header
     */
    protected function renderHeader()
    {
        @header('Content-Type: text/xml; charset=utf-8');
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

    protected function renderDataTable($table)
    {
        if ($table->getRowsCount() == 0) {
            return "<b><i>Empty table</i></b><br />\n";
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
                $html .= "\n\t<td><b>$name</b></td>";
            }
        }
        $html .= "\n</tr>";
        $colspan = count($allColumns);

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
