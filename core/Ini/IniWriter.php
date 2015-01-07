<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Ini;

/**
 * Writes INI configuration.
 */
class IniWriter
{
    /**
     * Writes an array configuration to a INI file.
     *
     * The array provided must be multidimensional, indexed by section names:
     *
     * ```
     * array(
     *     'Section 1' => array(
     *         'value1' => 'hello',
     *         'value2' => 'world',
     *     ),
     *     'Section 2' => array(
     *         'value3' => 'foo',
     *     )
     * );
     * ```
     *
     * @param string $filename
     * @param array $config
     * @param string $header Optional header to insert at the top of the file.
     * @throws IniWritingException
     */
    public function writeToFile($filename, array $config, $header = '')
    {
        $ini = $this->writeToString($config, $header);

        if (!file_put_contents($filename, $ini)) {
            throw new IniWritingException(sprintf('Impossible to write to file %s', $filename));
        }
    }

    /**
     * Writes an array configuration to a INI string and returns it.
     *
     * The array provided must be multidimensional, indexed by section names:
     *
     * ```
     * array(
     *     'Section 1' => array(
     *         'value1' => 'hello',
     *         'value2' => 'world',
     *     ),
     *     'Section 2' => array(
     *         'value3' => 'foo',
     *     )
     * );
     * ```
     *
     * @param array $config
     * @param string $header Optional header to insert at the top of the file.
     * @return string
     * @throws IniWritingException
     */
    public function writeToString(array $config, $header = '')
    {
        $ini = $header;

        $sectionNames = array_keys($config);

        foreach ($sectionNames as $sectionName) {
            $section = $config[$sectionName];

            // no point in writing empty sections
            if (empty($section)) {
                continue;
            }

            if (! is_array($section)) {
                throw new IniWritingException(sprintf("Section \"%s\" doesn't contain an array of values", $sectionName));
            }

            $ini .= "[$sectionName]\n";

            foreach ($section as $option => $value) {
                if (is_numeric($option)) {
                    $option = $sectionName;
                    $value = array($value);
                }

                if (is_array($value)) {
                    foreach ($value as $currentValue) {
                        $ini .= $option . '[] = ' . $this->encodeValue($currentValue) . "\n";
                    }
                } else {
                    $ini .= $option . ' = ' . $this->encodeValue($value) . "\n";
                }
            }

            $ini .= "\n";
        }

        return $ini;
    }

    private function encodeValue($value)
    {
        if (is_bool($value)) {
            return (int) $value;
        }
        if (is_string($value)) {
            return "\"$value\"";
        }
        return $value;
    }
}
