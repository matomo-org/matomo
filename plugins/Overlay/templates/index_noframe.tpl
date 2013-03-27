{include file="CoreHome/templates/header.tpl"}
<h1>{'Overlay_Overlay'|translate|escape:'html'}</h1>

<div id="Overlay_NoFrame">

    <script type="text/javascript">
        var newLocation = 'index.php?module=Overlay&action=startOverlaySession&idsite={$idSite}&period={$period}&date={$date}';

        {literal}

        var locationParts = window.location.href.split('#');
        if (locationParts.length > 1) {
            var url = broadcast.getParamValue('l', locationParts[1]);
            url = Overlay_Helper.decodeFrameUrl(url);
            newLocation += '#' + url;
        }

        window.location.href = newLocation;

        {/literal}
    </script>

</div>

<!-- close tag opened in header.tpl -->
</div>
</body>
</html>