<?php

/* geoipcity.inc
 *
 * Copyright (C) 2004 Maxmind LLC
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
 */

/*
 * Changelog:
 *
 * 2005-01-13   Andrew Hill, Awarez Ltd. (http://www.awarez.net)
 *              Formatted file according to PEAR library standards.
 *              Changed inclusion of geoip.inc file to require_once, so that
 *                  this library can be used in the same script as geoip.inc.
 */

define("FULL_RECORD_LENGTH",50);

require_once 'geoip.inc';
require_once 'geoipregionvars.php';

class geoiprecord {
  var $country_code;
  var $country_code3;
  var $country_name;
  var $region;
  var $city;
  var $postal_code;
  var $latitude;
  var $longitude;
  var $area_code;
  var $dma_code;   # metro and dma code are the same. use metro_code
  var $metro_code;
  var $continent_code;
}

class geoipdnsrecord {
  var $country_code;
  var $country_code3;
  var $country_name;
  var $region;
  var $regionname;
  var $city;
  var $postal_code;
  var $latitude;
  var $longitude;
  var $areacode;
  var $dmacode;
  var $isp;
  var $org;
  var $metrocode;
}

function getrecordwithdnsservice($str){
  $record = new geoipdnsrecord;
  $keyvalue = explode(";",$str);
  foreach ($keyvalue as $keyvalue2){
    list($key,$value) = explode("=",$keyvalue2);
    if ($key == "co"){
      $record->country_code = $value;
    }
    if ($key == "ci"){
      $record->city = $value;
    }
    if ($key == "re"){
      $record->region = $value;
    }
    if ($key == "ac"){
      $record->areacode = $value;
    }
    if ($key == "dm" || $key == "me" ){
      $record->dmacode   = $value;
      $record->metrocode = $value;
    }
    if ($key == "is"){
      $record->isp = $value;
    }
    if ($key == "or"){
      $record->org = $value;
    }
    if ($key == "zi"){
      $record->postal_code = $value;
    }
    if ($key == "la"){
      $record->latitude = $value;
    }
    if ($key == "lo"){
      $record->longitude = $value;
    }
  }
  $number = $GLOBALS['GEOIP_COUNTRY_CODE_TO_NUMBER'][$record->country_code];
  $record->country_code3 = $GLOBALS['GEOIP_COUNTRY_CODES3'][$number];
  $record->country_name = $GLOBALS['GEOIP_COUNTRY_NAMES'][$number];
  if ($record->region != "") {
    if (($record->country_code == "US") || ($record->country_code == "CA")){
      $record->regionname = $GLOBALS['ISO'][$record->country_code][$record->region];
    } else {
      $record->regionname = $GLOBALS['FIPS'][$record->country_code][$record->region];
    }
  }
  return $record;
}


function _get_record_v6($gi,$ipnum){
  $seek_country = _geoip_seek_country_v6($gi,$ipnum);
  if ($seek_country == $gi->databaseSegments) {
    return NULL;
  }
  return _common_get_record($gi, $seek_country);
}

function _common_get_record($gi, $seek_country){
  // workaround php's broken substr, strpos, etc handling with
  // mbstring.func_overload and mbstring.internal_encoding
  $enc = mb_internal_encoding();
  mb_internal_encoding('ISO-8859-1'); 

  $record_pointer = $seek_country + (2 * $gi->record_length - 1) * $gi->databaseSegments;
  
  if ($gi->flags & GEOIP_MEMORY_CACHE) {
    $record_buf = substr($gi->memory_buffer,$record_pointer,FULL_RECORD_LENGTH);
  } elseif ($gi->flags & GEOIP_SHARED_MEMORY){
    $record_buf = @shmop_read($gi->shmid,$record_pointer,FULL_RECORD_LENGTH);
  } else {
    fseek($gi->filehandle, $record_pointer, SEEK_SET);
    $record_buf = fread($gi->filehandle,FULL_RECORD_LENGTH);
  }
  $record = new geoiprecord;
  $record_buf_pos = 0;
  $char = ord(substr($record_buf,$record_buf_pos,1));
    $record->country_code = $gi->GEOIP_COUNTRY_CODES[$char];
    $record->country_code3 = $gi->GEOIP_COUNTRY_CODES3[$char];
    $record->country_name = $gi->GEOIP_COUNTRY_NAMES[$char];
  $record->continent_code = $gi->GEOIP_CONTINENT_CODES[$char];
  $record_buf_pos++;
  $str_length = 0;
    // Get region
  $char = ord(substr($record_buf,$record_buf_pos+$str_length,1));
  while ($char != 0){
    $str_length++;
    $char = ord(substr($record_buf,$record_buf_pos+$str_length,1));
  }
  if ($str_length > 0){
    $record->region = substr($record_buf,$record_buf_pos,$str_length);
  }
  $record_buf_pos += $str_length + 1;
  $str_length = 0;
    // Get city
  $char = ord(substr($record_buf,$record_buf_pos+$str_length,1));
  while ($char != 0){
    $str_length++;
    $char = ord(substr($record_buf,$record_buf_pos+$str_length,1));
  }
  if ($str_length > 0){
    $record->city = substr($record_buf,$record_buf_pos,$str_length);
  }
  $record_buf_pos += $str_length + 1;
  $str_length = 0;
    // Get postal code
  $char = ord(substr($record_buf,$record_buf_pos+$str_length,1));
  while ($char != 0){
    $str_length++;
    $char = ord(substr($record_buf,$record_buf_pos+$str_length,1));
  }
  if ($str_length > 0){
    $record->postal_code = substr($record_buf,$record_buf_pos,$str_length);
  }
  $record_buf_pos += $str_length + 1;
  $str_length = 0;
    // Get latitude and longitude
  $latitude = 0;
  $longitude = 0;
  for ($j = 0;$j < 3; ++$j){
    $char = ord(substr($record_buf,$record_buf_pos++,1));
    $latitude += ($char << ($j * 8));
  }
  $record->latitude = ($latitude/10000) - 180;
  for ($j = 0;$j < 3; ++$j){
    $char = ord(substr($record_buf,$record_buf_pos++,1));
    $longitude += ($char << ($j * 8));
  }
  $record->longitude = ($longitude/10000) - 180;
  if (GEOIP_CITY_EDITION_REV1 == $gi->databaseType){
    $metroarea_combo = 0;
    if ($record->country_code == "US"){
      for ($j = 0;$j < 3;++$j){
        $char = ord(substr($record_buf,$record_buf_pos++,1));
        $metroarea_combo += ($char << ($j * 8));
      }
      $record->metro_code = $record->dma_code = floor($metroarea_combo/1000);
      $record->area_code = $metroarea_combo%1000;
    }
  }
  mb_internal_encoding($enc);
  return $record;
}

function GeoIP_record_by_addr_v6 ($gi,$addr){
  if ($addr == NULL){
     return 0;
  }
  $ipnum = inet_pton($addr);
  return _get_record_v6($gi, $ipnum);
}

function _get_record($gi,$ipnum){
  $seek_country = _geoip_seek_country($gi,$ipnum);
  if ($seek_country == $gi->databaseSegments) {
    return NULL;
  }
  return _common_get_record($gi, $seek_country);
}

function GeoIP_record_by_addr ($gi,$addr){
  if ($addr == NULL){
     return 0;
  }
  $ipnum = ip2long($addr);
  return _get_record($gi, $ipnum);
}

?>
