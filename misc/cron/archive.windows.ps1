#===========================================================================
# Description
# This powershell script will automatically run Piwik archiving for whatever
# frequency you set it up to run, it is recommended that is be every 1 hour
# or 3600 seconds. The script will also run scheduled tasks configured within 
# piwik using the event hook 'TaskScheduler.getScheduledTasks'

# It automatically fetches the Super User token_auth 
# and triggers the archiving for all websites for all periods.
# This ensures that all reports are pre-computed and Piwik renders very fast. 

# Documentation
# Please check the documentation on http://piwik.org/docs/setup-auto-archiving/

# Optimization for high traffic websites
# You may want to override the following settings in config/config.ini.php:
# See documentation of the fields in your piwik/config/config.ini.php 
#
# [General]
# time_before_archive_considered_outdated = 3600
# enable_browser_archiving_triggering = false
#
#===========================================================================
$PHP_INI = "C:\Program Files\EasyPHP-5.3.2i\apache\php.ini"
$BINS = @("php5.exe", "php.exe")

foreach($phpTestBin in $BINS)
{
  if(Get-Command $phpTestBin -ea SilentlyContinue)
  {
    $PHP_BIN = (Get-Command $phpTestBin).Definition
    break
  }
}

if(($PHP_BIN -eq $null) -or !(Test-Path $PHP_BIN -ea SilentlyContinue))
{
  Write-Host "php binary not found. Make sure php5 or php exists in PATH."
  Exit 1
}

$PIWIK_SCRIPT_FOLDER = Split-Path -parent $MyInvocation.MyCommand.Definition
$PIWIK_PATH="$PIWIK_SCRIPT_FOLDER\..\..\index.php"
$PIWIK_CONFIG="$PIWIK_SCRIPT_FOLDER\..\..\config/config.ini.php"

Function Parse-IniFile ($file) {
  $ini = @{}
  switch -regex -file $file {
    "^\[(.+)\]$" {
      $section = $matches[1].Trim()
      $ini[$section] = @{}
    }
    "(.+)=(.+)" {
      $name,$value = $matches[1..2]
      $name = $name.Trim()
      $value = $value.Trim()
      $ini[$section][$name] = $value      
    }
  }
  $ini
}

$CONFIG = Parse-IniFile $PIWIK_CONFIG
$PIWIK_SUPERUSER=$CONFIG["superuser"]["login"].Replace('"', '')
$PIWIK_SUPERUSER_MD5_PASSWORD=$CONFIG["superuser"]["password"].Replace('"', '')

$TOKEN_AUTH= & $PHP_BIN -c $PHP_INI "$PIWIK_PATH" "--" "module=API&method=UsersManager.getTokenAuth&userLogin=$PIWIK_SUPERUSER&md5Password=$PIWIK_SUPERUSER_MD5_PASSWORD&format=php&serialize=0"

$ID_SITES= & $PHP_BIN -c $PHP_INI "$PIWIK_PATH" "--" "module=API&method=SitesManager.getAllSitesId&token_auth=$TOKEN_AUTH&format=csv&convertToUnicode=0"

Write-Host "Starting Piwik archiving..."

foreach($ID_SITE in $ID_SITES)
{  
  if($ID_SITE -match "^\d+$")
  {
    foreach($period in @("day","week","year")) 
    {
      Write-Host ""
      Write-Host "Archiving period = $period for idsite = $ID_SITE..."
      & $PHP_BIN -c $PHP_INI "$PIWIK_PATH" "--" "module=API&method=VisitsSummary.getVisits&idSite=$ID_SITE&period=$period&date=last52&format=xml&token_auth=$TOKEN_AUTH"      
    }

    Write-Host ""
    Write-Host "Archiving for idsite = $ID_SITE done!"
  }
}

Write-Host "Piwik archiving finished."

Write-Host "Starting Scheduled tasks..."
Write-Host ""

	& $PHP_BIN -c $PHP_INI "$PIWIK_PATH" "--" "module=API&method=CoreAdminHome.runScheduledTasks&format=csv&convertToUnicode=0&token_auth=$TOKEN_AUTH"

Write-Host ""	
Write-Host "Finished Scheduled tasks."
