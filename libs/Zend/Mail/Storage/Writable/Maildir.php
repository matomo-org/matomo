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
 * @package    Zend_Mail
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Maildir.php 23775 2011-03-01 17:25:24Z ralph $
 */


/**
 * @see Zend_Mail_Storage_Folder_Maildir
 */
// require_once 'Zend/Mail/Storage/Folder/Maildir.php';

/**
 * @see Zend_Mail_Storage_Writable_Interface
 */
// require_once 'Zend/Mail/Storage/Writable/Interface.php';


/**
 * @category   Zend
 * @package    Zend_Mail
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Mail_Storage_Writable_Maildir extends    Zend_Mail_Storage_Folder_Maildir
                                         implements Zend_Mail_Storage_Writable_Interface
{
    // TODO: init maildir (+ constructor option create if not found)

    /**
     * use quota and size of quota if given
     * @var bool|int
     */
    protected $_quota;

    /**
     * create a new maildir
     *
     * If the given dir is already a valid maildir this will not fail.
     *
     * @param string $dir directory for the new maildir (may already exist)
     * @return null
     * @throws Zend_Mail_Storage_Exception
     */
    public static function initMaildir($dir)
    {
        if (file_exists($dir)) {
            if (!is_dir($dir)) {
                /**
                 * @see Zend_Mail_Storage_Exception
                 */
                // require_once 'Zend/Mail/Storage/Exception.php';
                throw new Zend_Mail_Storage_Exception('maildir must be a directory if already exists');
            }
        } else {
            if (!mkdir($dir)) {
                /**
                 * @see Zend_Mail_Storage_Exception
                 */
                // require_once 'Zend/Mail/Storage/Exception.php';
                $dir = dirname($dir);
                if (!file_exists($dir)) {
                    throw new Zend_Mail_Storage_Exception("parent $dir not found");
                } else if (!is_dir($dir)) {
                    throw new Zend_Mail_Storage_Exception("parent $dir not a directory");
                } else {
                    throw new Zend_Mail_Storage_Exception('cannot create maildir');
                }
            }
        }

        foreach (array('cur', 'tmp', 'new') as $subdir) {
            if (!@mkdir($dir . DIRECTORY_SEPARATOR . $subdir)) {
                // ignore if dir exists (i.e. was already valid maildir or two processes try to create one)
                if (!file_exists($dir . DIRECTORY_SEPARATOR . $subdir)) {
                    /**
                     * @see Zend_Mail_Storage_Exception
                     */
                    // require_once 'Zend/Mail/Storage/Exception.php';
                    throw new Zend_Mail_Storage_Exception('could not create subdir ' . $subdir);
                }
            }
        }
    }

    /**
     * Create instance with parameters
     * Additional parameters are (see parent for more):
     *   - create if true a new maildir is create if none exists
     *
     * @param array $params mail reader specific parameters
     * @throws Zend_Mail_Storage_Exception
     */
    public function __construct($params) {
        if (is_array($params)) {
            $params = (object)$params;
        }

        if (!empty($params->create) && isset($params->dirname) && !file_exists($params->dirname . DIRECTORY_SEPARATOR . 'cur')) {
            self::initMaildir($params->dirname);
        }

        parent::__construct($params);
    }

    /**
     * create a new folder
     *
     * This method also creates parent folders if necessary. Some mail storages may restrict, which folder
     * may be used as parent or which chars may be used in the folder name
     *
     * @param   string                          $name         global name of folder, local name if $parentFolder is set
     * @param   string|Zend_Mail_Storage_Folder $parentFolder parent folder for new folder, else root folder is parent
     * @return  string only used internally (new created maildir)
     * @throws  Zend_Mail_Storage_Exception
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
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception('folder already exists');
        }

        if (strpos($folder, $this->_delim . $this->_delim) !== false) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception('invalid name - folder parts may not be empty');
        }

        if (strpos($folder, 'INBOX' . $this->_delim) === 0) {
            $folder = substr($folder, 6);
        }

        $fulldir = $this->_rootdir . '.' . $folder;

        // check if we got tricked and would create a dir outside of the rootdir or not as direct child
        if (strpos($folder, DIRECTORY_SEPARATOR) !== false || strpos($folder, '/') !== false
            || dirname($fulldir) . DIRECTORY_SEPARATOR != $this->_rootdir) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
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
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
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
     * @param   string|Zend_Mail_Storage_Folder $name      name or instance of folder
     * @return  null
     * @throws  Zend_Mail_Storage_Exception
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
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception('delete children first');
        }

        if ($name == 'INBOX' || $name == DIRECTORY_SEPARATOR || $name == '/') {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception('wont delete INBOX');
        }

        if ($name == $this->getCurrentFolder()) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception('wont delete selected folder');
        }

        foreach (array('tmp', 'new', 'cur', '.') as $subdir) {
            $dir = $this->_rootdir . '.' . $name . DIRECTORY_SEPARATOR . $subdir;
            if (!file_exists($dir)) {
                continue;
            }
            $dh = opendir($dir);
            if (!$dh) {
                /**
                 * @see Zend_Mail_Storage_Exception
                 */
                // require_once 'Zend/Mail/Storage/Exception.php';
                throw new Zend_Mail_Storage_Exception("error opening $subdir");
            }
            while (($entry = readdir($dh)) !== false) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                if (!unlink($dir . DIRECTORY_SEPARATOR . $entry)) {
                    /**
                     * @see Zend_Mail_Storage_Exception
                     */
                    // require_once 'Zend/Mail/Storage/Exception.php';
                    throw new Zend_Mail_Storage_Exception("error cleaning $subdir");
                }
            }
            closedir($dh);
            if ($subdir !== '.') {
                if (!rmdir($dir)) {
                    /**
                     * @see Zend_Mail_Storage_Exception
                     */
                    // require_once 'Zend/Mail/Storage/Exception.php';
                    throw new Zend_Mail_Storage_Exception("error removing $subdir");
                }
            }
        }

        if (!rmdir($this->_rootdir . '.' . $name)) {
            // at least we should try to make it a valid maildir again
            mkdir($this->_rootdir . '.' . $name . DIRECTORY_SEPARATOR . 'cur');
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
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
     * @param   string|Zend_Mail_Storage_Folder $oldName name or instance of folder
     * @param   string                          $newName new global name of folder
     * @return  null
     * @throws  Zend_Mail_Storage_Exception
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
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception('new folder cannot be a child of old folder');
        }

        // check if folder exists and has no children
        $folder = $this->getFolders($oldName);

        if ($oldName == 'INBOX' || $oldName == DIRECTORY_SEPARATOR || $oldName == '/') {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception('wont rename INBOX');
        }

        if ($oldName == $this->getCurrentFolder()) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
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
                /**
                 * @see Zend_Mail_Storage_Exception
                 */
                // require_once 'Zend/Mail/Storage/Exception.php';
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
     * @param   string $folder name of current folder without leading .
     * @return  array array('dirname' => dir of maildir folder, 'uniq' => unique id, 'filename' => name of create file
     *                     'handle'  => file opened for writing)
     * @throws  Zend_Mail_Storage_Exception
     */
    protected function _createTmpFile($folder = 'INBOX')
    {
        if ($folder == 'INBOX') {
            $tmpdir = $this->_rootdir . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
        } else {
            $tmpdir = $this->_rootdir . '.' . $folder . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
        }
        if (!file_exists($tmpdir)) {
            if (!mkdir($tmpdir)) {
                /**
                 * @see Zend_Mail_Storage_Exception
                 */
                // require_once 'Zend/Mail/Storage/Exception.php';
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
                    /**
                     * @see Zend_Mail_Storage_Exception
                     */
                    // require_once 'Zend/Mail/Storage/Exception.php';
                    throw new Zend_Mail_Storage_Exception('could not open temp file');
                }
                break;
            }
            sleep(1);
        }

        if (!$fh) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception("tried $max_tries unique ids for a temp file, but all were taken"
                                                . ' - giving up');
        }

        return array('dirname' => $this->_rootdir . '.' . $folder, 'uniq' => $uniq, 'filename' => $tmpdir . $uniq,
                     'handle' => $fh);
    }

    /**
     * create an info string for filenames with given flags
     *
     * @param   array $flags wanted flags, with the reference you'll get the set flags with correct key (= char for flag)
     * @return  string info string for version 2 filenames including the leading colon
     * @throws  Zend_Mail_Storage_Exception
     */
    protected function _getInfoString(&$flags)
    {
        // accessing keys is easier, faster and it removes duplicated flags
        $wanted_flags = array_flip($flags);
        if (isset($wanted_flags[Zend_Mail_Storage::FLAG_RECENT])) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
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
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception('unknown flag(s): ' . $wanted_flags);
        }

        return $info;
    }

    /**
     * append a new message to mail storage
     *
     * @param   string|stream                              $message message as string or stream resource
     * @param   null|string|Zend_Mail_Storage_Folder       $folder  folder for new message, else current folder is taken
     * @param   null|array                                 $flags   set flags for new message, else a default set is used
     * @param   bool                                       $recent  handle this mail as if recent flag has been set,
     *                                                              should only be used in delivery
     * @throws  Zend_Mail_Storage_Exception
     */
     // not yet * @param string|Zend_Mail_Message|Zend_Mime_Message $message message as string or instance of message class

    public function appendMessage($message, $folder = null, $flags = null, $recent = false)
    {
        if ($this->_quota && $this->checkQuota()) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception('storage is over quota!');
        }

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
        if (is_resource($message) && get_resource_type($message) == 'stream') {
            stream_copy_to_stream($message, $temp_file['handle']);
        } else {
            fputs($temp_file['handle'], $message);
        }
        fclose($temp_file['handle']);

        // we're adding the size to the filename for maildir++
        $size = filesize($temp_file['filename']);
        if ($size !== false) {
            $info = ',S=' . $size . $info;
        }
        $new_filename = $temp_file['dirname'] . DIRECTORY_SEPARATOR;
        $new_filename .= $recent ? 'new' : 'cur';
        $new_filename .= DIRECTORY_SEPARATOR . $temp_file['uniq'] . $info;

        // we're throwing any exception after removing our temp file and saving it to this variable instead
        $exception = null;

        if (!link($temp_file['filename'], $new_filename)) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            $exception = new Zend_Mail_Storage_Exception('cannot link message file to final dir');
        }
        @unlink($temp_file['filename']);

        if ($exception) {
            throw $exception;
        }

        $this->_files[] = array('uniq'     => $temp_file['uniq'],
                                'flags'    => $flags,
                                'filename' => $new_filename);
        if ($this->_quota) {
            $this->_addQuotaEntry((int)$size, 1);
        }
    }

    /**
     * copy an existing message
     *
     * @param   int                             $id     number of message
     * @param   string|Zend_Mail_Storage_Folder $folder name or instance of targer folder
     * @return  null
     * @throws  Zend_Mail_Storage_Exception
     */
    public function copyMessage($id, $folder)
    {
        if ($this->_quota && $this->checkQuota()) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception('storage is over quota!');
        }

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
        $size = filesize($old_file);
        if ($size !== false) {
            $info = ',S=' . $size . $info;
        }

        $new_file = $temp_file['dirname'] . DIRECTORY_SEPARATOR . 'cur' . DIRECTORY_SEPARATOR . $temp_file['uniq'] . $info;

        // we're throwing any exception after removing our temp file and saving it to this variable instead
        $exception = null;

        if (!copy($old_file, $temp_file['filename'])) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            $exception = new Zend_Mail_Storage_Exception('cannot copy message file');
        } else if (!link($temp_file['filename'], $new_file)) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
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

        if ($this->_quota) {
            $this->_addQuotaEntry((int)$size, 1);
        }
    }

    /**
     * move an existing message
     *
     * @param  int                             $id     number of message
     * @param  string|Zend_Mail_Storage_Folder $folder name or instance of targer folder
     * @return null
     * @throws Zend_Mail_Storage_Exception
     */
    public function moveMessage($id, $folder) {
        if (!($folder instanceof Zend_Mail_Storage_Folder)) {
            $folder = $this->getFolders($folder);
        }

        if ($folder->getGlobalName() == $this->_currentFolder
            || ($this->_currentFolder == 'INBOX' && $folder->getGlobalName() == '/')) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception('target is current folder');
        }

        $filedata = $this->_getFileData($id);
        $old_file = $filedata['filename'];
        $flags = $filedata['flags'];

        // moved message can't be recent
        while (($key = array_search(Zend_Mail_Storage::FLAG_RECENT, $flags)) !== false) {
            unset($flags[$key]);
        }
        $info = $this->_getInfoString($flags);

        // reserving a new name
        $temp_file = $this->_createTmpFile($folder->getGlobalName());
        fclose($temp_file['handle']);

        // we're adding the size to the filename for maildir++
        $size = filesize($old_file);
        if ($size !== false) {
            $info = ',S=' . $size . $info;
        }

        $new_file = $temp_file['dirname'] . DIRECTORY_SEPARATOR . 'cur' . DIRECTORY_SEPARATOR . $temp_file['uniq'] . $info;

        // we're throwing any exception after removing our temp file and saving it to this variable instead
        $exception = null;

        if (!rename($old_file, $new_file)) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            $exception = new Zend_Mail_Storage_Exception('cannot move message file');
        }
        @unlink($temp_file['filename']);

        if ($exception) {
            throw $exception;
        }

        unset($this->_files[$id - 1]);
        // remove the gap
        $this->_files = array_values($this->_files);
    }


    /**
     * set flags for message
     *
     * NOTE: this method can't set the recent flag.
     *
     * @param   int   $id    number of message
     * @param   array $flags new flags for message
     * @throws  Zend_Mail_Storage_Exception
     */
    public function setFlags($id, $flags)
    {
        $info = $this->_getInfoString($flags);
        $filedata = $this->_getFileData($id);

        // NOTE: double dirname to make sure we always move to cur. if recent flag has been set (message is in new) it will be moved to cur.
        $new_filename = dirname(dirname($filedata['filename'])) . DIRECTORY_SEPARATOR . 'cur' . DIRECTORY_SEPARATOR . "$filedata[uniq]$info";

        if (!@rename($filedata['filename'], $new_filename)) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception('cannot rename file');
        }

        $filedata['flags']    = $flags;
        $filedata['filename'] = $new_filename;

        $this->_files[$id - 1] = $filedata;
    }


    /**
     * stub for not supported message deletion
     *
     * @return  null
     * @throws  Zend_Mail_Storage_Exception
     */
    public function removeMessage($id)
    {
        $filename = $this->_getFileData($id, 'filename');

        if ($this->_quota) {
            $size = filesize($filename);
        }

        if (!@unlink($filename)) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception('cannot remove message');
        }
        unset($this->_files[$id - 1]);
        // remove the gap
        $this->_files = array_values($this->_files);
        if ($this->_quota) {
            $this->_addQuotaEntry(0 - (int)$size, -1);
        }
    }

    /**
     * enable/disable quota and set a quota value if wanted or needed
     *
     * You can enable/disable quota with true/false. If you don't have
     * a MDA or want to enforce a quota value you can also set this value
     * here. Use array('size' => SIZE_QUOTA, 'count' => MAX_MESSAGE) do
     * define your quota. Order of these fields does matter!
     *
     * @param bool|array $value new quota value
     * @return null
     */
    public function setQuota($value) {
        $this->_quota = $value;
    }

    /**
     * get currently set quota
     *
     * @see Zend_Mail_Storage_Writable_Maildir::setQuota()
     *
     * @return bool|array
     */
    public function getQuota($fromStorage = false) {
        if ($fromStorage) {
            $fh = @fopen($this->_rootdir . 'maildirsize', 'r');
            if (!$fh) {
                /**
                 * @see Zend_Mail_Storage_Exception
                 */
                // require_once 'Zend/Mail/Storage/Exception.php';
                throw new Zend_Mail_Storage_Exception('cannot open maildirsize');
            }
            $definition = fgets($fh);
            fclose($fh);
            $definition = explode(',', trim($definition));
            $quota = array();
            foreach ($definition as $member) {
                $key = $member[strlen($member) - 1];
                if ($key == 'S' || $key == 'C') {
                    $key = $key == 'C' ? 'count' : 'size';
                }
                $quota[$key] = substr($member, 0, -1);
            }
            return $quota;
        }

        return $this->_quota;
    }

    /**
     * @see http://www.inter7.com/courierimap/README.maildirquota.html "Calculating maildirsize"
     */
    protected function _calculateMaildirsize() {
        $timestamps = array();
        $messages = 0;
        $total_size = 0;

        if (is_array($this->_quota)) {
            $quota = $this->_quota;
        } else {
            try {
                $quota = $this->getQuota(true);
            } catch (Zend_Mail_Storage_Exception $e) {
                throw new Zend_Mail_Storage_Exception('no quota definition found', 0, $e);
            }
        }

        $folders = new RecursiveIteratorIterator($this->getFolders(), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($folders as $folder) {
            $subdir = $folder->getGlobalName();
            if ($subdir == 'INBOX') {
                $subdir = '';
            } else {
                $subdir = '.' . $subdir;
            }
            if ($subdir == 'Trash') {
                continue;
            }

            foreach (array('cur', 'new') as $subsubdir) {
                $dirname = $this->_rootdir . $subdir . DIRECTORY_SEPARATOR . $subsubdir . DIRECTORY_SEPARATOR;
                if (!file_exists($dirname)) {
                    continue;
                }
                // NOTE: we are using mtime instead of "the latest timestamp". The latest would be atime
                // and as we are accessing the directory it would make the whole calculation useless.
                $timestamps[$dirname] = filemtime($dirname);

                $dh = opendir($dirname);
                // NOTE: Should have been checked in constructor. Not throwing an exception here, quotas will
                // therefore not be fully enforeced, but next request will fail anyway, if problem persists.
                if (!$dh) {
                    continue;
                }


                while (($entry = readdir()) !== false) {
                    if ($entry[0] == '.' || !is_file($dirname . $entry)) {
                        continue;
                    }

                    if (strpos($entry, ',S=')) {
                        strtok($entry, '=');
                        $filesize = strtok(':');
                        if (is_numeric($filesize)) {
                            $total_size += $filesize;
                            ++$messages;
                            continue;
                        }
                    }
                    $size = filesize($dirname . $entry);
                    if ($size === false) {
                        // ignore, as we assume file got removed
                        continue;
                    }
                    $total_size += $size;
                    ++$messages;
                }
            }
        }

        $tmp = $this->_createTmpFile();
        $fh = $tmp['handle'];
        $definition = array();
        foreach ($quota as $type => $value) {
            if ($type == 'size' || $type == 'count') {
                $type = $type == 'count' ? 'C' : 'S';
            }
            $definition[] = $value . $type;
        }
        $definition = implode(',', $definition);
        fputs($fh, "$definition\n");
        fputs($fh, "$total_size $messages\n");
        fclose($fh);
        rename($tmp['filename'], $this->_rootdir . 'maildirsize');
        foreach ($timestamps as $dir => $timestamp) {
            if ($timestamp < filemtime($dir)) {
                unlink($this->_rootdir . 'maildirsize');
                break;
            }
        }

        return array('size' => $total_size, 'count' => $messages, 'quota' => $quota);
    }

    /**
     * @see http://www.inter7.com/courierimap/README.maildirquota.html "Calculating the quota for a Maildir++"
     */
    protected function _calculateQuota($forceRecalc = false) {
        $fh = null;
        $total_size = 0;
        $messages   = 0;
        $maildirsize = '';
        if (!$forceRecalc && file_exists($this->_rootdir . 'maildirsize') && filesize($this->_rootdir . 'maildirsize') < 5120) {
            $fh = fopen($this->_rootdir . 'maildirsize', 'r');
        }
        if ($fh) {
            $maildirsize = fread($fh, 5120);
            if (strlen($maildirsize) >= 5120) {
                fclose($fh);
                $fh = null;
                $maildirsize = '';
            }
        }
        if (!$fh) {
            $result = $this->_calculateMaildirsize();
            $total_size = $result['size'];
            $messages   = $result['count'];
            $quota      = $result['quota'];
        } else {
            $maildirsize = explode("\n", $maildirsize);
            if (is_array($this->_quota)) {
                $quota = $this->_quota;
            } else {
                $definition = explode(',', $maildirsize[0]);
                $quota = array();
                foreach ($definition as $member) {
                    $key = $member[strlen($member) - 1];
                    if ($key == 'S' || $key == 'C') {
                        $key = $key == 'C' ? 'count' : 'size';
                    }
                    $quota[$key] = substr($member, 0, -1);
                }
            }
            unset($maildirsize[0]);
            foreach ($maildirsize as $line) {
                list($size, $count) = explode(' ', trim($line));
                $total_size += $size;
                $messages   += $count;
            }
        }

        $over_quota = false;
        $over_quota = $over_quota || (isset($quota['size'])  && $total_size > $quota['size']);
        $over_quota = $over_quota || (isset($quota['count']) && $messages   > $quota['count']);
        // NOTE: $maildirsize equals false if it wasn't set (AKA we recalculated) or it's only
        // one line, because $maildirsize[0] gets unsetted.
        // Also we're using local time to calculate the 15 minute offset. Touching a file just for known the
        // local time of the file storage isn't worth the hassle.
        if ($over_quota && ($maildirsize || filemtime($this->_rootdir . 'maildirsize') > time() - 900)) {
            $result = $this->_calculateMaildirsize();
            $total_size = $result['size'];
            $messages   = $result['count'];
            $quota      = $result['quota'];
            $over_quota = false;
            $over_quota = $over_quota || (isset($quota['size'])  && $total_size > $quota['size']);
            $over_quota = $over_quota || (isset($quota['count']) && $messages   > $quota['count']);
        }

        if ($fh) {
            // TODO is there a safe way to keep the handle open for writing?
            fclose($fh);
        }

        return array('size' => $total_size, 'count' => $messages, 'quota' => $quota, 'over_quota' => $over_quota);
    }

    protected function _addQuotaEntry($size, $count = 1) {
        if (!file_exists($this->_rootdir . 'maildirsize')) {
            // TODO: should get file handler from _calculateQuota
        }
        $size = (int)$size;
        $count = (int)$count;
        file_put_contents($this->_rootdir . 'maildirsize', "$size $count\n", FILE_APPEND);
    }

    /**
     * check if storage is currently over quota
     *
     * @param bool $detailedResponse return known data of quota and current size and message count @see _calculateQuota()
     * @return bool|array over quota state or detailed response
     */
    public function checkQuota($detailedResponse = false, $forceRecalc = false) {
        $result = $this->_calculateQuota($forceRecalc);
        return $detailedResponse ? $result : $result['over_quota'];
    }
}
