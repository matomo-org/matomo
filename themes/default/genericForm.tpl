{if $form_data.errors}
	<div class="warning">
		<img src="themes/default/images/warning_medium.png">
		<strong>{'Installation_PleaseFixTheFollowingErrors'|translate}:</strong>
		<ul>
			{foreach from=$form_data.errors item=data}
				<li>{$data}</li>
			{/foreach}
		</ul>	
	</div>
{/if}

<form {$form_data.attributes}>
	<div class="centrer">
		<table class="centrer">
			{foreach from=$element_list item=fieldname}
				{if $form_data.$fieldname.type== 'checkbox'}
					<tr>
						<td colspan=2>{$form_data.$fieldname.html}</td>
					</tr>
				{elseif $form_data.$fieldname.label}
					<tr>
						<td>{$form_data.$fieldname.label}</td>
						<td>{$form_data.$fieldname.html}</td>
					</tr>
				{elseif $form_data.$fieldname.type == 'hidden'}
					<tr>
						<td colspan=2>{$form_data.$fieldname.html}</td>
					</tr>
				{/if}
			{/foreach}
		</table>
	</div>

	{$form_data.submit.html}
</form>
