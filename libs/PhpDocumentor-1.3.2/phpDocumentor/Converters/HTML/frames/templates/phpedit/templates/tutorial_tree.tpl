  var {$name}tree = new WebFXTree{if $subtree}Item{/if}('{$main.title|strip_tags}','{$main.link}');
{if !$subtree}  {$name}tree.setBehavior('classic');
{/if}  {$name}tree.openIcon = 'media/images/msgInformation.gif';
  {$name}tree.icon = 'media/images/{if $subtree}msgInformation.gif{else}FolderClosed.gif{/if}';
{if $kids}
{$kids}

{/if}{if $subtree}  {$parent}tree.add({$name}tree);
{else}
  document.write({$name}tree);
{/if}


