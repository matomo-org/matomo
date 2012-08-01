{* The following parameters can be used to customize this widget when 'include'-ing:
 * 
 * - $showAllSitesItem true if the 'All Websites' option should be shown, false if otherwise. Default = true.
 * - $allSitesItemText The text to use for the 'All Websites' option. Default = 'All Websites'.
 * - $allWebsitesLinkLocation The location of the 'All Websites' option. Can be 'top' or 'bottom'. Default = 'bottom'.
 * - $showSelectedSite false if the currently selected site should be excluded from the list of sites. Default = false.
 * - $sites The list of sites to use. By default, the first N sites are used. They are retrieved in Piwik_View.
 * - $show_autocompleter Whether to show the autocompleter or not. Default true.
 * - $switchSiteOnSelect Whether to change the page w/ a new idSite value when a site is selected, or not.
 *                       Default = true.
 * - $inputName If set, a hidden <input> w/ name == $inputName is created which will hold the selected site's ID. For
 *              use with <form> elements.
 * - $siteName The currently selected site name. Defaults to the first name in $sites set by Piwik_View.
 * - $idSite The currently selected idSite. Defaults to the first id in $sites set by Piwik_View.
 *}
{capture name=sitesSelector_allWebsitesLink assign=sitesSelector_allWebsitesLink}
<div class="custom_select_all" style="clear: both">
	<a href="#" {if isset($showAllSitesItem) && $showAllSitesItem eq false}style="display:none;"{/if}>
		{if isset($allSitesItemText)}{$allSitesItemText}{else}{'General_MultiSitesSummary'|translate}{/if}
	</a>
</div>
{/capture}
<div class="sites_autocomplete">
    <div id="sitesSelectionSearch" class="custom_select">
    
        <a href="#" onclick="return false" class="custom_select_main_link" siteid="{if isset($idSite)}{$idSite}{else}{$sites[0].idsite}{/if}">{if isset($siteName)}{$siteName}{else}{$sites[0].name}{/if}</a>
        
        <div class="custom_select_block">
            {if isset($allWebsitesLinkLocation) && $allWebsitesLinkLocation eq 'top'}
            {$sitesSelector_allWebsitesLink}
            {/if}
            <div id="custom_select_container">
            <ul class="custom_select_ul_list" >
                {foreach from=$sites item=info}
                    <li {if (!isset($showSelectedSite) || $showSelectedSite eq false) && $idSite==$info.idsite} style="display: none"{/if}><a href="#" siteid="{$info.idsite}">{$info.name}</a></li>
				{/foreach}
            </ul>
            </div>
            {if !isset($allWebsitesLinkLocation) || $allWebsitesLinkLocation eq 'bottom'}
            {$sitesSelector_allWebsitesLink}
            {/if}
            <div class="custom_select_search" {if !$show_autocompleter}style="display:none;"{/if}>
                <input type="text" length="15" id="websiteSearch" class="inp"/>
                <input type="hidden" class="max_sitename_width" id="max_sitename_width" value="130" />
                <input type="submit" value="Search" class="but"/>
				<img title="Clear" id="reset" style="position: relative; top: 4px; left: -44px; cursor: pointer; display: none;" src="plugins/CoreHome/templates/images/reset_search.png"/>
            </div>
        </div>
	</div>
	{if isset($inputName)}<input type="hidden" name="{$inputName}" value="{if isset($idSite)}{$idSite}{else}{$sites[0].idsite}{/if}"/>{/if}
	{if isset($switchSiteOnSelect) && $switchSiteOnSelect eq false}
	<script type="text/javascript">
	{literal}
		// make sure site is not switched an item is selected
		window.autocompleteOnNewSiteSelect = function(id) {
			$('.sites_autocomplete input').val(id);
		};
	{/literal}
	</script>
	{/if}
</div>

