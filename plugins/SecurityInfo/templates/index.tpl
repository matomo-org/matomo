{assign var=showSitesSelection value=false}
{assign var=showPeriodSelection value=false}
{include file="CoreAdminHome/templates/header.tpl"}
{loadJavascriptTranslations plugins='SecurityInfo'}
{include file="CoreAdminHome/templates/menu.tpl"}

<h2>{'SecurityInfo_SecurityInformation'|translate}</h2>
<p>{'SecurityInfo_PluginDescription'|translate:"<a target='_blank' href='misc/redirectToUrl.php?url=http://phpsec.org/'>PHP Security Consortium</a>"}</p>

<div style="max-width:980px;">
{foreach from=$results.test_results key=i item=section}
<h2>{$i}</h2>
<table class="adminTable">
	<thead>
		<tr>
		<th>{'SecurityInfo_Test'|translate}</th>
		<th>{'SecurityInfo_Result'|translate}</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$section key=j item=test}
		<tr>
			<td>{$j}</td>
			<td style="{if $test.result==-1}background-color:green;color:white;{elseif $test.result==-2}background-color:yellow;color:black;{else if $test.result=--4}background-color:red;color:white;{/if}">{$test.message}</td>
		</tr>
		{/foreach}
	</tbody>
</table>
{/foreach}
</div>

{include file="CoreAdminHome/templates/footer.tpl"}
