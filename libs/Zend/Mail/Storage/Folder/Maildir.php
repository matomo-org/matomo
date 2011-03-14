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
 * @see Zend_Mail_Storage_Folder
 */
// require_once 'Zend/Mail/Storage/Folder.php';

/**
 * @see Zend_Mail_Storage_Folder_Interface
 */
// require_once 'Zend/Mail/Storage/Folder/Interface.php';

/**
 * @see Zend_Mail_Storage_Maildir
 */
// require_once 'Zend/Mail/Storage/Maildir.php';


/**
 * @category   Zend
 * @package    Zend_Mail
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Mail_Storage_Folder_Maildir extends Zend_Mail_Storage_Maildir implements Zend_Mail_Storage_Folder_Interface
{
    /**
     * Zend_Mail_Storage_Folder root folder for folder structure
     * @var Zend_Mail_Storage_Folder
     */
    protected $_rootFolder;

    /**
     * rootdir of folder structure
     * @var string
     */
    protected $_rootdir;

    /**
     * name of current folder
     * @var string
     */
    protected $_currentFolder;

    /**
     * delim char for subfolders
     * @var string
     */
    protected $_delim;

    /**
     * Create instance with parameters
     * Supported parameters are:
     *   - dirname rootdir of maildir structure
     *   - delim   delim char for folder structur, default is '.'
     *   - folder intial selected folder, default is 'INBOX'
     *
     * @param array $params mail reader specific parameters
     * @throws Zend_Mail_Storage_Exception
     */
    public function __construct($params)
    {
        if (is_array($params)) {
            $params = (object)$params;
        }

        if (!isset($params->dirname) || !is_dir($params->dirname)) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception('no valid dirname given in params');
        }

        $this->_rootdir = rtrim($params->dirname, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        $this->_delim = isset($params->delim) ? $params->delim : '.';

        $this->_buildFolderTree();
        $this->selectFolder(!empty($params->folder) ? $params->folder : 'INBOX');
        $this->_has['top'] = true;
        $this->_has['flags'] = true;
    }

    /**
     * find all subfolders and mbox files for folder structure
     *
     * Result is save in Zend_Mail_Storage_Folder instances with the root in $this->_rootFolder.
     * $parentFolder and $parentGlobalName are only used internally for recursion.
     *
     * @return null
     * @throws Zend_Mail_Storage_Exception
     */
    protected function _buildFolderTree()
    {
        $this->_rootFolder = new Zend_Mail_Storage_Folder('/', '/', false);
        $this->_rootFolder->INBOX = new Zend_Mail_Storage_Folder('INBOX', 'INBOX', true);

        $dh = @opendir($this->_rootdir);
        if (!$dh) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception("can't read folders in maildir");
        }
        $dirs = array();
        while (($entry = readdir($dh)) !== false) {
            // maildir++ defines folders must start with .
            if ($entry[0] != '.' || $entry == '.' || $entry == '..') {
                continue;
            }
            if ($this->_isMaildir($this->_rootdir . $entry)) {
                $dirs[] = $entry;
            }
        }
        closedir($dh);

        sort($dirs);
        $stack = array(null);
        $folderStack = array(null);
        $parentFolder = $this->_rootFolder;
        $parent = '.';

        foreach ($dirs as $dir) {
            do {
                if (strpos($dir, $parent) === 0) {
                    $local = substr($dir, strlen($parent));
                    if (strpos($local, $this->_delim) !== false) {
                        /**
                         * @see Zend_Mail_Storage_Exception
                         */
                        // require_once 'Zend/Mail/Storage/Exception.php';
                        throw new Zend_Mail_Storage_Exception('error while reading maildir');
                    }
                    array_push($stack, $parent);
                    $parent = $dir . $this->_delim;
                    $folder = new Zend_Mail_Storage_Folder($local, substr($dir, 1), true);
                    $parentFolder->$local = $folder;
                    array_push($folderStack, $parentFolder);
                    $parentFolder = $folder;
                    break;
                } else if ($stack) {
                    $parent = array_pop($stack);
                    $parentFolder = array_pop($folderStack);
                }
            } while ($stack);
            if (!$stack) {
                /**
                 * @see Zend_Mail_Storage_Exception
                 */
                // require_once 'Zend/Mail/Storage/Exception.php';
                throw new Zend_Mail_Storage_Exception('error while reading maildir');
            }
        }
    }

    /**
     * get root folder or given folder
     *
     * @param string $rootFolder get folder structure for given folder, else root
     * @return Zend_Mail_Storage_Folder root or wanted folder
     * @throws Zend_Mail_Storage_Exception
     */
    public function getFolders($rootFolder = null)
    {
        if (!$rootFolder || $rootFolder == 'INBOX') {
            return $this->_rootFolder;
        }

        // rootdir is same as INBOX in maildir
        if (strpos($rootFolder, 'INBOX' . $this->_delim) === 0) {
            $rootFolder = substr($rootFolder, 6);
        }
        $currentFolder = $this->_rootFolder;
        $subname = trim($rootFolder, $this->_delim);
        while ($currentFolder) {
            @list($entry, $subname) = @explode($this->_delim, $subname, 2);
            $currentFolder = $currentFolder->$entry;
            if (!$subname) {
                break;
            }
        }

        if ($currentFolder->getGlobalName() != rtrim($rootFolder, $this->_delim)) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception("folder $rootFolder not found");
        }
        return $currentFolder;
    }

    /**
     * select given folder
     *
     * folder must be selectable!
     *
     * @param Zend_Mail_Storage_Folder|string $globalName global name of folder or instance for subfolder
     * @return null
     * @throws Zend_Mail_Storage_Exception
     */
    public function selectFolder($globalName)
    {
        $this->_currentFolder = (string)$globalName;

        // getting folder from folder tree for validation
        $folder = $this->getFolders($this->_currentFolder);

        try {
            $this->_openMaildir($this->_rootdir . '.' . $folder->getGlobalName());
        } catch(Zend_Mail_Storage_Exception $e) {
            // check what went wrong
            if (!$folder->isSelectable()) {
                /**
                 * @see Zend_Mail_Storage_Exception
                 */
                // require_once 'Zend/Mail/Storage/Exception.php';
                throw new Zend_Mail_Storage_Exception("{$this->_currentFolder} is not selectable", 0, $e);
            }
            // seems like file has vanished; rebuilding folder tree - but it's still an exception
            $this->_buildFolderTree($this->_rootdir);
            /**
             * @see Zend_Mail_Storage_Exception
             */
            // require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception('seems like the maildir has vanished, I\'ve rebuild the ' .
                                                         'folder tree, search for an other folder and try again', 0, $e);
        }
    }

    /**
     * get Zend_Mail_Storage_Folder instance for current folder
     *
     * @return Zend_Mail_Storage_Folder instance of current folder
     * @throws Zend_Mail_Storage_Exception
     */
    public function getCurrentFolder()
    {
        return $this->_currentFolder;
    }
}
