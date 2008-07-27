<div id="bar">
<span class="bar-elem"><b>Your Dashboard</b></span>
<span class="bar-elem"><a href='?module=CoreAdminHome&amp;action=showInContext&amp;moduleToLoad=API&amp;actionToLoad=listAllAPI&amp;module=CoreAdminHome&amp;action=showInContext'>API</a></span> 
<span class="bar-elem"><a href='?module=Widgetize'>Widgets</a></span>
<span class="bar-elem"><a href='?module=Feedback&amp;action=index&amp;keepThis=true&amp;TB_iframe=true&amp;height=400&amp;width=320' title="Send us feedback" class="thickbox">Send us feedback</a></span>
</div>


<div align="right">
<div id="user" align="right" width="100%" style="padding: 0pt 0pt 4px; font-size: 84%;">
<nobr>
<form action="{url idSite=null}" method="get" id="siteSelection">
<small>
	<strong>{$userLogin}</strong>
	| 
<a href='?module=CoreAdminHome'>Admin</a> |
<span id="sitesSelection">Site <select name="idSite" onchange='javascript:this.form.submit()'>
	<optgroup label="Sites">
	   {foreach from=$sites item=info}
	   		<option label="{$info.name}" value="{$info.idsite}" {if $idSite==$info.idsite} selected="selected"{/if}>{$info.name}</option>
	   {/foreach}
	</optgroup>
</select>
{hiddenurl idSite=null}
</span> | {if $userLogin=='anonymous'}<a href='?module=Login'>{'Login_LogIn'|translate}</a>{else}<a href='?module=Login&amp;action=logout'>{'Login_Logout'|translate}</a>{/if}
</small>
</form>
</nobr>
</div>
</div>

