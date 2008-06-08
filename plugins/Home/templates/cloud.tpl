<div id="{$id}" class="parentDiv">
{literal}
<style>

#tagCloud{
	width:100%;
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
.valueIsZero {
	text-decoration: line-through;
}
span.size0, span.size0 a {
	color: #344971;
	font-size: 28px;
}
span.size1, span.size1 a {
	color: #344971;
	font-size: 24px;
}
span.size2, span.size2 a {
	color: #4B74AD;
	font-size:20px;
}
span.size3, span.size3 a {
	color: #A3A8B6;
	font-size: 16px;
}
span.size4, span.size4 a {
	color: #A3A8B6;
	font-size: 15px;
}
span.size5, span.size5 a {
	color: #A3A8B6;
	font-size: 14px;
}
span.size6, span.size6 a {
	color: #A3A8B6;
	font-size: 11px;
}
</style>
{/literal}

<div id="tagCloud">
{if count($cloudValues) == 0}
	<div id="emptyDatatable">No data for this tag cloud.</div>
{else}
	{foreach from=$cloudValues key=word item=value}
	<span title="{$value.word} ({$labelMetadata[$value.word].hits} hits)" class="word size{$value.size} {* we strike tags with 0 hits *} {if $labelMetadata[$value.word].hits == 0}valueIsZero{/if}">
	{if false !== $labelMetadata[$value.word].url}<a href="{$labelMetadata[$value.word].url}" target="_blank">{/if}
	{if false !== $labelMetadata[$value.word].logo}<img src="{$labelMetadata[$value.word].logo}" width="{$value.logoWidth}">{else}
	
	{$value.wordTruncated}{/if}{if false !== $labelMetadata[$value.word].url}</a>{/if}</span>
	{/foreach}
{/if}
{if $showFooter}
	{include file="Home/templates/datatable_footer.tpl"}
{/if}
{include file="Home/templates/datatable_js.tpl"}
</div>
</div>
