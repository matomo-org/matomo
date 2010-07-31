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

/** @see Zend_Feed_Pubsubhubbub_Model_ModelAbstract */
// require_once 'Zend/Feed/Pubsubhubbub/Model/ModelAbstract.php';

/** @see Zend_Feed_Pubsubhubbub_Model_SubscriptionInterface */
// require_once 'Zend/Feed/Pubsubhubbub/Model/SubscriptionInterface.php';

/** @see Zend_Date */
// require_once 'Zend/Date.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Pubsubhubbub
 * @subpackage Entity
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Pubsubhubbub_Model_Subscription
    extends Zend_Feed_Pubsubhubbub_Model_ModelAbstract
    implements Zend_Feed_Pubsubhubbub_Model_SubscriptionInterface
{

    /**
     * Save subscription to RDMBS
     *
     * @param array $data
     * @return bool
     */
    public function setSubscription(array $data)
    {
        if (!isset($data['id'])) {
            // require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception(
                'ID must be set before attempting a save'
            );
        }
        $result = $this->_db->find($data['id']);
        if (count($result)) {
            $data['created_time'] = $result->current()->created_time;
            $now = new Zend_Date;
            if (isset($data['lease_seconds'])) {
                $data['expiration_time'] = $now->add($data['lease_seconds'], Zend_Date::SECOND)
                ->get('yyyy-MM-dd HH:mm:ss');
            }
            $this->_db->update(
                $data,
                $this->_db->getAdapter()->quoteInto('id = ?', $data['id'])
            );
            return false;
        }

        $this->_db->insert($data);
        return true;
    }

    /**
     * Get subscription by ID/key
     *
     * @param  string $key
     * @return array
     */
    public function getSubscription($key)
    {
        if (empty($key) || !is_string($key)) {
            // require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "key"'
                .' of "' . $key . '" must be a non-empty string');
        }
        $result = $this->_db->find($key);
        if (count($result)) {
            return $result->current()->toArray();
        }
        return false;
    }

    /**
     * Determine if a subscription matching the key exists
     *
     * @param  string $key
     * @return bool
     */
    public function hasSubscription($key)
    {
        if (empty($key) || !is_string($key)) {
            // require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "key"'
                .' of "' . $key . '" must be a non-empty string');
        }
        $result = $this->_db->find($key);
        if (count($result)) {
            return true;
        }
        return false;
    }

    /**
     * Delete a subscription
     *
     * @param string $key
     * @return bool
     */
    public function deleteSubscription($key)
    {
        $result = $this->_db->find($key);
        if (count($result)) {
            $this->_db->delete(
                $this->_db->getAdapter()->quoteInto('id = ?', $key)
            );
            return true;
        }
        return false;
    }

}
