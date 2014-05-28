<?php
namespace Piwik\Plugins\UsersManager;

/**
 * Interface PasswordValidator
 * @package Piwik\Plugins\UsersManager
 */
interface PasswordValidator
{
    /**
     * @param string $password
     * @return bool
     */
    public function validate($password);

    /**
     * @return string
     */
    public function getErrorMessage();
} 
