	var a{$name|replace:"-":"_"}node = new WebFXTreeItem('{$main.title|strip_tags|escape:"quotes"}','{$main.link}', parent_node);

{if $haskids}
  var a{$name|replace:"-":"_"}_old_parent_node = parent_node;
	parent_node = a{$name|replace:"-":"_"}node;
	{$kids}
	parent_node = a{$name|replace:"-":"_"}_old_parent_node;
{/if}
