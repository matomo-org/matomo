<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Feed.php 20519 2010-01-22 14:06:24Z padraic $
 */

/**
 * @see Zend_Date
 */
// require_once 'Zend/Date.php';

/**
 * @see Zend_Date
 */
// require_once 'Zend/Uri.php';

/**
 * @see Zend_Feed_Writer
 */
// require_once 'Zend/Feed/Writer.php';

/**
 * @see Zend_Feed_Writer_Entry
 */
// require_once 'Zend/Feed/Writer/Entry.php';

/**
 * @see Zend_Feed_Writer_Deleted
 */
// require_once 'Zend/Feed/Writer/Deleted.php';

/**
 * @see Zend_Feed_Writer_Renderer_Feed_Atom
 */
// require_once 'Zend/Feed/Writer/Renderer/Feed/Atom.php';

/**
 * @see Zend_Feed_Writer_Renderer_Feed_Rss
 */
// require_once 'Zend/Feed/Writer/Renderer/Feed/Rss.php';

// require_once 'Zend/Feed/Writer/Feed/FeedAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Writer_Feed extends Zend_Feed_Writer_Feed_FeedAbstract
implements Iterator, Countable
{

    /**
     * Contains all entry objects
     *
     * @var array
     */
    protected $_entries = array();

    /**
     * A pointer for the iterator to keep track of the entries array
     *
     * @var int
     */
    protected $_entriesKey = 0;

    /**
     * Creates a new Zend_Feed_Writer_Entry data container for use. This is NOT
     * added to the current feed automatically, but is necessary to create a
     * container with some initial values preset based on the current feed data.
     *
     * @return Zend_Feed_Writer_Entry
     */
    public function createEntry()
    {
        $entry = new Zend_Feed_Writer_Entry;
        if ($this->getEncoding()) {
            $entry->setEncoding($this->getEncoding());
        }
        $entry->setType($this->getType());
        return $entry;
    }

    /**
     * Appends a Zend_Feed_Writer_Deleted object representing a new entry tombstone
     * to the feed data container's internal group of entries.
     *
     * @param Zend_Feed_Writer_Deleted $entry
     */
    public function addTombstone(Zend_Feed_Writer_Deleted $deleted)
    {
        $this->_entries[] = $deleted;
    }
    
    /**
     * Creates a new Zend_Feed_Writer_Deleted data container for use. This is NOT
     * added to the current feed automatically, but is necessary to create a
     * container with some initial values preset based on the current feed data.
     *
     * @return Zend_Feed_Writer_Deleted
     */
    public function createTombstone()
    {
        $deleted = new Zend_Feed_Writer_Deleted;
        if ($this->getEncoding()) {
            $deleted->setEncoding($this->getEncoding());
        }
        $deleted->setType($this->getType());
        return $deleted;
    }

    /**
     * Appends a Zend_Feed_Writer_Entry object representing a new entry/item
     * the feed data container's internal group of entries.
     *
     * @param Zend_Feed_Writer_Entry $entry
     */
    public function addEntry(Zend_Feed_Writer_Entry $entry)
    {
        $this->_entries[] = $entry;
    }

    /**
     * Removes a specific indexed entry from the internal queue. Entries must be
     * added to a feed container in order to be indexed.
     *
     * @param int $index
     */
    public function removeEntry($index)
    {
        if (isset($this->_entries[$index])) {
            unset($this->_entries[$index]);
        }
        // require_once 'Zend/Feed/Exception.php';
        throw new Zend_Feed_Exception('Undefined index: ' . $index . '. Entry does not exist.');
    }

    /**
     * Retrieve a specific indexed entry from the internal queue. Entries must be
     * added to a feed container in order to be indexed.
     *
     * @param int $index
     */
    public function getEntry($index = 0)
    {
        if (isset($this->_entries[$index])) {
            return $this->_entries[$index];
        }
        // require_once 'Zend/Feed/Exception.php';
        throw new Zend_Feed_Exception('Undefined index: ' . $index . '. Entry does not exist.');
    }

    /**
     * Orders all indexed entries by date, thus offering date ordered readable
     * content where a parser (or Homo Sapien) ignores the generic rule that
     * XML element order is irrelevant and has no intrinsic meaning.
     *
     * Using this method will alter the original indexation.
     *
     * @return void
     */
    public function orderByDate()
    {
        /**
         * Could do with some improvement for performance perhaps
         */
        $timestamp = time();
        $entries = array();
        foreach ($this->_entries as $entry) {
            if ($entry->getDateModified()) {
                $timestamp = (int) $entry->getDateModified()->get(Zend_Date::TIMESTAMP);
            } elseif ($entry->getDateCreated()) {
                $timestamp = (int) $entry->getDateCreated()->get(Zend_Date::TIMESTAMP);
            }
            $entries[$timestamp] = $entry;
        }
        krsort($entries, SORT_NUMERIC);
        $this->_entries = array_values($entries);
    }

    /**
     * Get the number of feed entries.
     * Required by the Iterator interface.
     *
     * @return int
     */
    public function count()
    {
        return count($this->_entries);
    }

	/**
     * Return the current entry
     *
     * @return Zend_Feed_Reader_Entry_Interface
     */
    public function current()
    {
        return $this->_entries[$this->key()];
    }

    /**
     * Return the current feed key
     *
     * @return unknown
     */
    public function key()
    {
        return $this->_entriesKey;
    }

	/**
     * Move the feed pointer forward
     *
     * @return void
     */
    public function next()
    {
        ++$this->_entriesKey;
    }

    /**
     * Reset the pointer in the feed object
     *
     * @return void
     */
    public function rewind()
    {
        $this->_entriesKey = 0;
    }

    /**
     * Check to see if the iterator is still valid
     *
     * @return boolean
     */
    public function valid()
    {
        return 0 <= $this->_entriesKey && $this->_entriesKey < $this->count();
    }

    /**
     * Attempt to build and return the feed resulting from the data set
     *
     * @param $type The feed type "rss" or "atom" to export as
     * @return string
     */
    public function export($type, $ignoreExceptions = false)
    {
        $this->setType(strtolower($type));
        $type = ucfirst($this->getType());
        if ($type !== 'Rss' && $type !== 'Atom') {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid feed type specified: ' . $type . '.'
            . ' Should be one of "rss" or "atom".');
        }
        $renderClass = 'Zend_Feed_Writer_Renderer_Feed_' . $type;
        $renderer = new $renderClass($this);
        if ($ignoreExceptions) {
            $renderer->ignoreExceptions();
        }
        return $renderer->render()->saveXml();
    }

}
