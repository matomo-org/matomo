{literal}
    <style type="text/css">
        .trackingHelp ul {
            padding-left: 40px;
            list-style-type: square;
        }

        .trackingHelp ul li {
            margin-bottom: 10px;
        }

        .trackingHelp h2 {
            margin-top: 20px;
        }

        p {
            text-align: justify;
        }
    </style>
{/literal}

<h2>{'SitesManager_TrackingTags'|translate:$displaySiteName}</h2>

<div class='trackingHelp'>
    {'Installation_JSTracking_Intro'|translate}
    <br/><br/>
    {'CoreAdminHome_JSTrackingIntro3'|translate:'<a href="http://piwik.org/integrate/" target="_blank">':'</a>'}

    <h3>{'SitesManager_JsTrackingTag'|translate}</h3>

    <p>{'CoreAdminHome_JSTracking_CodeNote'|translate:"&lt;/body&gt;"}</p>

    <pre class="code-pre"><code>{$jsTag}</code></pre>

    <br/>
    {'CoreAdminHome_JSTrackingIntro5'|translate:'<a target="_blank" href="http://piwik.org/docs/javascript-tracking/">':'</a>'}
    <br/><br/>
    {'Installation_JSTracking_EndNote'|translate:'<em>':'</em>'}

</div>
{literal}
    <script type="text/javascript">
        $(document).ready(function () {
            // when code element is clicked, select the text
            $('code').click(function () {
                // credit where credit is due:
                //   http://stackoverflow.com/questions/1173194/select-all-div-text-with-single-mouse-click
                var range;
                if (document.body.createTextRange) // MSIE
                {
                    range = document.body.createTextRange();
                    range.moveToElementText(this);
                    range.select();
                }
                else if (window.getSelection) // others
                {
                    range = document.createRange();
                    range.selectNodeContents(this);

                    var selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
            });

            $('code').click();
        });
    </script>
{/literal}
