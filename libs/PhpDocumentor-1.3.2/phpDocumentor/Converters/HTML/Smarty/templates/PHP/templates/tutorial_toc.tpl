{if count($toc)}
<h1 align="center">Table of Contents</h1>
<ul>
{section name=toc loop=$toc}
{if $toc[toc].tagname == 'refsect1'}
{assign var="context" value="refsect1"}
{$toc[toc].link}<br />
{/if}
{if $toc[toc].tagname == 'refsect2'}
{assign var="context" value="refsect2"}
&nbsp;&nbsp;&nbsp;{$toc[toc].link}<br />
{/if}
{if $toc[toc].tagname == 'refsect3'}
{assign var="context" value="refsect3"}
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$toc[toc].link}<br />
{/if}
{if $toc[toc].tagname == 'table'}
{if $context == 'refsect2'}&nbsp;&nbsp;&nbsp;{/if}
{if $context == 'refsect3'}&nbsp;&nbsp;&nbsp;{/if}
Table: {$toc[toc].link}
{/if}
{if $toc[toc].tagname == 'example'}
{if $context == 'refsect2'}&nbsp;&nbsp;&nbsp;{/if}
{if $context == 'refsect3'}&nbsp;&nbsp;&nbsp;{/if}
Table: {$toc[toc].link}
{/if}
{/section}
</ul>
{/if}