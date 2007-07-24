@ECHO OFF
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
::
:: Batch file to start phpDocumentor with PHP's CLI
::
:: This SW was contributed by BlueShoes www.blueshoes.org "The PHP Framework"
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

::----------------------------------------------------------------------------------
:: Please set following to PHP's CLI
:: NOTE: In PHP 4.2.x the PHP-CLI used to be named php-cli.exe. 
::       PHP 4.3.x names it php.exe but stores it in a subdir called /cli/php.exe
::       E.g. for PHP 4.2 C:\phpdev\php-4.2-Win32\php-cli.exe
::            for PHP 4.3 C:\phpdev\php-4.3-Win32\cli\php.exe
  
  SET phpCli=C:\usr\local\php\cli\php.exe



::---------------------------------------------------------------------------------
::---------------------------------------------------------------------------------
:: Do not modify below this line!! (Unless you know what your doing :)
::---------------------------------------------------------------------------------
::---------------------------------------------------------------------------------

:: Only show this intro when no options are passed
IF '%1'=='' (
  ECHO ******************************************************************************
  ECHO * PhpDocument Command-Line  Starter 
  ECHO * 
  ECHO * phpDocumentor is a JavaDoc-like automatic documentation generator for PHP
  ECHO * written in PHP. It is the most versatile tool for documenting PHP.
  ECHO *
  ECHO * This batch-file will try to run the phpDocumentor using the command-line
  ECHO * version of PHP4. NOTE: It will not run with the PHP ISAPI module! 
  ECHO * Please update the path in this batch-file to your PHP-CLI.
  ECHO *
  ECHO * Tip: o Grab a copy of one of the ini-files in the user/ dir of the 
  ECHO *        phpDocumentor and modify the settings there. 
  ECHO *      o To see the command line options type  phpdoc -h
  ECHO * 
  ECHO * @version 1.3  2003-06-28
  ECHO * @author Sam Blum sam@blueshoes.org
  ECHO * @Copyright Free Software released under the GNU/GPL license
  ECHO * 
  ECHO * This SW was contributed by BlueShoes www.blueshoes.org "The PHP Framework"
  ECHO ******************************************************************************
)

:: Check existence of php.exe
IF EXIST "%phpCli%" (
  SET doNothing=
) ELSE GOTO :NoPhpCli

:: If called using options, just call phpdoc and end after without pausing.
:: This will allow use where pausing is not wanted.
IF '%1'=='' (
  SET doNothing=
) ELSE (
  "%phpCli%" phpdoc %*
  GOTO :EOF
)


SET iniFile=

ECHO ------------------------------------------------------------------------------
ECHO Select Ini-File [default is phpDocumentor.ini]
ECHO ------------------------------------------------------------------------------
ECHO # 0: phpDocumentor.ini
SET count=0
FOR /R user %%I IN (*.ini) DO (
  SET /a count+=1
  CALL :exec ECHO # %%count%%: %%~nI%%~xI
)

:LOOP_1
:: SET /P prompts for input and sets the variable
:: to whatever the user types
SET iniNr=
SET /P iniNr=Type a number and press Enter[0]:

::  Use default
IF '%iniNr%'=='' (
  SET iniNr=0
)

:: Check for default selection
SET iniFile=phpDocumentor.ini
IF %iniNr%==0 (
  CALL :exec GOTO :run
  GOTO :PAUSE_END
)

:: Check selected
SET count=0
SET found=
FOR /R user %%I IN (*.ini) DO (
  SET /a count+=1
  SET iniFile=%%~nI%%~xI
  CALL :exec IF '%%iniNr%%'=='%%count%%' GOTO :run 
)

:: Check if selected # was found
IF '%found%'=='' (
  ECHO Invalid input [%iniNr%]... try again
  ECHO.
  GOTO :LOOP_1
)

::
:: php.exe not found error  
GOTO :PAUSE_END
:NoPhpCli
ECHO ** ERROR *****************************************************************
ECHO * Sorry, can't find the php.exe file.
ECHO * You must edit this file to point to your php.exe (CLI version!)
ECHO *    [Currently set to %phpCli%]
ECHO * 
ECHO * NOTE: In PHP 4.2.x the PHP-CLI used to be named php-cli.exe. 
ECHO *       PHP 4.3.x renamed it php.exe but stores it in a subdir 
ECHO *       called /cli/php.exe
ECHO *       E.g. for PHP 4.2 C:\phpdev\php-4.2-Win32\php-cli.exe
ECHO *            for PHP 4.3 C:\phpdev\php-4.3-Win32\cli\php.exe
ECHO **************************************************************************

::
:: Stupid MS-batch: Can't evaluate environment variable inside a FOR loop!!! :((  
GOTO :PAUSE_END
:exec 
%*
GOTO :EOF

::
:: Start the phpDocumentor 
GOTO :PAUSE_END
:run
SET found=1
ECHO Starting: "%phpCli%" phpdoc -c "%iniFile%"
ECHO.
"%phpCli%" phpdoc -c "%iniFile%"
GOTO :EOF

:PAUSE_END
PAUSE