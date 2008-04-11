{if count($toc)}
<h1 class="title">Table of Contents</h1>
<ul class="toc">
	{assign var="lastcontext" value='refsect1'}
	{section name=toc loop=$toc}
		
		{if $toc[toc].tagname != $lastcontext}
		  {if $lastcontext == 'refsect1'}
				<ul class="toc">
					<li>{$toc[toc].link}</li>
			{else}
				{if $lastcontext == 'refsect2'}
					{if $toc[toc].tagname == 'refsect1'}
						</ul>
						<li>{$toc[toc].link}</li>
					{/if}
					{if $toc[toc].tagname == 'refsect3'}
						<ul class="toc">
							<li>{$toc[toc].link}</li>
					{/if}
				{else}
					</ul>
					<li>{$toc[toc].link}</li>
				{/if}
			{/if}
			{assign var="lastcontext" value=$toc[toc].tagname}
		{else}
			<li>{$toc[toc].link}</li>
		{/if}
	{/section}
	{if $lastcontext == 'refsect2'}
		</ul>
	{/if}
	{if $lastcontext == 'refsect3'}
			</ul>
		</ul>
	{/if}
</ul>
{/if}
