<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TestRunner\TravisYml;

/**
 * Utility class that will parse a .travis.yml file and return the contents of the
 * file's root YAML sections.
 */
class Parser
{
    /**
     * Parse existing data in a .travis.yml file that should be preserved in the output .travis.yml.
     * Includes comments.
     *
     * @var string $existingYmlPath The path to the existing .travis.yml file.
     * @return string[]
     */
    public function processExistingTravisYml($existingYmlPath)
    {
        $result = array();

        $existingYamlText = file_get_contents($existingYmlPath);
        foreach ($this->getRootSectionsFromYaml($existingYamlText) as $sectionName => $offset) {
            $section = $this->getRootSectionText($existingYamlText, $offset);
            $result[$sectionName] = $section;
        }

        return $result;
    }

    /**
     * Extracts the name and offset of all root elements of a YAML document. This method does this by
     * checking for text that starts at the beginning of a line and ends with a ':'.
     *
     * @param string $yamlText The YAML text to search through.
     * @return array Array mapping string section names with the starting offset of the text in the YAML.
     */
    private function getRootSectionsFromYaml($yamlText)
    {
        preg_match_all("/^[a-zA-Z_]+:/m", $yamlText, $allMatches, PREG_OFFSET_CAPTURE);

        $result = array();

        foreach ($allMatches[0] as $match) {
            $matchLength = strlen($match[0]);
            $sectionName = substr($match[0], 0, $matchLength - 1);

            $result[$sectionName] = $match[1] + $matchLength;
        }

        return $result;
    }

    /**
     * Gets the text of a root YAML element in a YAML doc using the name of the element and the starting
     * offset of the element's text. This is accomplished by searching for the first line that doesn't
     * start with whitespace after the given offset and using the text between the given offset and the
     * line w/o starting whitespace.
     *
     * @param string $yamlText The YAML text to search through.
     * @param int $offset The offset start of the YAML text (does not include the element name and colon, ie
     *                    the offset is after `'element:'`).
     * @return string
     */
    private function getRootSectionText($yamlText, $offset)
    {
        preg_match("/^[^\s]/m", $yamlText, $endMatches, PREG_OFFSET_CAPTURE, $offset);

        $endPos = isset($endMatches[0][1]) ? $endMatches[0][1] : strlen($yamlText);

        return substr($yamlText, $offset, $endPos - $offset);
    }
}