<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomPiwikJs;

use Piwik\Plugins\CustomPiwikJs\Exception\AccessDeniedException;

class File
{
    /**
     * @var string
     */
    private $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function checkReadable()
    {
        if (!$this->hasReadAccess()) {
            throw new AccessDeniedException(sprintf('The file %s is not readable', $this->file));
        }
    }

    public function checkWritable()
    {
        if (!$this->hasWriteAccess()) {
            throw new AccessDeniedException(sprintf('The file %s is not writable', $this->file));
        }
    }

    public function save($content)
    {
        if(false === file_put_contents($this->file, $content)) {
            throw new AccessDeniedException(sprintf("Could not write to %s", $this->file));
        }
    }

    public function getContent()
    {
        if (!$this->hasReadAccess()) {
            return null;
        }

        return file_get_contents($this->file);
    }

    public function getName()
    {
        return basename($this->file);
    }

    /**
     * @return bool
     */
    public function hasWriteAccess()
    {
        if (file_exists($this->file) && !is_writable($this->file)) {
            return false;
        }
        return is_writable(dirname($this->file)) || is_writable($this->file);
    }

    /**
     * @return bool
     */
    public function hasReadAccess()
    {
        return file_exists($this->file) && is_readable($this->file);
    }


}
