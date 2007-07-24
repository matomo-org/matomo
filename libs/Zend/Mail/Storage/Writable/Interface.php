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
 * @package    Zend_Mail
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
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
     * @throw Zend_Mail_Storage_Exception
     */
    public function createFolder($name, $parentFolder = null);

    /**
     * remove a folder
     *
     * @param string|Zend_Mail_Storage_Folder $name      name or instance of folder
     * @return null
     * @throw Zend_Mail_Storage_Exception
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
     * @throw Zend_Mail_Storage_Exception
     */
    public function renameFolder($oldName, $newName);

    /**
     * append a new message to mail storage
     *
     * @param string|Zend_Mail_Message|Zend_Mime_Message $message message as string or instance of message class
     * @param null|string|Zend_Mail_Storage_Folder       $folder  folder for new message, else current folder is taken
     * @param null|array                                 $flags   set flags for new message, else a default set is used
     * @throw Zend_Mail_Storage_Exception
     */
    public function appendMessage($message, $folder = null, $flags = null);

    /**
     * copy an existing message
     *
     * @param int                             $id     number of message
     * @param string|Zend_Mail_Storage_Folder $folder name or instance of targer folder
     * @return null
     * @throw Zend_Mail_Storage_Exception
     */
    public function copyMessage($id, $folder);

    /**
     * set flags for message
     *
     * NOTE: this method can't set the recent flag.
     *
     * @param int   $id    number of message
     * @param array $flags new flags for message
     * @throw Zend_Mail_Storage_Exception
     */
    public function setFlags($id, $flags);
}