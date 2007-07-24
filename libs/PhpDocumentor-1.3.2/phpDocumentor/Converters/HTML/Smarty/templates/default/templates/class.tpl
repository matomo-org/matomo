{include file="header.tpl" eltype="class" hasel=true contents=$classcontents}

{if $conflicts.conflict_type}<p class="warning">Conflicts with classes:<br />
	{section name=me loop=$conflicts.conflicts}
	{$conflicts.conflicts[me]}<br />
	{/section}
<p>
	{/if}

<div class="leftcol">
	<h3><a href="#class_details">{if $is_interface}Interface{else}Class{/if} Overview</a> <span class="smalllinenumber">[line {if $class_slink}{$class_slink}{else}{$line_number}{/if}]</span></h3>
	<div id="classTree"><pre>{section name=tree loop=$class_tree.classes}{$class_tree.classes[tree]}{$class_tree.distance[tree]}{/section}</pre>
</div>
	<div class="small">
	<p>{$sdesc|default:''}</p>
	{if $tutorial}
	<h4 class="classtutorial">{if $is_interface}Interface{else}Class{/if} Tutorial:</h4>
	<ul>
		<li>{$tutorial}</li>
	</ul>
	{/if}
	<h4>Author(s):</h4>
	<ul>
		{section name=tag loop=$tags}
			{if $tags[tag].keyword eq "author"}
			<li>{$tags[tag].data}</li>
			{/if}
		{/section}
	</ul>
	<h4>Version:</h4>
	<ul>
		{section name=tag loop=$tags}
			{if $tags[tag].keyword eq "version"}
			<li>{$tags[tag].data}</li>
			{/if}
		{/section}
	</ul>

	<h4>Copyright:</h4>
	<ul>
		{section name=tag loop=$tags}
			{if $tags[tag].keyword eq "copyright"}
			<li>{$tags[tag].data}</li>
			{/if}
		{/section}
	</li>
	</div>
</div>

<div class="middlecol">
	<h3><a href="#class_vars">Variables</a></h3>
	<ul class="small">
		{section name=contents loop=$contents.var}
		<li>{$contents.var[contents]}</li>
		{/section}
	</ul>
	<h3><a href="#class_consts">Constants</a></h3>
	<ul class="small">
		{section name=contents loop=$contents.const}
		<li>{$contents.const[contents]}</li>
		{/section}
	</ul>
</div>
<div class="rightcol">
	<h3><a href="#class_methods">Methods</a></h3>
	<ul class="small">
		{section name=contents loop=$contents.method}
		<li>{$contents.method[contents]}</li>
		{/section}
	</ul>
</div>

<div id="content">
<hr>
	<div class="contents">
{if $children}
	<h2>Child classes:</h2>
	{section name=kids loop=$children}
	<dl>
	<dt>{$children[kids].link}</dt>
		<dd>{$children[kids].sdesc}</dd>
	</dl>
	{/section}</p>
{/if}
	</div>

	<div class="leftCol">
    {if $implements}
    <h2>Implements interfaces</h2>
    <ul>
        {foreach item="int" from=$implements}<li>{$int}</li>{/foreach}
    </ul>
    {/if}
	<h2>Inherited Variables</h2>
	{section name=ivars loop=$ivars}
		<div class="indent">
		<h3>Class: {$ivars[ivars].parent_class}</h3>
		<div class="small">
			<dl>
			{section name=ivars2 loop=$ivars[ivars].ivars}
			<dt>
				{$ivars[ivars].ivars[ivars2].link}
			</dt>
			<dd>
				{$ivars[ivars].ivars[ivars2].ivars_sdesc} 
			</dd>
			{/section}
			</dl>
		</div>
		</div>
	{/section}
	<h2>Inherited Constants</h2>
	{section name=iconsts loop=$iconsts}
		<div class="indent">
		<h3>Class: {$iconsts[iconsts].parent_class}</h3>
		<div class="small">
			<dl>
			{section name=iconsts2 loop=$iconsts[iconsts].iconsts}
			<dt>
				{$iconsts[iconsts].iconsts[iconsts2].link}
			</dt>
			<dd>
				{$iconsts[iconsts].iconsts[iconsts2].iconsts_sdesc} 
			</dd>
			{/section}
			</dl>
		</div>
		</div>
	{/section}
	</div>

	<div class="rightCol">
	<h2>Inherited Methods</h2>
	{section name=imethods loop=$imethods}
		<div class="indent">
		<h3>Class: {$imethods[imethods].parent_class}</h3>
		<dl class="small">
			{section name=im2 loop=$imethods[imethods].imethods}
			<dt>
				{$imethods[imethods].imethods[im2].link}
			</dt>
			<dd>
				{$imethods[imethods].imethods[im2].sdesc}
			</dd>
		{/section}
		</dl>
		</div>
	{/section}
	</div>
	<br clear="all">
	<hr>

	<a name="class_details"></a>
	<h2>Class Details</h2>
	{include file="docblock.tpl" type="class" sdesc=$sdesc desc=$desc}
	<p class="small" style="color: #334B66;">[ <a href="#top">Top</a> ]</p>

	<hr>
	<a name="class_vars"></a>
	<h2>Class Variables</h2>
	{include file="var.tpl"}

	<hr>
	<a name="class_methods"></a>
	<h2>Class Methods</h2>
	{include file="method.tpl"}

	<hr>
	<a name="class_consts"></a>
	<h2>Class Constants</h2>
	{include file="const.tpl"}
</div>
{include file="footer.tpl"}
