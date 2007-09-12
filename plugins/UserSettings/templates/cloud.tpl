<div id="{$id}" class="parentDiv">
{literal}
<style>
<!--
#tagCloud{
	width:500px;
}
img {
	border:0;
}
.word a {
	text-decoration:none;
}
.word {
	padding: 4px 4px 4px 4px;
}
span.size0, span.size0 a {
	color: #344971;
	font-size: 2em;
}
span.size1, span.size1 a {
	color: #344971;
	font-size: 1.5em;
}
span.size2, span.size2 a {
	color: #4B74AD;
	font-size:1.2em;
}
span.size3, span.size3 a {
	color: #A3A8B6;
	font-size: 1.1em;
}
span.size4, span.size4 a {
	color: #A3A8B6;
	font-size: 1em;
}
span.size5, span.size5 a {
	color: #A3A8B6;
	font-size: 0.95em;
}
span.size6, span.size6 a {
	color: #A3A8B6;
	font-size: 0.9em;
}
//-->
</style>
{/literal}

<div id="tagCloud">
{if count($cloudValues) == 0}
	No data for this tag cloud
{else}
	{foreach from=$cloudValues key=word item=value}
	<span title="{$value.word} ({$labelDetails[$value.word].hits} hits)" class="word size{$value.size}">
	{if false !== $labelDetails[$value.word].url}<a href="{$labelDetails[$value.word].url}" target="_blank">{/if}
	{if false !== $labelDetails[$value.word].logo}<img src="{$labelDetails[$value.word].logo}" width="{$value.logoWidth}">{else}
	{$value.wordTruncated}{/if}{if false !== $labelDetails[$value.word].url}</a>{/if}
	</span>
	{/foreach}
{/if}
{include file="UserSettings/templates/datatable_footer.tpl"}
</div>
</div>