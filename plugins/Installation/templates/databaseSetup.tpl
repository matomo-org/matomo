<h1>Mysql database setup</h1>

{if isset($errorMessage)}
	<div class="error">
		<img src="themes/default/images/error_medium.png">
		Error while trying to connect to the Mysql database:
		<br>{$errorMessage}
		
	</div>
{/if}

{if isset($form_data)}
	{include file=default/genericForm.tpl}
{/if}