{if count($params)}<text size="10" left="15"><b><i>Function Parameters:</i></b>
</text><text size="11" left="20"><ul>{section name=params loop=$params}
<li><i>{$params[params].type}</i> <b>{$params[params].name}</b> {$params[params].description}</li>
{/section}</ul></text>{/if}
