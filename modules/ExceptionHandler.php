<?php

function Piwik_ExceptionHandler(Exception $exception) {
  echo "<b><div style='font-size:11pt'><pre>Uncaught exception: " , $exception->getMessage(), "\n";
  print( $exception->__toString() );
  echo "</b>";
  exit;
}
?>
