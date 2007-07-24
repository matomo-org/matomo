{include file="header.tpl" top2=true}
{if $hastodos}
<div id="todolist">
<p><a href="{$todolink}" target="right">Todo List</a></p>
</div>
{/if}
<h3>Navigation: {$info.0.package}</h3>
<script language="Javascript">
if (document.getElementById) {ldelim}
{section name=p loop=$info}
{if $info[p].subpackage == ""}
{if $info[p].packagetutorial}
  var tree = new WebFXTree('Help : {$info[p].packagetutorialtitle|strip_tags}', '{$info[p].packagetutorialnoa}');
{else}
  var tree = new WebFXTree('Help : {$info[p].package}', '{$info[p].packagedoc}.html');
{/if}
  tree.setBehavior('classic');
  tree.openIcon = 'media/images/Disk.gif';
  tree.icon = 'media/images/Disk.gif';

    var elements = new WebFXTreeItem('Index of elements', '{$elementindex}.html');
    elements.openIcon = 'media/images/file.png';
    elements.icon = 'media/images/file.png';
	tree.add(elements);

    var tree_function = new WebFXTreeItem('Function(s)', '{$packagedoc}');
    tree_function.openIcon = 'media/images/Functions.gif';
    tree_function.icon = 'media/images/Functions.gif';
    {section name=nonclass loop=$info[p].functions}
	var fic = new WebFXTreeItem('{$info[p].functions[nonclass].title}', '{$info[p].functions[nonclass].link}');
	fic.openIcon = 'media/images/PublicMethod.gif';
	fic.icon = 'media/images/PublicMethod.gif';
	tree_function.add(fic);
	{/section}
	tree.add(tree_function);

	var tree_interface = new WebFXTreeItem('Interface(s)', '{$classtreepage}.html');
    tree_interface.openIcon = 'media/images/classFolder.gif';
    tree_interface.icon = 'media/images/classFolder.gif';
    {section name=class loop=$info[p].classes}
      {if $info[p].classes[class].is_interface}
	  var classe = new WebFXTreeItem('{$info[p].classes[class].title}', '{$info[p].classes[class].link}');
      classe.openIcon = 'media/images/Class.gif';
      classe.icon = 'media/images/Class.gif';
      tree_interface.add(classe);
      {/if}
    {/section}
	tree.add(tree_interface);
	
    var tree_classe = new WebFXTreeItem('Class(es)', '{$classtreepage}.html');
    tree_classe.openIcon = 'media/images/classFolder.gif';
    tree_classe.icon = 'media/images/classFolder.gif';
    {section name=class loop=$info[p].classes}
      {if $info[p].classes[class].is_class}
	  var classe = new WebFXTreeItem('{$info[p].classes[class].title}', '{$info[p].classes[class].link}');
      classe.openIcon = 'media/images/Class.gif';
      classe.icon = 'media/images/Class.gif';
      tree_classe.add(classe);
      {/if}
    {/section}
	tree.add(tree_classe);

    var tree_file = new WebFXTreeItem('File(s)', '{$packagedoc}');
    tree_file.openIcon = 'media/images/FolderOpened.gif';
    tree_file.icon = 'media/images/foldericon.png';
    {section name=nonclass loop=$info[p].files}
	  var file = new WebFXTreeItem('{$info[p].files[nonclass].title}', '{$info[p].files[nonclass].link}');
      file.openIcon = 'media/images/file.png';
      file.icon = 'media/images/file.png';
      tree_file.add(file);
    {/section}
	tree.add(tree_file);
{else}
{if $info[p].subpackagetutorial}
  var subpackagetree = new WebFXTreeItem('Subpackage : {$info[p].subpackagetutorialtitle|strip_tags}', '{$info[p].subpackagetutorialnoa}');
{else}
  var subpackagetree = new WebFXTreeItem('Subpackage : {$info[p].subpackage}', '{$packagedoc}');
{/if}
  subpackagetree.openIcon = 'media/images/Disk.gif';
  subpackagetree.icon = 'media/images/Disk.gif';

    var subpackagetree_function = new WebFXTreeItem('Function(s)', '{$packagedoc}');
    subpackagetree_function.openIcon = 'media/images/Functions.gif';
    subpackagetree_function.icon = 'media/images/Functions.gif';
    {section name=nonclass loop=$info[p].functions}
	var fic = new WebFXTreeItem('{$info[p].functions[nonclass].title}', '{$info[p].functions[nonclass].link}');
	fic.openIcon = 'media/images/PublicMethod.gif';
	fic.icon = 'media/images/PublicMethod.gif';
	subpackagetree_function.add(fic);
	{/section}
	subpackagetree.add(subpackagetree_function);
	
    var subpackagetree_classe = new WebFXTreeItem('Class(es)', '{$classtreepage}.html');
    subpackagetree_classe.openIcon = 'media/images/classFolder.gif';
    subpackagetree_classe.icon = 'media/images/classFolder.gif';
    {section name=class loop=$info[p].classes}
	  var classe = new WebFXTreeItem('{$info[p].classes[class].title}', '{$info[p].classes[class].link}');
      classe.openIcon = 'media/images/Class.gif';
      classe.icon = 'media/images/Class.gif';
      subpackagetree_classe.add(classe);
    {/section}
	subpackagetree.add(subpackagetree_classe);

    var subpackagetree_file = new WebFXTreeItem('File(s)', '{$packagedoc}');
    subpackagetree_file.openIcon = 'media/images/FolderOpened.gif';
    subpackagetree_file.icon = 'media/images/foldericon.png';
    {section name=nonclass loop=$info[p].files}
	  var file = new WebFXTreeItem('{$info[p].files[nonclass].title}', '{$info[p].files[nonclass].link}');
      file.openIcon = 'media/images/file.png';
      file.icon = 'media/images/file.png';
      subpackagetree_file.add(file);
    {/section}
	subpackagetree.add(subpackagetree_file);
	
	tree.add(subpackagetree);
{/if}
{/section}
  document.write(tree);
{rdelim}
</script>
<br />
{if $hastutorials}
<div class="tutorialist">
{section name=p loop=$info}
{if count($info[p].tutorials)}
<h3>Tutorials/Manuals:{if $info[p].subpackage} {$info[p].subpackage}{/if}</h3>
{if $info[p].tutorials.pkg}
<strong>Package-level:</strong>
<script language="Javascript">
if (document.getElementById) {ldelim}
{section name=ext loop=$info[p].tutorials.pkg}
{$info[p].tutorials.pkg[ext]}
{/section}
{rdelim}
</script>
{/if}
{if $info[p].tutorials.cls}
<strong>Class-level:</strong>
<script language="Javascript">
if (document.getElementById) {ldelim}
{section name=ext loop=$info[p].tutorials.cls}
{$info[p].tutorials.cls[ext]}
{/section}
{rdelim}
</script>
{/if}
{if $info[p].tutorials.proc}
<strong>Procedural-level:</strong>
<script language="Javascript">
if (document.getElementById) {ldelim}
{section name=ext loop=$info[p].tutorials.proc}
{$info[p].tutorials.proc[ext]}
{/section}
{rdelim}
{/if}
</script>
{/if}
{/section}
{/if}
</div>
<br />
<span CLASS="small"><a href="{$phpdocwebsite}" target="_blank">phpDocumentor v <b>{$phpdocversion}</b></a><br />
<br />
<i>HTML layout inspired by </i><a href="http://www.phpedit.com" target="right">PHPEdit</a></span>
</body>
</html>
