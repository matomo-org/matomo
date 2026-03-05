<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Translation\Loader;

use Piwik\Common;

/**
 * Loads translations from JSON files.
 */
class JsonFileLoader implements LoaderInterface
{
    public function load($language, array $directories)
    {
        if (empty($language)) {
            return array();
        }

        $translations = array();

        foreach ($directories as $directory) {
            $filename = $directory . '/' . $language . '.json';

            if (! file_exists($filename)) {
                continue;
            }

            $translations = array_replace_recursive(
                $translations,
                $this->loadFile($filename)
            );
        }

        return $translations;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function loadFile(string $filename): array
    {
        $data = file_get_contents($filename);
        $translations = json_decode($data, true);

        if (is_null($translations) && Common::hasJsonErrorOccurred()) {
            throw new \Exception(sprintf(
                'Not able to load translation file %s: %s',
                $filename,
                Common::getLastJsonError()
            ));
        }

        if (!is_array($translations)) {
            return [];
        }

        return $translations;
    }
}
