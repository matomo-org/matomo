<?php
/** @package tests */
/** include tests */
//require_once $FORUM['LIB'] . '/classes/db/PearDb.php'; // removed due to CVE-2005-4593
require PEAR . 'test' . 'me';
include('file.ext');
include 'file.ext';
include(PEAR . 'test' . 'me');
?>