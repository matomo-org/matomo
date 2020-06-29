<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomJsTracker;

use Piwik\Plugins\CustomJsTracker\Exception\AccessDeniedException;

class File
{
    /**
     * @var string
     */
    protected $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function setFile($file)
    {
        return new static($file);
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

    public function isFileContentSame($content)
    {
        // we determine if file content is the same in here in case a different "file" implementation needs to check
        // whether multiple files are up to date
        return $this->getContent() === $content;
    }

    public function save($content)
    {
        if (false === file_put_contents($this->file, $content, LOCK_EX)) {
            throw new AccessDeniedException(sprintf("Could not write to %s", $this->file));
        }
        // we need to return an array of files in case some other "File" implementation actually updates multiple files
        // eg one file per trusted host
        return [$this->getPath()];
    }

    public function getContent()
    {
        if (!$this->hasReadAccess()) {
            return null;
        }

        return file_get_contents($this->file);
    }

    public function getPath()
    {
        return $this->file;
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
