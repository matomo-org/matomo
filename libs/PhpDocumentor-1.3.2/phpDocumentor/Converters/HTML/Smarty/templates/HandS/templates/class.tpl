{include file="header.tpl" eltype="class" hasel=true contents=$classcontents}

<h2 class="class-name">{if $is_interface}Interface{else}Class{/if} {$class_name}</h2>

<a name="sec-description"></a>
<div class="info-box">
	<div class="info-box-title">{if $is_interface}Interface{else}Class{/if} Overview</div>
	<div class="nav-bar">
		{if $children || $vars || $ivars || $methods || $imethods || $consts || $iconsts}
			<span class="disabled">{if $is_interface}Interface{else}Class{/if} Overview</span> |
		{/if}
		{if $children}
			<a href="#sec-descendents">Descendants</a>
			{if $vars || $ivars || $methods || $imethods || $consts || $iconsts}|{/if}
		{/if}
		
		{if $ivars || $imethods}
			<a href="#sec-inherited">Inherited Properties, Constants, and Methods</a>
			{if $vars || $ivars || $methods || $imethods || $consts || $iconsts}|{/if}
		{/if}
		{if $vars || $ivars}
			{if $vars}
				<a href="#sec-var-summary">Properties Summary</a> | <a href="#sec-vars">Properties Detail</a>
			{else}
				<a href="#sec-vars">Properties</a>
			{/if}
			{if $methods || $imethods}|{/if}
		{/if}
		{if $methods || $imethods}
			{if $methods}
				<a href="#sec-method-summary">Method Summary</a> | <a href="#sec-methods">Methods Detail</a>
			{else}
				<a href="#sec-methods">Methods</a>
			{/if}
			{if $consts || $iconsts}|{/if}
		{/if}
		{if $consts || $iconsts}
			{if $consts}
				<a href="#sec-const-summary">Constants Summary</a> | <a href="#sec-consts">Constants Detail</a>
			{else}
				<a href="#sec-consts">Constants</a>
			{/if}
		{/if}
	</div>
	<div class="info-box-body">
		<table width="100%" border="0">
		<tr><td valign="top" width="60%" class="class-overview">

        {if $implements}
        <p class="implements">
            Implements interfaces:
            <ul>
                {foreach item="int" from=$implements}<li>{$int}</li>{/foreach}
            </ul>
        </p>
        {/if}
		{include file="docblock.tpl" type="class" sdesc=$sdesc desc=$desc}

		<p class="notes">
			Located in <a class="field" href="{$page_link}">{$source_location}</a> [<span class="field">line {if $class_slink}{$class_slink}{else}{$line_number}{/if}</span>]
		</p>

		{if $tutorial}
			<hr class="separator" />
			<div class="notes">Tutorial: <span class="tutorial">{$tutorial}</div>
		{/if}

		<pre>{section name=tree loop=$class_tree.classes}{$class_tree.classes[tree]}{$class_tree.distance[tree]}{/section}</pre>

		{if $conflicts.conflict_type}
			<hr class="separator" />
			<div><span class="warning">Conflicts with classes:</span><br />
			{section name=me loop=$conflicts.conflicts}
				{$conflicts.conflicts[me]}<br />
			{/section}
			</div>
		{/if}

		{if count($tags) > 0}
		<strong>Author(s):</strong>
		<ul>
		  {section name=tag loop=$tags}
			 {if $tags[tag].keyword eq "author"}
			 <li>{$tags[tag].data}</li>
			 {/if}
		  {/section}
		</ul>
		{/if}

		{include file="classtags.tpl" tags=$tags}
		</td>

		{if count($contents.var) > 0}
		<td valign="top" width="20%" class="class-overview">
		<p align="center" class="short-description"><strong><a href="#sec_vars">Properties</a></strong></p>
		<ul>
		  {section name=contents loop=$contents.var}
		  <li>{$contents.var[contents]}</li>
		  {/section}
		</ul>
		</td>
		{/if}

		{if count($contents.method) > 0}
		<td valign="top" width="20%" class="class-overview">
		<p align="center" class="short-description"><strong><a href="#sec_methods">Methods</a></strong></p>
		<ul>
		  {section name=contents loop=$contents.method}
		  <li>{$contents.method[contents]}</li>
		  {/section}
		</ul>
		</td>
		{/if}

		</tr></table>
		<div class="top">[ <a href="#top">Top</a> ]</div>
	</div>
</div>

{if $children}
	<a name="sec-descendents"></a>
	<div class="info-box">
		<div class="info-box-title">Direct descendents</div>
		<div class="nav-bar">
			<a href="#sec-description">Class Overview</a> |
			{if $children}
				<a href="#sec-descendents">Descendents</a>
				{if $vars || $ivars || $methods || $imethods}|{/if}
			{/if}
			{if $ivars || $imethods}
				<a href="#sec-inherited">Inherited Properties and Methods</a>
				{if $vars || $ivars || $methods || $imethods}|{/if}
			{/if}
		{if $vars || $ivars}
			{if $vars}
				<a href="#sec-var-summary">Properties Summary</a> | <a href="#sec-vars">Properties Detail</a>
			{else}
				<a href="#sec-vars">Properties</a>
			{/if}
			{if $methods || $imethods}|{/if}
		{/if}
		{if $methods || $imethods}
			{if $methods}
				<a href="#sec-method-summary">Method Summary</a> | <a href="#sec-methods">Methods Detail</a>
			{else}
				<a href="#sec-methods">Methods</a>
			{/if}
            {if $consts || $iconsts}|{/if}
		{/if}
		{if $consts || $iconsts}
			{if $consts}
				<a href="#sec-const-summary">Constants Summary</a> | <a href="#sec-consts">Constants Detail</a>
			{else}
				<a href="#sec-consts">Constants</a>
			{/if}
		{/if}
		</div>
		<div class="info-box-body">
			<table cellpadding="2" cellspacing="0" class="class-table">
				<tr>
					<th class="class-table-header">Child Class</th>
					<th class="class-table-header">Description</th>
				</tr>
				{section name=kids loop=$children}
				<tr>
					<td style="padding-right: 2em">{$children[kids].link}</td>
					<td>
					{if $children[kids].sdesc}
						{$children[kids].sdesc}
					{else}
						{$children[kids].desc}
					{/if}
					</td>
				</tr>
				{/section}
			</table>
			<br /><div class="top">[ <a href="#top">Top</a> ]</div>
		</div>
	</div>
{/if}

{if $ivars || $imethods || $iconsts}
	<a name="sec-inherited"></a>
	<div class="info-box">
		<div class="info-box-title">Inherited Properties, Constants, and Methods</div>
		<div class="nav-bar">
			<a href="#sec-description">Class Overview</a> |
			{if $children}
				<a href="#sec-descendents">Descendants</a> |
			{/if}
			{if $vars || $ivars || $methods || $imethods || $iconsts || $consts}|{/if}
			<span class="disabled">Inherited Properties, Constants, and Methods</span>
			{if $vars || $ivars || $methods || $imethods || $consts || $iconsts}|{/if}
			{if $vars || $ivars}
				{if $vars}
					<a href="#sec-var-summary">Properties Summary</a> | <a href="#sec-vars">Properties Detail</a>
				{else}
					<a href="#sec-vars">Properties</a>
				{/if}
				{if $methods || $imethods}|{/if}
			{/if}
			{if $methods || $imethods}
				{if $methods}
					<a href="#sec-method-summary">Method Summary</a> | <a href="#sec-methods">Methods Detail</a>
				{else}
					<a href="#sec-methods">Methods</a>
				{/if}
				{if $consts || $iconsts}|{/if}
			{/if}
    		{if $consts || $iconsts}
    			{if $consts}
    				<a href="#sec-const-summary">Constants Summary</a> | <a href="#sec-consts">Constants Detail</a>
    			{else}
    				<a href="#sec-consts">Constants</a>
    			{/if}
    		{/if}
		</div>
		<div class="info-box-body">
			<table cellpadding="2" cellspacing="0" class="class-table">
				<tr>
					<th class="class-table-header" width="30%">Inherited Properties</th>
					<th class="class-table-header" width="40%">Inherited Methods</th>
					<th class="class-table-header" width="30%">Inherited Constants</th>
				</tr>
				<tr>
					<td width="30%">
						{section name=ivars loop=$ivars}
							<p>Inherited From <span class="classname">{$ivars[ivars].parent_class}</span></p>
							<blockquote>
								<dl>
									{section name=ivars2 loop=$ivars[ivars].ivars}
										<dt>
											<span class="method-definition">{$ivars[ivars].ivars[ivars2].link}</span>
										</dt>
										<dd>
											<span class="method-definition">{$ivars[ivars].ivars[ivars2].ivars_sdesc}</span>
										</dd>
									{/section}
								</dl>
							</blockquote>
						{/section}
					</td>
					<td width="40%">
						{section name=imethods loop=$imethods}
							<p>Inherited From <span class="classname">{$imethods[imethods].parent_class}</span></p>
							<blockquote>
								<dl>
									{section name=im2 loop=$imethods[imethods].imethods}
										<dt>
											<span class="method-definition">{$imethods[imethods].imethods[im2].link}</span>
										</dt>
										<dd>
											<span class="method-definition">{$imethods[imethods].imethods[im2].sdesc}</span>
										</dd>
									{/section}
								</dl>
							</blockquote>
						{/section}
					</td>
					<td width="30%">
						{section name=iconsts loop=$iconsts}
							<p>Inherited From <span class="classname">{$iconsts[iconsts].parent_class}</span></p>
							<blockquote>
								<dl>
									{section name=iconsts2 loop=$iconsts[iconsts].iconsts}
										<dt>
											<span class="method-definition">{$iconsts[iconsts].iconsts[iconsts2].link}</span>
										</dt>
										<dd>
											<span class="method-definition">{$iconsts[iconsts].iconsts[iconsts2].iconsts_sdesc}</span>
										</dd>
									{/section}
								</dl>
							</blockquote>
						{/section}
					</td>
				</tr>
			</table>
			<br /><div class="top">[ <a href="#top">Top</a> ]</div>
		</div>
	</div>
{/if}

{if $consts}
	<a name="sec-const-summary"></a>
	<div class="info-box">
		<div class="info-box-title">Constant Summary</span></div>
		<div class="nav-bar">
			<a href="#sec-description">Class Overview</a> |
			{if $children}
				<a href="#sec-descendents">Descendants</a> |
			{/if}
			{if $ivars || $imethods || $iconsts}
				<a href="#sec-inherited">Inherited Properties, Constants, and Methods</a>
				{if $vars || $ivars || $methods || $imethods || $consts || $iconsts}|{/if}
			{/if}
			<span class="disabled">Constants Summary</span> | <a href="#sec-consts">Constants Detail</a>
			{if $vars || $ivars}
				{if $vars}
					<a href="#sec-var-summary">Properties Summary</a> | <a href="#sec-vars">Properties Detail</a>
				{else}
					<a href="#sec-vars">Properties</a>
				{/if}
				{if $methods || $imethods}|{/if}
			{/if}
			{if $methods || $imethods}
				|
				{if $methods}
					<a href="#sec-method-summary">Method Summary</a> | <a href="#sec-methods">Methods Detail</a>
				{else}
					<a href="#sec-methods">Methods</a>
				{/if}
			{/if}
		</div>
		<div class="info-box-body">
			<div class="const-summary">
			<table border="0" cellspacing="0" cellpadding="0" class="var-summary">
			{section name=consts loop=$consts}
				<div class="var-title">
					<tr>
					<td class="var-title"><a href="#{$consts[consts].const_dest}" title="details" class="const-name-summary">{$consts[consts].const_name}</a>&nbsp;&nbsp;</td>
					<td class="const-summary-description">{$consts[consts].sdesc}</td></tr>
				</div>
				{/section}
				</table>
			</div>
			<br /><div class="top">[ <a href="#top">Top</a> ]</div>
		</div>
	</div>
{/if}

{if $vars}
	<a name="sec-var-summary"></a>
	<div class="info-box">
		<div class="info-box-title">Property Summary</span></div>
		<div class="nav-bar">
			<a href="#sec-description">Class Overview</a> |
			{if $children}
				<a href="#sec-descendents">Descendents</a> |
			{/if}
			{if $ivars || $imethods || $iconsts}
				<a href="#sec-inherited">Inherited Properties and Methods</a>
				{if $vars || $ivars || $methods || $imethods}|{/if}
			{/if}
			<span class="disabled">Property Summary</span> | <a href="#sec-vars">Properties Detail</a>
			{if $methods || $imethods}
				{if $methods}
					<a href="#sec-method-summary">Method Summary</a> | <a href="#sec-methods">Methods Detail</a>
				{else}
					<a href="#sec-methods">Methods</a>
				{/if}
				{if $consts || $iconsts}|{/if}
			{/if}
    		{if $consts || $iconsts}
    			{if $consts}
    				<a href="#sec-const-summary">Constants Summary</a> | <a href="#sec-consts">Constants Detail</a>
    			{else}
    				<a href="#sec-consts">Constants</a>
    			{/if}
    		{/if}
		</div>
		<div class="info-box-body">
			<div class="var-summary">
			<table border="0" cellspacing="0" cellpadding="0" class="var-summary">
			{section name=vars loop=$vars}
			{if $vars[vars].static}
				<div class="var-title">
					<tr><td class="var-title">static <span class="var-type-summary">{$vars[vars].var_type}</span>&nbsp;&nbsp;</td>
					<td class="var-title"><a href="#{$vars[vars].var_name}" title="details" class="var-name-summary">{$vars[vars].var_name}</a>&nbsp;&nbsp;</td>
					<td class="var-summary-description">{$vars[vars].sdesc}</td></tr>
				</div>
			{/if}
			{/section}
			{section name=vars loop=$vars}
			{if !$vars[vars].static}
				<div class="var-title">
					<tr><td class="var-title"><span class="var-type-summary">{$vars[vars].var_type}</span>&nbsp;&nbsp;</td>
					<td class="var-title"><a href="#{$vars[vars].var_name}" title="details" class="var-name-summary">{$vars[vars].var_name}</a>&nbsp;&nbsp;</td>
					<td class="var-summary-description">{$vars[vars].sdesc}</td></tr>
				</div>
			{/if}
			{/section}
				</table>
			</div>
			<br /><div class="top">[ <a href="#top">Top</a> ]</div>
		</div>
	</div>
{/if}

{if $methods}
	<a name="sec-method-summary"></a>
	<div class="info-box">
		<div class="info-box-title">Method Summary</span></div>
		<div class="nav-bar">
			<a href="#sec-description">Class Overview</a> |
			{if $children}
				<a href="#sec-descendents">Descendents</a> |
			{/if}
			{if $ivars || $imethods || $iconsts}
				<a href="#sec-inherited">Inherited Properties and Methods</a>
				{if $vars || $ivars || $methods || $imethods}|{/if}
			{/if}
			{if $vars || $ivars}
				{if $vars}
					<a href="#sec-var-summary">Properties Summary</a> | <a href="#sec-vars">Properties Detail</a>
				{else}
					<a href="#sec-vars">Properties</a>
				{/if}
				{if $consts || $iconsts}|{/if}
			{/if}
    		{if $consts || $iconsts}
    			{if $consts}
    				<a href="#sec-const-summary">Constants Summary</a> | <a href="#sec-consts">Constants Detail</a>
    			{else}
    				<a href="#sec-consts">Constants</a>
    			{/if}
    		{/if}
			<span class="disabled">Method Summary</span> | <a href="#sec-methods">Methods Detail</a>
		</div>
		<div class="info-box-body">
			<div class="method-summary">
				<table border="0" cellspacing="0" cellpadding="0" class="method-summary">
				{section name=methods loop=$methods}
				{if $methods[methods].static}
				<div class="method-definition">
					<tr><td class="method-definition">static
					{if $methods[methods].function_return}
						<span class="method-result">{$methods[methods].function_return}</span>&nbsp;&nbsp;
					{/if}</td>
					<td class="method-definition"><a href="#{$methods[methods].function_name}" title="details" class="method-name">{if $methods[methods].ifunction_call.returnsref}&amp;{/if}{$methods[methods].function_name}</a>()&nbsp;&nbsp;</td>
					<td class="method-definition">{$methods[methods].sdesc}</td></tr>
				</div>
				{/if}
				{/section}
				{section name=methods loop=$methods}
				{if !$methods[methods].static}
				<div class="method-definition">
					{if $methods[methods].function_return}
						<tr><td class="method-definition"><span class="method-result">{$methods[methods].function_return}</span>&nbsp;&nbsp;</td>
					{/if}
					<td class="method-definition"><a href="#{$methods[methods].function_name}" title="details" class="method-name">{if $methods[methods].ifunction_call.returnsref}&amp;{/if}{$methods[methods].function_name}</a>()&nbsp;&nbsp;</td>
					<td class="method-definition">{$methods[methods].sdesc}</td></tr>
				</div>
				{/if}
				{/section}
				</table>
			</div>
			<br /><div class="top">[ <a href="#top">Top</a> ]</div>
		</div>
	</div>
{/if}

{if $vars || $ivars}
	<a name="sec-vars"></a>
	<div class="info-box">
		<div class="info-box-title">Properties</div>
		<div class="nav-bar">
			<a href="#sec-description">Class Overview</a> |
			{if $children}
				<a href="#sec-descendents">Descendents</a> |
			{/if}
			{if $ivars || $imethods || $iconsts}
				<a href="#sec-inherited">Inherited Properties and Methods</a>
				{if $vars || $ivars || $methods || $imethods}|{/if}
			{/if}
			{if $methods}
				<a href="#sec-var-summary">Property Summary</a> | <a href="#sec-vars">Properties Detail</a>
			{else}
				<span class="disabled">Properties</span>
			{/if}

			{if $methods || $imethods}
				|
				{if $methods}
					<a href="#sec-method-summary">Method Summary</a> | <a href="#sec-methods">Methods Detail</a>
				{else}
					<a href="#sec-methods">Methods</a>
				{/if}
			{/if}
			{if $consts || $iconsts}
				|
				{if $consts}
					<a href="#sec-const-summary">Constants Summary</a> | <a href="#sec-consts">Constants Detail</a>
				{else}
					<a href="#sec-consts">Constants</a>
				{/if}
			{/if}
		</div>
		<div class="info-box-body">
			{include file="var.tpl"}
		</div>
	</div>
{/if}

{if $methods || $imethods}
	<a name="sec-methods"></a>
	<div class="info-box">
		<div class="info-box-title">Methods</div>
		<div class="nav-bar">
			<a href="#sec-description">Class Overview</a> |
			{if $children}
				<a href="#sec-descendents">Descendents</a>
			{/if}
			{if $ivars || $imethods || $iconsts}
				<a href="#sec-inherited">Inherited Properties and Methods</a>
				{if $vars || $ivars || $methods || $imethods}|{/if}
			{/if}
			{if $vars || $ivars}
                |
				{if $vars}
					<a href="#sec-var-summary">Property Summary</a> | <a href="#sec-vars">Properties Detail</a>
				{else}
					<a href="#sec-vars">Properties</a>
				{/if}
			{/if}
			{if $consts || $iconsts}
				|
				{if $consts}
					<a href="#sec-const-summary">Constants Summary</a> | <a href="#sec-consts">Constants Detail</a>
				{else}
					<a href="#sec-consts">Constants</a>
				{/if}
			{/if}
			{if $methods}
				| <a href="#sec-method-summary">Method Summary</a> | <span class="disabled">Methods Detail</span>
			{else}
				| <span class="disabled">Methods</span>
			{/if}
		</div>
		<div class="info-box-body">
			{include file="method.tpl"}
		</div>
	</div>
{/if}

{if $consts || $consts}
	<a name="sec-consts"></a>
	<div class="info-box">
		<div class="info-box-title">Constants</div>
		<div class="nav-bar">
			<a href="#sec-description">Class Overview</a> |
			{if $children}
				<a href="#sec-descendents">Descendants</a>
			{/if}
			{if $consts}
				| <a href="#sec-const-summary">Constants Summary</a> | <span class="disabled">Constants Detail</span>
			{else}
				| <span class="disabled">Constants</span>
			{/if}
			{if $ivars || $imethods || $iconsts}
				<a href="#sec-inherited">Inherited Properties, Constants, and Methods</a>
				{if $vars || $ivars || $methods || $imethods || $consts || $iconsts} | {/if}
			{/if}

			{if $methods || $imethods}
				|
				{if $methods}
					<a href="#sec-method-summary">Method Summary</a> | <a href="#sec-methods">Methods Detail</a>
				{else}
					<a href="#sec-methods">Methods</a>
				{/if}
			{/if}
		</div>
		<div class="info-box-body">
			{include file="const.tpl"}
		</div>
	</div>
{/if}

{include file="footer.tpl"}