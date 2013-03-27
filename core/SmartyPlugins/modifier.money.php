<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package SmartyPlugins
 */

/**
 * Prints money, given the currency symbol.
 *
 * @param $amount
 * @throws Exception
 * @return string The amount with the currency symbol
 */
function smarty_modifier_money($amount)
{
    if (func_num_args() != 2) {
        throw new Exception('the smarty modifier money expects one parameter: the idSite.');
    }
    $idSite = func_get_args();
    $idSite = $idSite[1];
    return Piwik::getPrettyMoney($amount, $idSite);
}
