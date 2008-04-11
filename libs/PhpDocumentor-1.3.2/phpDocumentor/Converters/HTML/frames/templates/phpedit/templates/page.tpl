{include file="header.tpl" top3=true}
<h2>File: {$source_location}</h2>
<div class="tab-pane" id="tabPane1">
<script type="text/javascript">
tp1 = new WebFXTabPane( document.getElementById( "tabPane1" ) );
</script>

<div class="tab-page" id="Description">
<h2 class="tab">Description</h2>
{if $tutorial}
<div class="maintutorial">Main Tutorial: {$tutorial}</div>
{/if}
{include file="docblock.tpl" desc=$desc sdesc=$sdesc tags=$tags}
<!-- =========== Used Classes =========== -->
<A NAME='classes_summary'><!-- --></A>
<h3>Classes defined in this file</h3>

<TABLE CELLPADDING='3' CELLSPACING='0' WIDTH='100%' CLASS="border">
	<THEAD>
		<TR><TD STYLE="width:20%"><h4>CLASS NAME</h4></TD><TD STYLE="width: 80%"><h4>DESCRIPTION</h4></TD></TR>
	</THEAD>
	<TBODY>
		{section name=classes loop=$classes}
		<TR BGCOLOR='white' CLASS='TableRowColor'>
			<TD>{$classes[classes].link}</TD>
			<TD>{$classes[classes].sdesc}</TD>
		</TR>
		{/section}
	</TBODY>
</TABLE>
</div>
<script type="text/javascript">tp1.addTabPage( document.getElementById( "Description" ) );</script>
<div class="tab-page" id="tabPage1">
{include file="include.tpl"}
</div>
<div class="tab-page" id="tabPage2">
{include file="global.tpl"}
</div>
<div class="tab-page" id="tabPage3">
{include file="define.tpl"}
</div>
<div class="tab-page" id="tabPage4">
{include file="function.tpl"}
</div>
</div>
<script type="text/javascript">
//<![CDATA[

setupAllTabs();

//]]>
</script>
{include file="footer.tpl"}