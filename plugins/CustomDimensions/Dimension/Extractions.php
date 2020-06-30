<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CustomDimensions\Dimension;

use Exception;

class Extractions
{
    private $extractions = array();

    public function __construct($extractions)
    {
        $this->extractions = $extractions;
    }

    public function check()
    {
        if (!is_array($this->extractions)) {
            throw new Exception('extractions has to be an array');
        }

        foreach ($this->extractions as $extraction) {

            if (!is_array($extraction)) {
                throw new \Exception('Each extraction within extractions has to be an array');
            }

            if (count($extraction) !== 2
                || !array_key_exists('dimension', $extraction)
                || !array_key_exists('pattern', $extraction)) {

                throw new \Exception('Each extraction within extractions must have a key "dimension" and "pattern" only');
            }

            $extraction = new Extraction($extraction['dimension'], $extraction['pattern']);
            $extraction->check();
        }
    }

}