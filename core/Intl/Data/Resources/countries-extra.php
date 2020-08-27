<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Country codes database.
 *
 * The mapping of countries to continents is from MaxMind with the exception
 * of Central America.  MaxMind groups Central American countries with
 * North America.  Piwik previously grouped Central American countries with
 * South America.  Given this conflict and the fact that most of Central
 * America lies on its own continental plate (i.e., the Caribbean Plate), we
 * currently use a separate continent code (amc).
 */
return array(
    // unknown
    'xx'  => 'unk',

    // exceptionally reserved
    'ac'  => 'afr', // .ac TLD
    'cp'  => 'amc',
    'dg'  => 'asi',
    'ea'  => 'afr',
    'eu'  => 'eur', // .eu TLD
    'fx'  => 'eur',
    'ic'  => 'afr',
    'su'  => 'eur', // .su TLD
    'ta'  => 'afr',
    'uk'  => 'eur', // .uk TLD

    // transitionally reserved
    'an'  => 'amc', // former Netherlands Antilles
    'bu'  => 'asi',
    'cs'  => 'eur', // former Serbia and Montenegro
    'nt'  => 'asi',
    'sf'  => 'eur',
    'tp'  => 'oce', // .tp TLD
    'yu'  => 'eur', // .yu TLD
    'zr'  => 'afr',

    // MaxMind GeoIP specific
    'a1'  => 'unk',
    'a2'  => 'unk',
    'ap'  => 'asi',
    'o1'  => 'unk',

    // Catalonia (Spain)
    'cat' => 'eur',
);
