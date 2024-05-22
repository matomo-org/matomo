<?php
?><!DOCTYPE html>
<html lang="en">
    <head>
        <title>Opt Out UI Test</title>
        <style>
            * {
                font-family: Arial !important;
            }
        </style>
    </head>
    <body>
        <h1>Opt Out</h1>

        <?php
            $implementation = $_GET['implementation'] ?? '';
        ?>

        <?php if ('iframe' === $implementation) : ?>
            <h2>Iframe</h2>
            <iframe id="optOutIframe" src="../../../index.php?module=CoreAdminHome&action=optOut&language=en"></iframe>
        <?php else : ?>
            <strong>Missing or unknown implementation parameter!</strong>
        <?php endif ?>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <script type="text/javascript">
            var pkBaseURL = (("https:" == document.location.protocol) ? "https://%trackerBaseUrl%" : "http://%trackerBaseUrl%");
            document.write(unescape("%3Cscript src='" + pkBaseURL + "matomo.js' type='text/javascript'%3E%3C/script%3E"));
        </script>
        <script>
            var pkBaseURL = (("https:" == document.location.protocol) ? "https://%trackerBaseUrl%" : "http://%trackerBaseUrl%");
            try {
                var piwikTracker = Piwik.addTracker(pkBaseURL + "matomo.php", %idSite%);
                piwikTracker.setCookieDomain('*.piwik.org');

                piwikTracker.trackPageView();
                piwikTracker.enableLinkTracking();
            } catch( err ) {
                console.log(err.stack || err.message);
            }
        </script>
    </body>
</html>
