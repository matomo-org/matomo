<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\AssetManager\UIAsset;

use Exception;
use Piwik\AssetManager\UIAsset;

class InMemoryUIAsset extends UIAsset
{
    private $content;

    public function validateFile()
    {
        return;
    }

    public function getAbsoluteLocation()
    {
        throw new Exception('invalid operation');
    }

    public function getRelativeLocation()
    {
        throw new Exception('invalid operation');
    }

    public function getBaseDirectory()
    {
        throw new Exception('invalid operation');
    }

    public function delete()
    {
        $this->content = null;
    }

    public function exists()
    {
        return false;
    }

    public function writeContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getModificationDate()
    {
        throw new Exception('invalid operation');
    }
}
