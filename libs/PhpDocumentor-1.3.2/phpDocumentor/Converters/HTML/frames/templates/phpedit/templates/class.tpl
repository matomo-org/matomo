{include file="header.tpl" top3=true}
<!-- Start of Class Data -->
<h2>
	{if $is_interface}Interface{else}Class{/if} {$class_name}
</h2> (line <span class="linenumber">{if $class_slink}{$class_slink}{else}{$line_number}{/if}</span>)
<div class="tab-pane" id="tabPane1">
<script type="text/javascript">
tp1 = new WebFXTabPane( document.getElementById( "tabPane1" ));
</script>

<div class="tab-page" id="Description">
<h2 class="tab">Description</h2>
<pre>
{section name=tree loop=$class_tree.classes}{$class_tree.classes[tree]}{$class_tree.distance[tree]}{/section}
</pre>
{if $tutorial}
<div class="maintutorial">Class Tutorial: {$tutorial}</div>
{/if}
{if $conflicts.conflict_type}
	<div align="left"><span class="font10bold" style="color:#FF0000">Warning:</span> Conflicts with classes:<br />
	{section name=me loop=$conflicts.conflicts}
		{$conflicts.conflicts[me]}<br />
	{/section}
	</div>
{/if}
<p>
	<b><i>Located in File: <a href="{$page_link}">{$source_location}</a></i></b><br>
</p>
{include file="docblock.tpl" type="class" sdesc=$sdesc desc=$desc}
<br /><hr />
{if $children}
<span class="type">Classes extended from {$class_name}:</span>
 	{section name=kids loop=$children}
	<dl>
	<dt>{$children[kids].link}</dt>
		<dd>{$children[kids].sdesc}</dd>
	</dl>
	{/section}</p>
{/if}
</div>
<script type="text/javascript">tp1.addTabPage( document.getElementById( "Description" ) );</script>
<div class="tab-page" id="tabPage1">
{include file="var.tpl"}
</div>
<div class="tab-page" id="constantsTabpage">
{include file="const.tpl"}
</div>
<div class="tab-page" id="tabPage2">
{include file="method.tpl"}
</div>
<div class="tab-page" id="iVars">
<h2 class="tab">Inherited Variables</h2>
<script type="text/javascript">tp1.addTabPage( document.getElementById( "iVars" ) );</script>
<!-- =========== VAR INHERITED SUMMARY =========== -->
<A NAME='var_inherited_summary'><!-- --></A>
<h3>Inherited Class Variable Summary</h3>

	{section name=ivars loop=$ivars}
	<!-- =========== Summary =========== -->
	<h4>Inherited From Class {$ivars[ivars].parent_class}</h4>
	{section name=ivars2 loop=$ivars[ivars].ivars}
	<h4>
<img src="{$subdir}media/images/PublicProperty.gif" border="0" /><strong class="property"> {$ivars[ivars].ivars[ivars2].link}</strong> - {$ivars[ivars].ivars[ivars2].sdesc}
	</h4> 
	{/section}
	{/section}
</div>
<div class="tab-page" id="iMethods">
<h2 class="tab">Inherited Methods</h2>
<script type="text/javascript">tp1.addTabPage( document.getElementById( "iMethods" ) );</script>
<!-- =========== INHERITED METHOD SUMMARY =========== -->
<A NAME='functions_inherited'><!-- --></A>
<h3>Inherited Method Summary</h3>

	{section name=imethods loop=$imethods}
	<!-- =========== Summary =========== -->
	<h4>Inherited From Class {$imethods[imethods].parent_class}</h4>
		{section name=im2 loop=$imethods[imethods].imethods}
		<h4>
<img src="{$subdir}media/images/{if $imethods[imethods].imethods[im2].constructor}Constructor{elseif $imethods[imethods].imethods[im2].destructor}Destructor{else}PublicMethod{/if}.gif" border="0" /><strong class="method"> {$imethods[imethods].imethods[im2].link}</strong> - {$imethods[imethods].imethods[im2].sdesc}
		</h4> 

		{/section}
		<br />
	{/section}
</div>
</div>
<script type="text/javascript">
//<![CDATA[

setupAllTabs();

//]]>
</script>
{include file="footer.tpl"}
