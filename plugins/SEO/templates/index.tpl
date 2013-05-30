<div id='SeoRanks'>
    <script type="text/javascript" src="plugins/SEO/templates/rank.js"></script>

    <form method="post" style="padding: 8px;">
        <div align="left" class="mediumtext">
            {'Installation_SetupWebSiteURL'|translate|ucfirst}
            <input type="text" id="seoUrl" size="15" value="{$urlToRank|escape:'html'}" class="textbox"/>
		  <span style="padding-left:2px;"> 
		  <input type="submit" id="rankbutton" value="{'SEO_Rank'|translate}"/>
		  </span>
        </div>

        {ajaxLoadingDiv id=ajaxLoadingSEO}

        <div id="rankStats" align="left" style='margin-top:10px'>
            {if empty($ranks)}
                {'General_Error'|translate}
            {else}
                {capture name=cleanUrl}
                    <a href='http://{$urlToRank|escape:'html'}' target='_blank'>{$urlToRank|escape:'html'}</a>
                {/capture}
                {'SEO_SEORankingsFor'|translate:$smarty.capture.cleanUrl}
                <table cellspacing='2' style='margin:auto;line-height:1.5em;padding-top:10px'>
                    {foreach from=$ranks item=rank}
                        <tr>
{capture assign=seoLink}{if !empty($rank.logo_link)}<a class="linkContent" href="?module=Proxy&action=redirect&url={$rank.logo_link|urlencode}" target="_blank"
                         {if !empty($rank.logo_tooltip)}title="{$rank.logo_tooltip}"{/if}>{/if}{/capture}
                            {capture assign=majesticLink}{$seoLink}Majestic</a>{/capture}
                            <td>{if !empty($rank.logo_link)}{$seoLink}{/if}<img
                                            style='vertical-align:middle;margin-right:6px;' src='{$rank.logo}' border='0'
                                            alt="{$rank.label}">{if !empty($rank.logo_link)}</a>{/if} {$rank.label|replace:"Majestic":$majesticLink}
                            </td>
                            <td>
                                <div style='margin-left:15px'>
                                {if !empty($rank.logo_link)}{$seoLink}{/if}
                                    {if isset($rank.rank)}{$rank.rank}{else}-{/if}
                                    {if $rank.id=='pagerank'} /10
                                    {elseif $rank.id=='google-index' || $rank.id=='bing-index'} {'SEO_Pages'|translate}
                                    {/if}
                                {if !empty($rank.logo_link)}</a>{/if}
                                </div>
                            </td>
                        </tr>
                    {/foreach}

                </table>
            {/if}
        </div>
    </form>
</div>
