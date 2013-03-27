<span id="logo">
<a href="index.php" title="{if $isCustomLogo}{'General_PoweredBy'|translate} {/if}Piwik # {'General_OpenSourceWebAnalytics'|translate}"
   style="text-decoration: none;">
    {if $hasSVGLogo}
<img src='{$logoSVG}' alt="{if $isCustomLogo}{'General_PoweredBy'|translate} {/if}Piwik" style='margin-left: 10px' height='40' class="ie-hide"/>
    <!--[if lt IE 9]>
    {/if}
    <img src='{$logoHeader}' alt="{if $isCustomLogo}{'General_PoweredBy'|translate} {/if}Piwik" style='margin-left:10px' height='50'/>
    {if $hasSVGLogo}<![endif]-->{/if}
</a>
</span>
