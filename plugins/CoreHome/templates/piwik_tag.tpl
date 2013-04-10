{* Disabled by default, tracks activity of this Piwik instance *}

{if $piwikUrl == 'http://demo.piwik.org/' || $debugTrackVisitsInsidePiwikUI}
    <div class="clear"></div>
    {literal}
        <!-- Piwik -->
        <script type="text/javascript">
        var _paq = _paq || [];
        _paq.push(['setTrackerUrl', 'piwik.php']);
        _paq.push(['setSiteId', 1]);
    {/literal}
_paq.push(['setCookieDomain', '*.piwik.org']);
    {literal}
        // set the domain the visitor landed on, in the Custom Variable
        _paq.push([function () {
        if (!this.getCustomVariable(1))
        {
        this.setCustomVariable(1, "Domain landed", document.domain);
        }
        }]);
        // Set the selected Piwik language in a custom var
        _paq.push(['setCustomVariable', 2, "Demo language", piwik.languageName]);
        _paq.push(['setDocumentTitle', document.domain + "/" + document.title]);
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);

        (function() {
        var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript';
        g.defer=true; g.async=true; g.src='js/piwik.js'; s.parentNode.insertBefore(g,s);
        })();
        </script>
        <!-- End Piwik Code -->
    {/literal}

{/if}
