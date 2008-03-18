<link rel="stylesheet" href="themes/default/common-admin.css">
<div style="width:520px;margin:auto;padding-top:40px;">

	<div id="logo">
		<h1>Piwik <span class="description"># open source web analytics</span></h1>
		
	</div>

<div style="border: 1px solid #ddd;padding:15px;">

{if $AccessErrorString}
<div class="access_error">{$AccessErrorString}</div>
{/if}

{if $form_data.errors}
	<div class="warning">
	<div style="float:left;margin-right:20px;margin-bottom:40px;"><img src="themes/default/images/warning_medium.png"></div>
	<div>
	<strong>Please fix the following errors:</strong>
	<ul>
	{foreach from=$form_data.errors item=data}
	<li>{$data}</li>
	{/foreach}
	</ul>
	</div>
	</div>
{/if}

{*
{if isset($form_text)}
<p>{$form_text}</p>
{/if}
*}

<form {$form_data.attributes}>
<!-- Output hidden fields -->

<!-- Display the fields -->
{foreach from=$element_list key=title item=data}
	<h3>{$title}</h3>
	
	<table>
	{foreach from=$data item=fieldname}
		{* normal form *}
		{if $form_data.$fieldname.label}
		<tr>
			<td>{$form_data.$fieldname.label}</td>
			<td>{$form_data.$fieldname.html}</td>
		
		{elseif $form_data.$fieldname.type == 'hidden'}
			{$form_data.$fieldname.html}
		{/if}</tr>
	{/foreach}
	</table>
	
{/foreach}

<div id="submit">
{$form_data.submit.html}
</div>

</form> 


</div>

</div>
