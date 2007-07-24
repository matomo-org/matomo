<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to version 1.0 of the Zend Framework
 * license, that is bundled with this package in the file LICENSE, and
 * is available through the world-wide-web at the following URL:
 * http://www.zend.com/license/framework/1_0.txt. If you did not receive
 * a copy of the Zend Framework license and are unable to obtain it
 * through the world-wide-web, please send a note to license@zend.com
 * so we can mail you a copy immediately.
 *
 * @package    Zend_Mail
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */


/**
 * Zend_Mail_Storage_Folder_Maildir
 */
require_once 'Zend/Mail/Storage/Folder/Maildir.php';

/**
 * Zend_Mail_Storage_Exception
 */
require_once 'Zend/Mail/Storage/Exception.php';

/**
 * Zend_Mail_Storage_Writable_Interface
 */
require_once 'Zend/Mail/Storage/Writable/Interface.php';


/**
 * @package    Zend_Mail
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */
class Zend_Mail_Storage_Writable_Maildir extends    Zend_Mail_Storage_Folder_Maildir
                                         implements Zend_Mail_Storage_Writable_Interface
{
    /**
     * create a new folder
     *
     * This method also creates parent folders if necessary. Some mail storages may restrict, which folder
     * may be used as parent or which chars may be used in the folder name
     *
     * @param string                          $name         global name of folder, local name if $parentFolder is set
     * @param string|Zend_Mail_Storage_Folder $parentFolder parent folder for new folder, else root folder is parent
     * @return string only used internally (new created maildir)
     * @throw Zend_Mail_Storage_Exception
     */
    public function createFolder($name, $parentFolder = null)
    {
        if ($parentFolder instanceof Zend_Mail_Storage_Folder) {
            $folder = $parentFolder->getGlobalName() . $this->_delim . $name;
        } else if ($parentFolder != null) {
            $folder = rtrim($parentFolder, $this->_delim) . $this->_delim . $name;
        } else {
            $folder = $name;
        }

        $folder = trim($folder, $this->_delim);

        // first we check if we try to create a folder that does exist
        $exists = null;
        try {
            $exists = $this->getFolders($folder);
        } catch (Zend_Mail_Exception $e) {
            // ok
        }
        if ($exists) {
            throw new Zend_Mail_Storage_Exception('folder already exists');
        }

        if (strpos($folder, $this->_delim . $this->_delim) !== false) {
            throw new Zend_Mail_Storage_Exception('invalid name - folder parts may not be empty');
        }

        if (strpos($folder, 'INBOX' . $this->_delim) === 0) {
            $folder = substr($folder, 6);
        }

        $fulldir = $this->_rootdir . '.' . $folder;

        // check if we got tricked and would create a dir outside of the rootdir or not as direct child
        if (strpos($folder, DIRECTORY_SEPARATOR) !== false || strpos($folder, '/') !== false
            || dirname($fulldir) . DIRECTORY_SEPARATOR != $this->_rootdir) {
            throw new Zend_Mail_Storage_Exception('invalid name - no directory seprator allowed in folder name');
        }

        // has a parent folder?
        $parent = null;
        if (strpos($folder, $this->_delim)) {
            // let's see if the parent folder exists
            $parent = substr($folder, 0, strrpos($folder, $this->_delim));
            try {
                $this->getFolders($parent);
            } catch (Zend_Mail_Exception $e) {
                // does not - create parent folder
                $this->createFolder($parent);
            }
        }

        if (!@mkdir($fulldir) || !@mkdir($fulldir . DIRECTORY_SEPARATOR . 'cur')) {
            throw new Zend_Mail_Storage_Exception('error while creating new folder, may be created incompletly');
        }

        mkdir($fulldir . DIRECTORY_SEPARATOR . 'new');
        mkdir($fulldir . DIRECTORY_SEPARATOR . 'tmp');

        $localName = $parent ? substr($folder, strlen($parent) + 1) : $folder;
        $this->getFolders($parent)->$localName = new Zend_Mail_Storage_Folder($localName, $folder, true);

        return $fulldir;
    }

    /**
     * remove a folder
     *
     * @param string|Zend_Mail_Storage_Folder $name      name or instance of folder
     * @return null
     * @throw Zend_Mail_Storage_Exception
     */
    public function removeFolder($name)
    {
        // TODO: This could fail in the middle of the task, which is not optimal.
        // But there is no defined standard way to mark a folder as removed and there is no atomar fs-op
        // to remove a directory. Also moving the folder to a/the trash folder is not possible, as
        // all parent folders must be created. What we could do is add a dash to the front of the
        // directory name and it should be ignored as long as other processes obey the standard.

        if ($name instanceof Zend_Mail_Storage_Folder) {
            $name = $name->getGlobalName();
        }

        $name = trim($name, $this->_delim);
        if (strpos($name, 'INBOX' . $this->_delim) === 0) {
            $name = substr($name, 6);
        }

        // check if folder exists and has no children
        if (!$this->getFolders($name)->isLeaf()) {
            throw new Zend_Mail_Storage_Exception('delete children first');
        }

        if ($name == 'INBOX' || $name == '/') {
            throw new Zend_Mail_Storage_Exception('wont delete INBOX');
        }

        if ($name == $this->getCurrentFolder()) {
            throw new Zend_Mail_Storage_Exception('wont delete selected folder');
        }

        foreach (array('tmp', 'new', 'cur', '.') as $subdir) {
            $dir = $this->_rootdir . '.' . $name . '/' . $subdir;
            if (!file_exists($dir)) {
                continue;
            }
            $dh = opendir($dir);
            if (!$dh) {
                throw new Zend_Mail_Storage_Exception("error opening $subdir");
            }
            while (($entry = readdir($dh)) !== false) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                if (!unlink($dir . '/' . $entry)) {
                    throw new Zend_Mail_Storage_Exception("error cleaning $subdir");
                }
            }
            closedir($dh);
            if ($subdir !== '.') {
                if (!rmdir($dir)) {
                    throw new Zend_Mail_Storage_Exception("error removing $subdir");
                }
            }
        }

        if (!rmdir($this->_rootdir . '.' . $name)) {
            // at least we should try to make it a valid maildir again
            mkdir($this->_rootdir . '.' . $name . '/' . 'cur');
            throw new Zend_Mail_Storage_Exception("error removing maindir");
        }

        $parent = strpos($name, $this->_delim) ? substr($name, 0, strrpos($name, $this->_delim)) : null;
        $localName = $parent ? substr($name, strlen($parent) + 1) : $name;
        unset($this->getFolders($parent)->$localName);
    }

    /**
     * rename and/or move folder
     *
     * The new name has the same restrictions as in createFolder()
     *
     * @param string|Zend_Mail_Storage_Folder $oldName name or instance of folder
     * @param string                          $newName new global name of folder
     * @return null
     * @throw Zend_Mail_Storage_Exception
     */
    public function renameFolder($oldName, $newName)
    {
        // TODO: This is also not atomar and has similar problems as removeFolder()

        if ($oldName instanceof Zend_Mail_Storage_Folder) {
            $oldName = $oldName->getGlobalName();
        }

        $oldName = trim($oldName, $this->_delim);
        if (strpos($oldName, 'INBOX' . $this->_delim) === 0) {
            $oldName = substr($oldName, 6);
        }

        $newName = trim($newName, $this->_delim);
        if (strpos($newName, 'INBOX' . $this->_delim) === 0) {
            $newName = substr($newName, 6);
        }

        if (strpos($newName, $oldName . $this->_delim) === 0) {
            throw new Zend_Mail_Storage_Exception('new folder cannot be a child of old folder');
        }

        // check if folder exists and has no children
        $folder = $this->getFolders($oldName);

        if ($oldName == 'INBOX' || $oldName == '/') {
            throw new Zend_Mail_Storage_Exception('wont rename INBOX');
        }

        if ($oldName == $this->getCurrentFolder()) {
            throw new Zend_Mail_Storage_Exception('wont rename selected folder');
        }

        $newdir = $this->createFolder($newName);

        if (!$folder->isLeaf()) {
            foreach ($folder as $k => $v) {
                $this->renameFolder($v->getGlobalName(), $newName . $this->_delim . $k);
            }
        }

        $olddir = $this->_rootdir . '.' . $folder;
        foreach (array('tmp', 'new', 'cur') as $subdir) {
            $subdir = DIRECTORY_SEPARATOR . $subdir;
            if (!file_exists($olddir . $subdir)) {
                continue;
            }
            // using copy or moving files would be even better - but also much slower
            if (!rename($olddir . $subdir, $newdir . $subdir)) {
                throw new Zend_Mail_Storage_Exception('error while moving ' . $subdir);
            }
        }
        // create a dummy if removing fails - otherwise we can't read it next time
        mkdir($olddir . DIRECTORY_SEPARATOR . 'cur');
        $this->removeFolder($oldName);
    }

    /**
     * create a uniqueid for maildir filename
     *
     * This is nearly the format defined in the maildir standard. The microtime() call should already
     * create a uniqueid, the pid is for multicore/-cpu machine that manage to call this function at the
     * exact same time, and uname() gives us the hostname for multiple machines accessing the same storage.
     *
     * If someone disables posix we create a random number of the same size, so this method should also
     * work on Windows - if you manage to get maildir working on Windows.
     * Microtime could also be disabled, altough I've never seen it.
     *
     * @return string new uniqueid
     */
    protected function _createUniqueId()
    {
        $id = '';
        $id .= function_exists('microtime') ? microtime(true) : (time() . ' ' . rand(0, 100000));
        $id .= '.' . (function_exists('posix_getpid') ? posix_getpid() : rand(50, 65535));
        $id .= '.' . php_uname('n');

        return $id;
    }

    /**
     * open a temporary maildir file
     *
     * makes sure tmp/ exists and create a file with a unique name
     * you should close the returned filehandle!
     *
     * @param string $folder name of current folder without leading .
     * @return array array('dirname' => dir of maildir folder, 'uniq' => unique id, 'filename' => name of create file
     *                     'handle'  => file opened for writing)
     * @throw Zend_Mail_Storage_Exception
     */
    protected function _createTmpFile($folder)
    {
        $tmpdir = $this->_rootdir . '.' . $folder . '/tmp/';
        if (!file_exists($tmpdir)) {
            if (!mkdir($tmpdir)) {
                throw new Zend_Mail_Storage_Exception('problems creating tmp dir');
            }
        }

        // we should retry to create a unique id if a file with the same name exists
        // to avoid a script timeout we only wait 1 second (instead of 2) and stop
        // after a defined retry count
        // if you change this variable take into account that it can take up to $max_tries seconds
        // normally we should have a valid unique name after the first try, we're just following the "standard" here
        $max_tries = 5;
        for ($i = 0; $i < $max_tries; ++$i) {
            $uniq = $this->_createUniqueId();
            if (!file_exists($tmpdir . $uniq)) {
                // here is the race condition! - as defined in the standard
                // to avoid having a long time between stat()ing the file and creating it we're opening it here
                // to mark the filename as taken
                $fh = fopen($tmpdir . $uniq, 'w');
                if (!$fh) {
                    throw new Zend_Mail_Storage_Exception('could not open temp file');
                }
                break;
            }
            sleep(1);
        }

        if (!$fh) {
            throw new Zend_Mail_Storage_Exception("tried $max_tries unique ids for a temp file, but all were taken"
                                                . ' - giving up');
        }

        return array('dirname' => $this->_rootdir . '.' . $folder, 'uniq' => $uniq, 'filename' => $tmpdir . $uniq,
                     'handle' => $fh);
    }

    /**
     * create an info string for filenames with given flags
     *
     * @param array $flags wanted flags, with the reference you'll get the set flags with correct key (= char for flag)
     * @return string info string for version 2 filenames including the leading colon
     */
    protected function _getInfoString(&$flags)
    {
        // accessing keys is easier, faster and it removes duplicated flags
        $wanted_flags = array_flip($flags);
        if (isset($wanted_flags[Zend_Mail_Storage::FLAG_RECENT])) {
            throw new Zend_Mail_Storage_Exception('recent flag may not be set');
        }

        $info = ':2,';
        $flags = array();
        foreach (Zend_Mail_Storage_Maildir::$_knownFlags as $char => $flag) {
            if (!isset($wanted_flags[$flag])) {
                continue;
            }
            $info .= $char;
            $flags[$char] = $flag;
            unset($wanted_flags[$flag]);
        }

        if (!empty($wanted_flags)) {
            $wanted_flags = implode(', ', array_keys($wanted_flags));
            throw new Zend_Mail_Storage_Exception('unknown flag(s): ' . $wanted_flags);
        }

        return $info;
    }

    /**
     * append a new message to mail storage
     *
     * @param string                                     $message message as string or instance of message class
     * @param null|string|Zend_Mail_Storage_Folder       $folder  folder for new message, else current folder is taken
     * @param null|array                                 $flags   set flags for new message, else a default set is used
     * @throw Zend_Mail_Storage_Exception
     */
     // not yet * @param string|Zend_Mail_Message|Zend_Mime_Message $message message as string or instance of message class

    public function appendMessage($message, $folder = null, $flags = null)
    {
        if ($folder === null) {
            $folder = $this->_currentFolder;
        }

        if (!($folder instanceof Zend_Mail_Storage_Folder)) {
            $folder = $this->getFolders($folder);
        }

        if ($flags === null) {
            $flags = array(Zend_Mail_Storage::FLAG_SEEN);
        }
        $info = $this->_getInfoString($flags);
        $temp_file = $this->_createTmpFile($folder->getGlobalName());

        // TODO: handle class instances for $message
        fputs($temp_file['handle'], $message);
        fclose($temp_file['handle']);

        // we're adding the size to the filename for maildir++
        $size = filesize($temp_file['filename']);
        if ($size) {
            $info = ',S=' . $size . $info;
        }
        $new_filename = $temp_file['dirname'] . '/cur/' . $temp_file['uniq'] . $info;

        // we're throwing any exception after removing our temp file and saving it to this variable instead
        $exception = null;

        if (!link($temp_file['filename'], $new_filename)) {
            $exception = new Zend_Mail_Storage_Exception('cannot link message file to final dir');
        }
        @unlink($temp_file['filename']);

        if ($exception) {
            throw $exception;
        }

        $this->_files[] = array('uniq'     => $temp_file['uniq'],
                                'flags'    => $flags,
                                'filename' => $new_filename);
    }

    /**
     * copy an existing message
     *
     * @param int                             $id     number of message
     * @param string|Zend_Mail_Storage_Folder $folder name or instance of targer folder
     * @return null
     * @throw Zend_Mail_Storage_Exception
     */
    public function copyMessage($id, $folder)
    {
        if (!($folder instanceof Zend_Mail_Storage_Folder)) {
            $folder = $this->getFolders($folder);
        }

        $filedata = $this->_getFileData($id);
        $old_file = $filedata['filename'];
        $flags = $filedata['flags'];

        // copied message can't be recent
        while (($key = array_search(Zend_Mail_Storage::FLAG_RECENT, $flags)) !== false) {
            unset($flags[$key]);
        }
        $info = $this->_getInfoString($flags);

        // we're creating the copy as temp file before moving to cur/
        $temp_file = $this->_createTmpFile($folder->getGlobalName());
        // we don't write directly to the file
        fclose($temp_file['handle']);

        // we're adding the size to the filename for maildir++
        // TODO: maybe we should support maildirsize or we just let the MDA do the work
        $size = filesize($old_file);
        if ($size) {
            $info = ',S=' . $size . $info;
        }
        $new_file = $temp_file['dirname'] . '/cur/' . $temp_file['uniq'] . $info;

        // we're throwing any exception after removing our temp file and saving it to this variable instead
        $exception = null;

        if (!copy($old_file, $temp_file['filename'])) {
            $exception = new Zend_Mail_Storage_Exception('cannot copy message file');
        } else if (!link($temp_file['filename'], $new_file)) {
            $exception = new Zend_Mail_Storage_Exception('cannot link message file to final dir');
        }
        @unlink($temp_file['filename']);

        if ($exception) {
            throw $exception;
        }

        if ($folder->getGlobalName() == $this->_currentFolder
            || ($this->_currentFolder == 'INBOX' && $folder->getGlobalName() == '/')) {
            $this->_files[] = array('uniq'     => $temp_file['uniq'],
                                    'flags'    => $flags,
                                    'filename' => $new_file);
        }
    }

    /**
     * set flags for message
     *
     * NOTE: this method can't set the recent flag.
     *
     * @param int   $id    number of message
     * @param array $flags new flags for message
     * @throw Zend_Mail_Storage_Exception
     */
    public function setFlags($id, $flags)
    {
        $info = $this->_getInfoString($flags);
        $filedata = $this->_getFileData($id);

        // TODO: move file from new to cur
        $new_filename = dirname($filedata['filename']) . "/$filedata[uniq]$info";

        if (!@rename($filedata['filename'], $new_filename)) {
            throw new Zend_Mail_Storage_Exception('cannot rename file');
        }

        $filedata['flags']    = $flags;
        $filedata['filename'] = $new_filename;

        $this->_files[$id - 1] = $filedata;
    }


    /**
     * stub for not supported message deletion
     *
     * @return null
     * @throws Zend_Mail_Storage_Exception
     */
    public function removeMessage($id)
    {
        $filename = $this->_getFileData($id, 'filename');
        if (!@unlink($filename)) {
            throw new Zend_Mail_Storage_Exception('cannot remove message');
        }
        unset($this->_files[$id - 1]);
        // remove the gap
        $this->_files = array_values($this->_files);
    }
}
