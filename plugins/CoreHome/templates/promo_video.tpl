{literal}
    <script type="text/javascript">
        $(document).ready(function () {
            $('#piwik-promo-thumbnail').click(function () {
                var promoEmbed = $('#piwik-promo-embed'),
                        widgetWidth = $(this).closest('.widgetContent').width(),
                        height = (266 * widgetWidth) / 421,
                        embedHtml = '<iframe width="100%" height="' + height + '" src="http://www.youtube.com/embed/OslfF_EH81g?autoplay=1&vq=hd720&wmode=transparent" frameborder="0" wmode="Opaque"></iframe>';

                $(this).hide();
                promoEmbed.height(height).html(embedHtml);
                promoEmbed.show();
            });
        });
    </script>
    <style type="text/css">
        #piwik-promo-thumbnail {
            background: #fff url(plugins/CoreHome/templates/images/promo_splash.png) no-repeat 0 0;
            background-position: center;
            width: 321px;
            margin: 0 auto 0 auto;
        }

        #piwik-promo-embed {
            margin-left: 1px;
        }

        #piwik-promo-embed>iframe {
            z-index: 0;
        }

        #piwik-promo-thumbnail {
            height: 178px;
        }

        #piwik-promo-thumbnail:hover {
            opacity: .75;
            cursor: pointer;
        }

        #piwik-promo-thumbnail>img {
            display: block;
            position: relative;
            top: 53px;
            left: 125px;
        }

        #piwik-promo-video {
            margin: 2em 0 2em 0;
        }

        #piwik-widget-footer {
            margin: 0 1em 1em 1em;
        }

        #piwik-promo-share {
            margin: 0 2em 1em 0;
            background-color: #CCC;

            border: 1px solid #CCC;
            -webkit-border-radius: 6px;
            -moz-border-radius: 6px;
            border-radius: 6px;

            display: inline-block;

            padding: 0 .5em 0 .5em;

            float: right;
        }

        #piwik-promo-share > a {
            margin-left: .5em;
            margin-top: 4px;
            display: inline-block;
        }

        #piwik-promo-share>span {
            display: inline-block;
            vertical-align: top;
            margin-top: 4px;
        }

        #piwik-promo-videos-link {
            font-size: .8em;
            font-style: italic;
            margin: 1em 0 0 1.25em;
            color: #666;
            display: inline-block;
        }

        #piwik-promo-videos-link:hover {
            text-decoration: none;
        }
    </style>
{/literal}
<div id="piwik-promo">
    <div id="piwik-promo-video">
        <div id="piwik-promo-thumbnail">
            <img src="themes/default/images/video_play.png"/>
        </div>

        <div id="piwik-promo-embed" style="display:none">
        </div>
    </div>

    <a id="piwik-promo-videos-link" href="http://piwik.org/blog/2012/12/piwik-how-to-videos/" target="_blank">
        {'CoreHome_ViewAllPiwikVideoTutorials'|translate}
    </a>

    <div id="piwik-promo-share">
        <span>{'CoreHome_ShareThis'|translate}:</span>

        {* facebook *}
        <a href="http://www.facebook.com/sharer.php?u={$promoVideoUrl|urlencode|escape:'html'}" target="_blank"><img
                    src="plugins/Referers/images/socials/facebook.com.png"/></a>

        {* twitter *}
        <a href="http://twitter.com/share?text={$shareText|urlencode|escape:'html'}&url={$promoVideoUrl|urlencode|escape:'html'}" target="_blank"><img
                    src="plugins/Referers/images/socials/twitter.com.png"/></a>

        {* email *}
        <a href="mailto:?body={$shareTextLong|rawurlencode|escape:'html'}&subject={$shareText|rawurlencode|escape:'html'}" target="_blank"><img
                    src="themes/default/images/email.png"/></a>
    </div>

    <div style="clear:both"></div>

    <div id="piwik-widget-footer" style='color:#666'>{'CoreHome_CloseWidgetDirections'|translate}</div>
</div>
