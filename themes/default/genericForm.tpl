
{if $form_data.errors}
	<div class="warning">
		<img src="themes/default/images/warning_medium.png">
	<strong>Please fix the following errors:</strong>
	<ul>
	{foreach from=$form_data.errors item=data}
	<li>{$data}</li>
	{/foreach}
	</ul>	
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
	<div class="centrer">
	<table class="centrer">
	{foreach from=$data item=fieldname}
		{* normal form *}
		{if $form_data.$fieldname.label}
		<tr>
			<td>{$form_data.$fieldname.label}</td>
			<td>{$form_data.$fieldname.html}</td>
		</tr>
		{elseif  $form_data.$fieldname.type == 'hidden'}
			<tr><td colspan=2>{$form_data.$fieldname.html}</td></tr>
		{* radio form 
		{else}
			{foreach from=$form_data.$fieldname key=key item=radio}
			<tr>
				<td>{$radio.label}</td>
				<td>{$radio.html}</td>
			</tr>
			{/foreach}*}
		{/if}
	{/foreach}
	</table>
	</div>
{/foreach}
<div class="submit">

{$form_data.submit.html}
</div>
</form> 