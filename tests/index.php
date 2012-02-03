<html>
<body>
<p>Hello, world!</p>
<p>Today looks like an ideal day to write & run tests.</p>

<?php 
require_once "TestRunner.php";
echo TestRunner::$testsHelpLinks;
?>
<p>If you are new to the wonderful world of testing, <a href='README.txt'>see the README</a> for an introduction.</p>
 
<img src='resources/disturbing-image.jpg' alt='I find your lack of tests disturbing'>
<br/><i><a href='http://www.flickr.com/photos/sebastian_bergmann/2282734669/'>Photo source & license</a></i>
</body>
</html>