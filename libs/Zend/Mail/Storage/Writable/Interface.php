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
 * @version    $Id: Interface.php 23775 2011-03-01 17:25:24Z ralph $
 */


/**
 * @category   Zend
 * @package    Zend_Mail
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

interface Zend_Mail_Storage_Writable_Interface
{
    /**
     * create a new folder
     *
     * This method also creates parent folders if necessary. Some mail storages may restrict, which folder
     * may be used as parent or which chars may be used in the folder name
     *
     * @param string                          $name         global name of folder, local name if $parentFolder is set
     * @param string|Zend_Mail_Storage_Folder $parentFolder parent folder for new folder, else root folder is parent
     * @return null
     * @throws Zend_Mail_Storage_Exception
     */
    public function createFolder($name, $parentFolder = null);

    /**
     * remove a folder
     *
     * @param string|Zend_Mail_Storage_Folder $name      name or instance of folder
     * @return null
     * @throws Zend_Mail_Storage_Exception
     */
    public function removeFolder($name);

    /**
     * rename and/or move folder
     *
     * The new name has the same restrictions as in createFolder()
     *
     * @param string|Zend_Mail_Storage_Folder $oldName name or instance of folder
     * @param string                          $newName new global name of folder
     * @return null
     * @throws Zend_Mail_Storage_Exception
     */
    public function renameFolder($oldName, $newName);

    /**
     * append a new message to mail storage
     *
     * @param  string|Zend_Mail_Message|Zend_Mime_Message $message message as string or instance of message class
     * @param  null|string|Zend_Mail_Storage_Folder       $folder  folder for new message, else current folder is taken
     * @param  null|array                                 $flags   set flags for new message, else a default set is used
     * @throws Zend_Mail_Storage_Exception
     */
    public function appendMessage($message, $folder = null, $flags = null);

    /**
     * copy an existing message
     *
     * @param  int                             $id     number of message
     * @param  string|Zend_Mail_Storage_Folder $folder name or instance of targer folder
     * @return null
     * @throws Zend_Mail_Storage_Exception
     */
    public function copyMessage($id, $folder);

    /**
     * move an existing message
     *
     * @param  int                             $id     number of message
     * @param  string|Zend_Mail_Storage_Folder $folder name or instance of targer folder
     * @return null
     * @throws Zend_Mail_Storage_Exception
     */
    public function moveMessage($id, $folder);

    /**
     * set flags for message
     *
     * NOTE: this method can't set the recent flag.
     *
     * @param  int   $id    number of message
     * @param  array $flags new flags for message
     * @throws Zend_Mail_Storage_Exception
     */
    public function setFlags($id, $flags);
}
