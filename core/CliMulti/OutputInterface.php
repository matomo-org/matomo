<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CliMulti;


interface OutputInterface
{
    public function getOutputId();

    public function write($content);

    public function isAbnormal(): bool;

    public function exists(): bool;

    public function get();

    public function destroy();
}
