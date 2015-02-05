<?php
exit; // Remove this line before using the script

// How to remove the piwik/ directory if it does not work in FTP?
// 1) Download and upload this file to your webserver
// 2) Remove the 2nd line (the "exit;")
// 3) Put this file in the folder that contains the piwik/ directory (above the piwik/ directory)
//    For example if the piwik/ folder is at http://your-site/piwik/ you put the file in http://your-site/uninstall-delete-piwik-directory.php
// 4) Go with your browser to http://your-site/uninstall-delete-piwik-directory.php
// 5) The folder http://your-site/piwik/ should now be deleted!
// We hope you enjoyed Piwik. If you have any feedback why you stopped using Piwik,
// please let us know at hello@piwik.org - we are interested by your experience
function unlinkRecursive($dir)
{
    if (!$dh = @opendir($dir)) return "Warning: folder $dir couldn't be read by PHP";
    while (false !== ($obj = readdir($dh))) {
        if ($obj == '.' || $obj == '..') {
            continue;
        }
        if (!@unlink($dir . '/' . $obj)) {
            unlinkRecursive($dir . '/' . $obj, true);
        }
    }
    closedir($dh);
    @rmdir($dir);
    return "Folder $dir deleted!";
}

echo unlinkRecursive('piwik/');
