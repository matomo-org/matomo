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
 * @package    Zend_Date
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: DateObject.php 2511 2006-12-26 22:54:37Z bkarwin $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Additional data for sunset/sunrise calculations
 * Holds the geographical data for all capital cities and many others worldwide
 * Original data from http://www.fallingrain.com/world/
 *
 * @category   Zend
 * @package    Zend_Date
 * @subpackage Zend_Date_Cities
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Date_Cities {

    public static $Cities = array(
        'Abidjan'     => array('latitude' =>    5.3411111, 'longitude' =>   -4.0280556),
        'Abu Dhabi'   => array('latitude' =>   24.4666667, 'longitude' =>   54.3666667),
        'Abuja'       => array('latitude' =>    9.1758333, 'longitude' =>    7.1808333),
        'Accra'       => array('latitude' =>    5.55,      'longitude' =>   -0.2166667),
        'Adamstown'   => array('latitude' =>  -25.0666667, 'longitude' => -130.0833333),
        'Addis Ababa' => array('latitude' =>    9.0333333, 'longitude' =>   38.7),
        'Adelaide'    => array('latitude' =>  -34.9333333, 'longitude' =>  138.6),
        'Algiers'     => array('latitude' =>   36.7630556, 'longitude' =>    3.0505556),
        'Alofi'       => array('latitude' =>  -19.0166667, 'longitude' => -169.9166667),
        'Amman'       => array('latitude' =>   31.95,      'longitude' =>   35.9333333),
        'Amsterdam'   => array('latitude' =>   52.35,      'longitude' =>    4.9166667),
        'Andorra la Vella' => array('latitude' => 42.5,    'longitude' =>    1.5166667),
        'Ankara'      => array('latitude' =>   39.9272222, 'longitude' =>   32.8644444),
        'Antananarivo' => array('latitude' => -18.9166667, 'longitude' =>   47.5166667),
        'Apia'        => array('latitude' =>  -13.8333333, 'longitude' => -171.7333333),
        'Ashgabat'    => array('latitude' =>   37.95,      'longitude' =>   58.3833333),
        'Asmara'      => array('latitude' =>   15.3333333, 'longitude' =>   38.9333333),
        'Astana'      => array('latitude' =>   51.1811111, 'longitude' =>   71.4277778),
        'Asunción'    => array('latitude' =>  -25.2666667, 'longitude' =>  -57.6666667),
        'Athens'      => array('latitude' =>   37.9833333, 'longitude' =>   23.7333333),
        'Auckland'    => array('latitude' =>  -36.8666667, 'longitude' =>  174.7666667),
        'Avarua'      => array('latitude' =>  -21.2,       'longitude' => -159.7666667),
        'Baghdad'     => array('latitude' =>   33.3386111, 'longitude' =>   44.3938889),
        'Baku'        => array('latitude' =>   40.3952778, 'longitude' =>   49.8822222),
        'Bamako'      => array('latitude' =>   12.65,      'longitude' =>   -8),
        'Bandar Seri Begawan'      => array('latitude' => 4.8833333, 'longitude' => 114.9333333),
        'Bankok'      => array('latitude' =>   13.5833333, 'longitude' =>  100.2166667),
        'Bangui'      => array('latitude' =>    4.3666667, 'longitude' =>   18.5833333),
        'Banjul'      => array('latitude' =>   13.4530556, 'longitude' =>  -16.5775),
        'Basel'       => array('latitude' =>   47.5666667, 'longitude' =>    7.6),
        'Basseterre'  => array('latitude' =>   17.3,       'longitude' =>  -62.7166667),
        'Beijing'     => array('latitude' =>   39.9288889, 'longitude' =>  116.3883333),
        'Beirut'      => array('latitude' =>   33.8719444, 'longitude' =>   35.5097222),
        'Belgrade'    => array('latitude' =>   44.8186111, 'longitude' =>   20.4680556),
        'Belmopan'    => array('latitude' =>   17.25,      'longitude' =>  -88.7666667),
        'Berlin'      => array('latitude' =>   52.5166667, 'longitude' =>   13.4),
        'Bern'        => array('latitude' =>   46.9166667, 'longitude' =>    7.4666667),
        'Bishkek'     => array('latitude' =>   42.8730556, 'longitude' =>   74.6002778),
        'Bissau'      => array('latitude' =>   11.85,      'longitude' =>  -15.5833333),
        'Bloemfontein' => array('latitude' => -29.1333333, 'longitude' =>   26.2),
        'Bogotá'      => array('latitude' =>    4.6,       'longitude' =>  -74.0833333),
        'Brasilia'    => array('latitude' =>  -15.7833333, 'longitude' =>  -47.9166667),
        'Bratislava'  => array('latitude' =>   48.15,      'longitude' =>   17.1166667),
        'Brazzaville' => array('latitude' =>   -4.2591667, 'longitude' =>   15.2847222),
        'Bridgetown'  => array('latitude' =>   13.1,       'longitude' =>  -59.6166667),
        'Brisbane'    => array('latitude' =>  -27.5,       'longitude' =>  153.0166667),
        'Brussels'    => array('latitude' =>  50.8333333,  'longitude' =>    4.3333333),
        'Bucharest'   => array('latitude' =>  44.4333333,  'longitude' =>   26.1),
        'Budapest'    => array('latitude' =>  47.5,        'longitude' =>   19.0833333),
        'Buenos Aires' => array('latitude' => -34.5875,    'longitude' =>  -58.6725),
        'Bujumbura'   => array('latitude' =>   -3.3761111, 'longitude' =>   29.36),
        'Cairo'       => array('latitude' =>   30.05,      'longitude' =>   31.25),
        'Calgary'     => array('latitude' =>   51.0833333, 'longitude' => -114.0833333),
        'Canberra'    => array('latitude' =>  -35.2833333, 'longitude' =>  149.2166667),
        'Cape Town'   => array('latitude' =>  -33.9166667, 'longitude' =>   18.4166667),
        'Caracas'     => array('latitude' =>   10.5,       'longitude' =>  -66.9166667),
        'Castries'    => array('latitude' =>   14,         'longitude' =>  -61),
        'Charlotte Amalie' => array('latitude' => 18.34389, 'longitude' => -64.93111),
        'Chicago'     => array('latitude' =>   41.85,      'longitude' =>  -87.65),
        'Chisinau'    => array('latitude' =>   47.055556,  'longitude' =>   28.8575),
        'Cockburn Town' => array('latitude' => 21.4666667, 'longitude' =>  -71.1333333),
        'Colombo'     => array('latitude' =>    6.9319444, 'longitude' =>   79.8477778),
        'Conakry'     => array('latitude' =>    9.5091667, 'longitude' =>  -13.7122222),
        'Copenhagen'  => array('latitude' =>   55.6666667, 'longitude' =>   12.5833333),
        'Cotonou'     => array('latitude' =>    6.35,      'longitude' =>    2.4333333),
        'Dakar'       => array('latitude' =>   14.6708333, 'longitude' =>  -17.4380556),
        'Damascus'    => array('latitude' =>   33.5,       'longitude' =>   36.3),
        'Dar es Salaam' => array('latitude' => -6.8,       'longitude' =>   39.2833333),
        'Dhaka'       => array('latitude' =>   23.7230556, 'longitude' =>   90.4086111),
        'Dili'        => array('latitude' =>   -8.5586111, 'longitude' =>  125.5736111),
        'Djibouti'    => array('latitude' =>   11.595,     'longitude' =>   43.1480556),
        'Dodoma'      => array('latitude' =>   -6.1833333, 'longitude' =>   35.75),
        'Doha'        => array('latitude' =>   25.2866667, 'longitude' =>   51.5333333),
        'Dubai'       => array('latitude' =>   25.2522222, 'longitude' =>   55.28),
        'Dublin'      => array('latitude' =>   53.3330556, 'longitude' =>   -6.2488889),
        'Dushanbe'    => array('latitude' =>   38.56,      'longitude' =>   68.7738889 ),
        'Fagatogo'    => array('latitude' =>  -14.2825,    'longitude' => -170.69),
        'Fongafale'   => array('latitude' =>   -8.5166667, 'longitude' =>  179.2166667),
        'Freetown'    => array('latitude' =>    8.49,      'longitude' =>  -13.2341667),
        'Gaborone'    => array('latitude' =>  -24.6463889, 'longitude' =>   25.9119444),
        'Geneva'      => array('latitude' =>   46.2,       'longitude' =>    6.1666667),
        'George Town' => array('latitude' =>   19.3,       'longitude' =>  -81.3833333),
        'Georgetown'  => array('latitude' =>    6.8,       'longitude' =>  -58.1666667),
        'Gibraltar'   => array('latitude' =>   36.1333333, 'longitude' =>   -5.35),
        'Glasgow'     => array('latitude' =>   55.8333333, 'longitude' =>   -4.25),
        'Guatemala la Nueva' => array('latitude' => 14.6211111, 'longitude' => -90.5269444),
        'Hagatna'     => array('latitude' =>   13.47417,   'longitude' =>  144.74778),
        'The Hague'   => array('latitude' =>   52.0833333, 'longitude' =>    4.3),
        'Hamilton'    => array('latitude' =>   32.2941667, 'longitude' =>  -64.7838889),
        'Hanoi'       => array('latitude' =>   21.0333333, 'longitude' =>  105.85),
        'Harare'      => array('latitude' =>  -17.8177778, 'longitude' =>   31.0447222),
        'Havana'      => array('latitude' =>   23.1319444, 'longitude' =>  -82.3641667),
        'Helsinki'    => array('latitude' =>   60.1755556, 'longitude' =>   24.9341667),
        'Honiara'     => array('latitude' =>   -9.4333333, 'longitude' =>  159.95),
        'Islamabad'   => array('latitude' =>   30.8486111, 'longitude' =>   72.4944444),
        'Istanbul'    => array('latitude' =>   41.0186111, 'longitude' =>   28.9647222),
        'Jakarta'     => array('latitude' =>   -6.1744444, 'longitude' =>  106.8294444),
        'Jamestown'   => array('latitude' =>  -15.9333333, 'longitude' =>   -5.7166667),
        'Jerusalem'   => array('latitude' =>   31.7666667, 'longitude' =>   35.2333333),
        'Johannesburg' => array('latitude' => -26.2,       'longitude' =>   28.0833333),
        'Kabul'       => array('latitude' =>   34.5166667, 'longitude' =>   69.1833333),
        'Kampala'     => array('latitude' =>    0.3155556, 'longitude' =>   32.5655556),
        'Kathmandu'   => array('latitude' =>   27.7166667, 'longitude' =>   85.3166667),
        'Khartoum'    => array('latitude' =>   15.5880556, 'longitude' =>   32.5341667),
        'Kigali'      => array('latitude' =>   -1.9536111, 'longitude' =>   30.0605556),
        'Kingston'    => array('latitude' =>  -29.05,      'longitude' =>  167.95),
        'Kingstown'   => array('latitude' =>   13.1333333, 'longitude' =>  -61.2166667),
        'Kinshasa'    => array('latitude' =>   -4.3,       'longitude' =>   15.3),
        'Kolkata'     => array('latitude' =>   22.5697222, 'longitude' =>   88.3697222),
        'Kuala Lumpur' => array('latitude' =>   3.1666667, 'longitude' =>  101.7),
        'Kuwait City' => array('latitude' =>   29.3697222, 'longitude' =>   47.9783333),
        'Kiev'        => array('latitude' =>   50.4333333, 'longitude' =>   30.5166667),
        'La Paz'      => array('latitude' =>  -16.5,       'longitude' =>  -68.15),
        'Libreville'  => array('latitude' =>    0.3833333, 'longitude' =>    9.45),
        'Lilongwe'    => array('latitude' =>  -13.9833333, 'longitude' =>   33.7833333),
        'Lima'        => array('latitude' =>  -12.05,      'longitude' =>  -77.05),
        'Lisbon'      => array('latitude' =>   38.7166667, 'longitude' =>   -9.1333333),
        'Ljubljana'   => array('latitude' =>   46.0552778, 'longitude' =>   14.5144444),
        'Lobamba'     => array('latitude' =>  -26.4666667, 'longitude' =>   31.2),
        'Lomé'        => array('latitude' =>    9.7166667, 'longitude' =>   38.3),
        'London'      => array('latitude' =>   51.5,       'longitude' =>   -0.1166667),
        'Los Angeles' => array('latitude' =>   34.05222,   'longitude' => -118.24278),
        'Luanda'      => array('latitude' =>   -8.8383333, 'longitude' =>   13.2344444),
        'Lusaka'      => array('latitude' =>  -15.4166667, 'longitude' =>   28.2833333),
        'Luxembourg'  => array('latitude' =>   49.6116667, 'longitude' =>    6.13),
        'Madrid'      => array('latitude' =>   40.4,       'longitude' =>   -3.6833333),
        'Majuro'      => array('latitude' =>    7.1,       'longitude' =>  171.3833333),
        'Malabo'      => array('latitude' =>    3.75,      'longitude' =>    8.7833333),
        'Managua'     => array('latitude' =>   12.1508333, 'longitude' =>  -86.2683333),
        'Manama'      => array('latitude' =>   26.2361111, 'longitude' =>   50.5830556),
        'Manila'      => array('latitude' =>   14.6041667, 'longitude' =>  120.9822222),
        'Maputo'      => array('latitude' =>  -25.9652778, 'longitude' =>   32.5891667),
        'Maseru'      => array('latitude' =>  -29.3166667, 'longitude' =>   27.4833333),
        'Mbabane'     => array('latitude' =>  -26.3166667, 'longitude' =>   31.1333333),
        'Melbourne'   => array('latitude' =>  -37.8166667, 'longitude' =>  144.9666667),
        'Melekeok'    => array('latitude' =>    7.4933333, 'longitude' =>  134.6341667),
        'Mexiko City' => array('latitude' =>   19.4341667, 'longitude' =>  -99.1386111),
        'Minsk'       => array('latitude' =>   53.9,       'longitude' =>   27.5666667),
        'Mogadishu'   => array('latitude' =>    2.0666667, 'longitude' =>   45.3666667),
        'Monaco'      => array('latitude' =>   43.7333333, 'longitude' =>    7.4166667),
        'Monrovia'    => array('latitude' =>    6.3105556, 'longitude' =>  -10.8047222),
        'Montevideo'  => array('latitude' =>  -34.8580556, 'longitude' =>  -56.1708333),
        'Montreal'    => array('latitude' =>   45.5,       'longitude' =>  -73.5833333),
        'Moroni'      => array('latitude' =>  -11.7041667, 'longitude' =>   43.2402778),
        'Moscow'      => array('latitude' =>   55.7522222, 'longitude' =>   37.6155556),
        'Muscat'      => array('latitude' =>   23.6133333, 'longitude' =>   58.5933333),
        'Nairobi'     => array('latitude' =>   -1.3166667, 'longitude' =>   36.8333333),
        'Nassau'      => array('latitude' =>   25.0833333, 'longitude' =>  -77.35),
        'N´Djamena'   => array('latitude' =>   12.1130556, 'longitude' =>   15.0491667),
        'New Dehli'   => array('latitude' =>   28.6,       'longitude' =>   77.2),
        'New York'    => array('latitude' =>   40.71417,   'longitude' =>  -74.00639),
        'Newcastle'   => array('latitude' =>  -32.9166667, 'longitude' =>  151.75),
        'Niamey'      => array('latitude' =>   13.6666667, 'longitude' =>    1.7833333),
        'Nicosia'     => array('latitude' =>   35.1666667, 'longitude' =>   33.3666667),
        'Nouakchott'  => array('latitude' =>   18.0863889, 'longitude' =>  -15.9752778),
        'Noumea'      => array('latitude' =>  -22.2666667, 'longitude' =>  166.45),
        'Nuku´alofa'  => array('latitude' =>  -21.1333333, 'longitude' => -175.2),
        'Nuuk'        => array('latitude' =>   64.1833333, 'longitude' =>  -51.75),
        'Oranjestad'  => array('latitude' =>   12.5166667, 'longitude' =>  -70.0333333),
        'Oslo'        => array('latitude' =>   59.9166667, 'longitude' =>   10.75),
        'Ouagadougou' => array('latitude' =>   12.3702778, 'longitude' =>   -1.5247222),
        'Palikir'     => array('latitude' =>    6.9166667, 'longitude' =>  158.15),
        'Panama City' => array('latitude' =>    8.9666667, 'longitude' =>  -79.5333333),
        'Papeete'     => array('latitude' =>  -17.5333333, 'longitude' => -149.5666667),
        'Paramaribo'  => array('latitude' =>    5.8333333, 'longitude' =>  -55.1666667),
        'Paris'       => array('latitude' =>   48.8666667, 'longitude' =>    2.3333333),
        'Perth'       => array('latitude' =>  -31.9333333, 'longitude' =>  115.8333333),
        'Phnom Penh'  => array('latitude' =>   11.55,      'longitude' =>  104.9166667),
        'Podgorica'   => array('latitude' =>   43.7752778, 'longitude' =>   19.6827778),
        'Port Louis'  => array('latitude' =>  -20.1666667, 'longitude' =>   57.5),
        'Port Moresby' => array('latitude' =>  -9.4647222, 'longitude' =>  147.1925),
        'Port-au-Prince' => array('latitude' => 18.5391667, 'longitude' => -72.335),
        'Port of Spain' => array('latitude' => 10.6666667, 'longitude' =>  -61.5),
        'Porto-Novo'  => array('latitude' =>    6.4833333, 'longitude' =>    2.6166667),
        'Prague'      => array('latitude' =>   50.0833333, 'longitude' =>   14.4666667),
        'Praia'       => array('latitude' =>   14.9166667, 'longitude' =>  -23.5166667),
        'Pretoria'    => array('latitude' =>  -25.7069444, 'longitude' =>   28.2294444),
        'Pyongyang'   => array('latitude' =>   39.0194444, 'longitude' =>  125.7547222),
        'Quito'       => array('latitude' =>   -0.2166667, 'longitude' =>  -78.5),
        'Rabat'       => array('latitude' =>   34.0252778, 'longitude' =>   -6.8361111),
        'Reykjavik'   => array('latitude' =>   64.15,      'longitude' =>  -21.95),
        'Riga'        => array('latitude' =>   56.95,      'longitude' =>   24.1),
        'Rio de Janero' => array('latitude' => -22.9,      'longitude' =>  -43.2333333),
        'Road Town'   => array('latitude' =>   18.4166667, 'longitude' =>  -64.6166667),
        'Rome'        => array('latitude' =>   41.9,       'longitude' =>   12.4833333),
        'Roseau'      => array('latitude' =>   15.3,       'longitude' =>  -61.4),
        'Rotterdam'   => array('latitude' =>   51.9166667, 'longitude' =>    4.5),
        'Salvador'    => array('latitude' =>  -12.9833333, 'longitude' =>  -38.5166667),
        'San José'    => array('latitude' =>    9.9333333, 'longitude' =>  -84.0833333),
        'San Juan'    => array('latitude' =>   18.46833,   'longitude' =>  -66.10611),
        'San Marino'  => array('latitude' =>   43.5333333, 'longitude' =>   12.9666667),
        'San Salvador' => array('latitude' =>  13.7086111, 'longitude' =>  -89.2030556),
        'Sanaá'       => array('latitude' =>   15.3547222, 'longitude' =>   44.2066667),
        'Santa Cruz'  => array('latitude' =>  -17.8,       'longitude' =>  -63.1666667),
        'Santiago'    => array('latitude' =>  -33.45,      'longitude' =>  -70.6666667),
        'Santo Domingo' => array('latitude' => 18.4666667, 'longitude' =>  -69.9),
        'Sao Paulo'   => array('latitude' =>  -23.5333333, 'longitude' =>  -46.6166667),
        'Sarajevo'    => array('latitude' =>   43.85,      'longitude' =>   18.3833333),
        'Seoul'       => array('latitude' =>   37.5663889, 'longitude' =>  126.9997222),
        'Shanghai'    => array('latitude' =>   31.2222222, 'longitude' =>  121.4580556),
        'Sydney'      => array('latitude' =>  -33.8833333, 'longitude' =>  151.2166667),
        'Singapore'   => array('latitude' =>    1.2930556, 'longitude' =>  103.8558333),
        'Skopje'      => array('latitude' =>   42,         'longitude' =>   21.4333333),
        'Sofia'       => array('latitude' =>   42.6833333, 'longitude' =>   23.3166667),
        'St. George´s' => array('latitude' =>  12.05,      'longitude' =>  -61.75),
        'St. John´s'  => array('latitude' =>   17.1166667, 'longitude' =>  -61.85),
        'Stanley'     => array('latitude' =>  -51.7,       'longitude' =>  -57.85),
        'Stockholm'   => array('latitude' =>   59.3333333, 'longitude' =>   18.05),
        'Suva'        => array('latitude' =>  -18.1333333, 'longitude' =>  178.4166667),
        'Taipei'      => array('latitude' =>   25.0166667, 'longitude' =>  121.45),
        'Tallinn'     => array('latitude' =>   59.4338889, 'longitude' =>   24.7280556),
        'Tashkent'    => array('latitude' =>   41.3166667, 'longitude' =>   69.25),
        'Tbilisi'     => array('latitude' =>   41.725,     'longitude' =>   44.7908333),
        'Tegucigalpa' => array('latitude' =>   14.1,       'longitude' =>  -87.2166667),
        'Tehran'      => array('latitude' =>   35.6719444, 'longitude' =>   51.4244444),
        'The Hague'   => array('latitude' =>   52.0833333, 'longitude' =>    4.3),
        'Thimphu'     => array('latitude' =>   27.4833333, 'longitude' =>   89.6),
        'Tirana'      => array('latitude' =>   41.3275,    'longitude' =>   19.8188889),
        'Tiraspol'    => array('latitude' =>   46.8402778, 'longitude' =>   29.6433333),
        'Tokyo'       => array('latitude' =>   35.685,     'longitude' =>  139.7513889),
        'Toronto'     => array('latitude' =>   43.6666667, 'longitude' =>  -79.4166667),
        'Tórshavn'    => array('latitude' =>   62.0166667, 'longitude' =>   -6.7666667),
        'Tripoli'     => array('latitude' =>   32.8925,    'longitude' =>   13.18),
        'Tunis'       => array('latitude' =>   36.8027778, 'longitude' =>   10.1797222),
        'Ulaanbaatar' => array('latitude' =>   47.9166667, 'longitude' =>  106.9166667),
        'Vaduz'       => array('latitude' =>   47.1333333, 'longitude' =>    9.5166667),
        'Valletta'    => array('latitude' =>   35.8997222, 'longitude' =>   14.5147222),
        'Valparaiso'  => array('latitude' =>  -33.0477778, 'longitude' =>  -71.6011111),
        'Vancouver'   => array('latitude' =>   49.25,      'longitude' => -123.1333333),
        'Vatican City' => array('latitude' =>  41.9,       'longitude' =>   12.4833333),
        'Victoria'    => array('latitude' =>   -4.6166667, 'longitude' =>   55.45),
        'Vienna'      => array('latitude' =>   48.2,       'longitude' =>   16.3666667),
        'Vientaine'   => array('latitude' =>   17.9666667, 'longitude' =>  102.6),
        'Vilnius'     => array('latitude' =>   54.6833333, 'longitude' =>   25.3166667),
        'Warsaw'      => array('latitude' =>   52.25,      'longitude' =>   21),
        'Washington dc' => array('latitude' => 38.895,     'longitude' =>  -77.03667),
        'Wellington'  => array('latitude' =>  -41.3,       'longitude' =>  174.7833333),
        'Willemstad'  => array('latitude' =>   12.1,       'longitude' =>  -68.9166667),
        'Windhoek'    => array('latitude' =>  -22.57,      'longitude' =>   17.0836111),
        'Yamoussoukro' => array('latitude' =>   6.8166667, 'longitude' =>   -5.2833333),
        'Yaoundé'     => array('latitude' =>    3.8666667, 'longitude' =>   11.5166667),
        'Yerevan'     => array('latitude' =>   40.1811111, 'longitude' =>   44.5136111),
        'Zürich'      => array('latitude' =>   47.3666667, 'longitude' =>    8.55),
        'Zagreb'      => array('latitude' =>   45.8,       'longitude' =>   16)
    );

    /**
     * Returns the location from the selected city
     *
     * @param string $city    - city to get location for
     * @param string $horizon - horizon to use :
     *                          default: effective
     *                          others are civil, nautic, astronomic
     * @return array
     * @throws Zend_Date_Exception
     */
    public static function City($city, $horizon = false) {
        foreach (self::$Cities as $key => $value) {
            if (strtolower($key) == strtolower($city)) {
                $return = $value;
                $return['horizon'] = $horizon;
                return $return;
            }
        }
        /**
         * @see Zend_Date_Exception
         */
        require_once 'Zend/Date/Exception.php';
        throw new Zend_Date_Exception('unknown city');
    }

    /**
     * Return a list with all known cities
     *
     * @return array
     */
    public static function getCityList() {
        return array_keys(self::$Cities);
    }
}
