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
            $loadTracker = '1' === ($_GET['loadTracker'] ?? '1');
        ?>

        <?php if ('iframe' === $implementation) : ?>
            <h2>Iframe</h2>
            <iframe id="optOutIframe" src="../../../index.php?module=CoreAdminHome&action=optOut&language=en"></iframe>
        <?php elseif ('js' === $implementation) : ?>
            <h2>JS</h2>

            <div style="border: 1px solid black; padding: 8px; width: 640px">
                <div id="matomo-opt-out"></div>

                <?php
                    $optOutArgs = [
                        'divId' => $_GET['divId'] ?? 'matomo-opt-out',
                        'showIntro' => '1',
                        'useCookiesIfNoTracker' => $loadTracker ? '0' : '1'
                    ];
                ?>
                <script src="../../../index.php?module=CoreAdminHome&action=optOutJS&language=auto&<?= http_build_query($optOutArgs) ?>"></script>
            </div>
        <?php else : ?>
            <strong>Missing or unknown implementation parameter!</strong>
        <?php endif ?>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

        <?php if ($loadTracker) : ?>
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
        <?php endif ?>
    </body>
</html>
