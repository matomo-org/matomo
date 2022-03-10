<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\AssetManager;


abstract class UIAsset
{
    abstract public function validateFile();

    /**
     * @return string
     */
    abstract public function getAbsoluteLocation();

    /**
     * @return string
     */
    abstract public function getRelativeLocation();

    /**
     * @return string
     */
    abstract public function getBaseDirectory();

    /**
     * Removes the previous file if it exists.
     * Also tries to remove compressed version of the file.
     *
     * @see ProxyStaticFile::serveStaticFile(serveFile
     * @throws Exception if the file couldn't be deleted
     */
    abstract public function delete();

    /**
     * @param string $content
     * @throws \Exception
     */
    abstract public function writeContent($content);

    /**
     * @return string
     */
    abstract public function getContent();

    /**
     * @return boolean
     */
    abstract public function exists();

    /**
     * @return int
     */
    abstract public function getModificationDate();
}
