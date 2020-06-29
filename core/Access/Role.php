<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Access;

abstract class Role
{
    abstract public function getName(): string;
    abstract public function getId(): string;
    abstract public function getDescription(): string;

    public function getHelpUrl(): string
    {
        return '';
    }

}
