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
 * @package    Zend_Feed_Pubsubhubbub
 * @subpackage Entity
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @category   Zend
 * @package    Zend_Feed_Pubsubhubbub
 * @subpackage Entity
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Feed_Pubsubhubbub_Model_SubscriptionInterface
{
    
    /**
     * Save subscription to RDMBS
     *
     * @param array $data The key must be stored here as a $data['id'] entry
     * @return bool
     */
    public function setSubscription(array $data);
    
    /**
     * Get subscription by ID/key
     * 
     * @param  string $key 
     * @return array
     */
    public function getSubscription($key);

    /**
     * Determine if a subscription matching the key exists
     * 
     * @param  string $key 
     * @return bool
     */
    public function hasSubscription($key);
    
    /**
     * Delete a subscription
     *
     * @param string $key
     * @return bool
     */
    public function deleteSubscription($key);
    
}
