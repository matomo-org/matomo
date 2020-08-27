<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

/**
 * Providers names
 */
if (!isset($GLOBALS['Piwik_ProviderNames'])) {
    $GLOBALS['Piwik_ProviderNames'] = array(
        // France
        "wanadoo"                 => "Orange",
        "proxad"                  => "Free",
        "bbox"                    => "Bouygues Telecom",
        "bouyguestelecom"         => "Bouygues Telecom",
        "coucou-networks"         => "Free Mobile",
        "sfr"                     => "SFR", //Acronym, keep in uppercase
        "univ-metz"               => "Université de Lorraine",
        "unilim"                  => "Université de Limoges",
        "univ-paris5"             => "Université Paris Descartes",

        // US
        "rr"                      => "Time Warner Cable Internet", // Not sure
        "uu"                      => "Verizon",

        // UK
        'zen.net'                 => 'Zen Internet',

        // DE
        't-ipconnect'             => 'Deutsche Telekom',
        't-dialin'                => 'Deutsche Telekom',
        'dtag'                    => 'Deutsche Telekom',
        't-ipnet'                 => 'Deutsche Telekom',
        'd1-online'               => 'Deutsche Telekom (Mobile)',
        'superkabel'              => 'Kabel Deutschland',
        'unitymediagroup'         => 'Unitymedia',
        'arcor-ip'                => 'Vodafone',
        'kabel-badenwuerttemberg' => 'Kabel BW',
        'alicedsl'                => 'O2',
        'komdsl'                  => 'komDSL - Thüga MeteringService',
        'mediaways'               => 'mediaWays - Telefonica',
        'citeq'                   => 'Citeq - Stadt Münster',
    );
}
