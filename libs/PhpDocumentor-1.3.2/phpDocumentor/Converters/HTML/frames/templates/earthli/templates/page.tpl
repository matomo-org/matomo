{include file="header.tpl" top3=true}

<h2 class="file-name"><img src="{$subdir}media/images/Page_logo.png" alt="File" style="vertical-align: middle">{$source_location}</h2>

<a name="sec-description"></a>
<div class="info-box">
	<div class="info-box-title">Description</div>
	<div class="nav-bar">
		{if $classes || $includes || $defines || $globals || $functions}
			<span class="disabled">Description</span> |
		{/if}
		{if $classes}
			<a href="#sec-classes">Classes</a>
			{if $includes || $defines || $globals || $functions}|{/if}
		{/if}
		{if $includes}
			<a href="#sec-includes">Includes</a>
			{if $defines || $globals || $functions}|{/if}
		{/if}
		{if $defines}
			<a href="#sec-constants">Constants</a>
			{if $globals || $functions}|{/if}
		{/if}
		{if $globals}
			<a href="#sec-variables">Variables</a>
			{if $functions}|{/if}
		{/if}
		{if $functions}
			<a href="#sec-functions">Functions</a>
		{/if}
	</div>
	<div class="info-box-body">	
		{include file="docblock.tpl" desc=$desc sdesc=$sdesc tags=$tags}
		
		{if $tutorial}
			<hr class="separator" />
			<div class="notes">Tutorial: <span class="tutorial">{$tutorial}</div>
		{/if}
	</div>
</div>
		
{if $classes}
	<a name="sec-classes"></a>	
	<div class="info-box">
		<div class="info-box-title">Classes</div>
		<div class="nav-bar">
			<a href="#sec-description">Description</a> |
			<span class="disabled">Classes</span>
			{if $includes || $defines || $globals || $functions}|{/if}
			{if $includes}
				<a href="#sec-includes">Includes</a>
				{if $defines || $globals || $functions}|{/if}
			{/if}
			{if $defines}
				<a href="#sec-constants">Constants</a>
				{if $globals || $functions}|{/if}
			{/if}
			{if $globals}
				<a href="#sec-variables">Variables</a>
				{if $functions}|{/if}
			{/if}
			{if $functions}
				<a href="#sec-functions">Functions</a>
			{/if}
		</div>
		<div class="info-box-body">	
			<table cellpadding="2" cellspacing="0" class="class-table">
				<tr>
					<th class="class-table-header">Class</th>
					<th class="class-table-header">Description</th>
				</tr>
				{section name=classes loop=$classes}
				<tr>
					<td style="padding-right: 2em; vertical-align: top; white-space: nowrap">
						<img src="{$subdir}media/images/{if $classes[classes].abstract}Abstract{/if}{if $classes[classes].access == 'private'}Private{/if}Class.png"
								 alt="{if $classes[classes].abstract}Abstract{/if}{if $classes[classes].access == 'private'}Private{/if} class"
								 title="{if $classes[classes].abstract}Abstract{/if}{if $classes[classes].access == 'private'}Private{/if} class"/>
						{$classes[classes].link}
					</td>
					<td>
					{if $classes[classes].sdesc}
						{$classes[classes].sdesc}
					{else}
						{$classes[classes].desc}
					{/if}
					</td>
				</tr>
				{/section}
			</table>
		</div>
	</div>
{/if}

{if $includes}
	<a name="sec-includes"></a>	
	<div class="info-box">
		<div class="info-box-title">Includes</div>
		<div class="nav-bar">
			<a href="#sec-description">Description</a> |
			{if $classes}
				<a href="#sec-classes">Classes</a>
				{if $includes || $defines || $globals || $functions}|{/if}
			{/if}
			<span class="disabled">Includes</span>
			{if $defines || $globals || $functions}|{/if}
			{if $defines}
				<a href="#sec-constants">Constants</a>
				{if $globals || $functions}|{/if}
			{/if}
			{if $globals}
				<a href="#sec-variables">Variables</a>
				{if $functions}|{/if}
			{/if}
			{if $functions}
				<a href="#sec-functions">Functions</a>
			{/if}
		</div>
		<div class="info-box-body">	
			{include file="include.tpl"}
		</div>
	</div>
{/if}
	
{if $defines}
	<a name="sec-constants"></a>	
	<div class="info-box">
		<div class="info-box-title">Constants</div>
		<div class="nav-bar">
			<a href="#sec-description">Description</a> |
			{if $classes}
				<a href="#sec-classes">Classes</a>
				{if $includes || $defines || $globals || $functions}|{/if}
			{/if}
			{if $includes}
				<a href="#sec-includes">Includes</a>
				{if $defines || $globals || $functions}|{/if}
			{/if}
			<span class="disabled">Constants</span>
			{if $globals || $functions}|{/if}
			{if $globals}
				<a href="#sec-variables">Variables</a>
				{if $functions}|{/if}
			{/if}
			{if $functions}
				<a href="#sec-functions">Functions</a>
			{/if}
		</div>
		<div class="info-box-body">	
			{include file="define.tpl"}
		</div>
	</div>
{/if}
	
{if $globals}
	<a name="sec-variables"></a>	
	<div class="info-box">
		<div class="info-box-title">Variables</div>
		<div class="nav-bar">
			<a href="#sec-description">Description</a> |
			{if $classes}
				<a href="#sec-classes">Classes</a>
				{if $includes || $defines || $globals || $functions}|{/if}
			{/if}
			{if $includes}
				<a href="#sec-includes">Includes</a>
				{if $defines || $globals || $functions}|{/if}
			{/if}
			{if $defines}
				<a href="#sec-constants">Constants</a>
				{if $globals || $functions}|{/if}
			{/if}
			<span class="disabled">Variables</span>
			{if $functions}|{/if}
			{if $functions}
				<a href="#sec-functions">Functions</a>
			{/if}
		</div>
		<div class="info-box-body">	
			{include file="global.tpl"}
		</div>
	</div>
{/if}
	
{if $functions}
	<a name="sec-functions"></a>	
	<div class="info-box">
		<div class="info-box-title">Functions</div>
		<div class="nav-bar">
			<a href="#sec-description">Description</a> |
			{if $classes}
				<a href="#sec-classes">Classes</a>
				{if $includes || $defines || $globals || $functions}|{/if}
			{/if}
			{if $includes}
				<a href="#sec-includes">Includes</a>
				{if $defines || $globals || $functions}|{/if}
			{/if}
			{if $defines}
				<a href="#sec-constants">Constants</a>
				{if $globals || $functions}|{/if}
			{/if}
			{if $globals}
				<a href="#sec-variables">Variables</a>
				{if $functions}|{/if}
			{/if}
			<span class="disabled">Functions</span>
		</div>
		<div class="info-box-body">	
			{include file="function.tpl"}
		</div>
	</div>
{/if}
	
{include file="footer.tpl" top3=true}
