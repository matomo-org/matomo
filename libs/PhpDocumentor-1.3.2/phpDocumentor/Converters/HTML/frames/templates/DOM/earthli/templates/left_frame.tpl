{include file="header.tpl" top2=true}
<h3 class="package-title">{$info.0.package}</h3>
<div class="tree">
<script language="Javascript">
if (document.getElementById) {ldelim}
{section name=p loop=$info}
	{if $info[p].subpackage == ""}
		var tree = new WebFXTree('<span class="package">{$info.0.package}</span>');
		tree.setBehavior('classic');
		tree.openIcon = 'media/images/package.png';
		tree.icon = 'media/images/package.png';
	
		{if $hastodos}
			var todos = new WebFXTreeItem('To-do List', '{$todolink}');
			todos.openIcon = 'media/images/Index.png';
			todos.icon = 'media/images/Index.png';
			tree.add(todos);
		{/if}

		var class_trees = new WebFXTreeItem('Class trees', '{$classtreepage}.html');
		class_trees.openIcon = 'media/images/Index.png';
		class_trees.icon = 'media/images/Index.png';
		tree.add(class_trees);

		var elements = new WebFXTreeItem('Index of elements', '{$elementindex}.html');
		elements.openIcon = 'media/images/Index.png';
		elements.icon = 'media/images/Index.png';
		tree.add(elements);

		var parent_node;

		{if $info[p].tutorials}
			var tree_tutorial = new WebFXTreeItem('Tutorial(s)/Manual(s)', '');
			tree_tutorial.openIcon = 'media/images/tutorial_folder.png';
			tree_tutorial.icon = 'media/images/tutorial_folder.png';
			tree.add(tree_tutorial);
			
			{if $info[p].tutorials.pkg}
				var tree_inner_tutorial = new WebFXTreeItem('Package-level', '');
				tree_inner_tutorial.openIcon = 'media/images/package_folder.png';
				tree_inner_tutorial.icon = 'media/images/package_folder.png';
				tree_tutorial.add(tree_inner_tutorial);
				
				parent_node = tree_inner_tutorial;
				{section name=ext loop=$info[p].tutorials.pkg}
					{$info[p].tutorials.pkg[ext]}
				{/section}
			{/if}
			
			{if $info[p].tutorials.cls}
				var tree_inner_tutorial = new WebFXTreeItem('Class-level', '');
				tree_inner_tutorial.openIcon = 'media/images/class_folder.png';
				tree_inner_tutorial.icon = 'media/images/class_folder.png';
				tree_tutorial.add(tree_inner_tutorial);
				
				parent_node = tree_inner_tutorial;
				{section name=ext loop=$info[p].tutorials.cls}
					{$info[p].tutorials.cls[ext]}
				{/section}
			{/if}
			
			{if $info[p].tutorials.proc}
				var tree_inner_tutorial = new WebFXTreeItem('Function-level', '');
				tree_inner_tutorial.openIcon = 'media/images/function_folder.png';
				tree_inner_tutorial.icon = 'media/images/function_folder.png';
				tree_tutorial.add(tree_inner_tutorial);
				
				parent_node = tree_inner_tutorial;
				{section name=ext loop=$info[p].tutorials.proc}
					{$info[p].tutorials.proc[ext]}
				{/section}
			{/if}		
		{/if}

		{if $info[p].hasinterfaces}
			var tree_classe = new WebFXTreeItem('Interface(s)', '{$packagedoc|escape:"quotes"}');
			tree_classe.openIcon = 'media/images/class_folder.png';
			tree_classe.icon = 'media/images/class_folder.png';
			
			{section name=class loop=$info[p].classes}
			    {if $info[p].classes[class].is_interface}
				var classe = new WebFXTreeItem('{$info[p].classes[class].title|escape:"quotes"}', '{$info[p].classes[class].link|escape:"quotes"}');
				classe.openIcon = 'media/images/Interface.png';
				classe.icon = 'media/images/Interface.png';
				tree_classe.add(classe);
				{/if}
			{/section}
	
			tree.add(tree_classe);
		{/if}

		{if $info[p].hasclasses}
			var tree_classe = new WebFXTreeItem('Class(es)', '{$packagedoc|escape:"quotes"}');
			tree_classe.openIcon = 'media/images/class_folder.png';
			tree_classe.icon = 'media/images/class_folder.png';
			
			{section name=class loop=$info[p].classes}
			    {if $info[p].classes[class].is_class}
				var classe = new WebFXTreeItem('{$info[p].classes[class].title|escape:"quotes"}', '{$info[p].classes[class].link|escape:"quotes"}');
				classe.openIcon = 'media/images/{if $info[p].classes[class].abstract}Abstract{/if}{if $info[p].classes[class].access == 'private'}Private{/if}Class.png';
				classe.icon = 'media/images/{if $info[p].classes[class].abstract}Abstract{/if}{if $info[p].classes[class].access == 'private'}Private{/if}Class.png';
				tree_classe.add(classe);
				{/if}
			{/section}
	
			tree.add(tree_classe);
		{/if}

		{if $info[p].functions}
			var tree_function = new WebFXTreeItem('Function(s)', '{$packagedoc|escape:"quotes"}');
			tree_function.openIcon = 'media/images/function_folder.png';
			tree_function.icon = 'media/images/function_folder.png';
			
			{section name=nonclass loop=$info[p].functions}
				var fic = new WebFXTreeItem('{$info[p].functions[nonclass].title|escape:"quotes"}', '{$info[p].functions[nonclass].link|escape:"quotes"}');
				fic.openIcon = 'media/images/Function.png';
				fic.icon = 'media/images/Function.png';
				tree_function.add(fic);
			{/section}
		
			tree.add(tree_function);
		{/if}
	
		{if $info[p].files}
			var tree_file = new WebFXTreeItem('File(s)', '{$packagedoc|escape:"quotes"}');
			tree_file.openIcon = 'media/images/folder.png';
			tree_file.icon = 'media/images/folder.png';
	
			{section name=nonclass loop=$info[p].files}
				var file = new WebFXTreeItem('{$info[p].files[nonclass].title|escape:"quotes"}', '{$info[p].files[nonclass].link|escape:"quotes"}');
				file.openIcon = 'media/images/Page.png';
				file.icon = 'media/images/Page.png';
				tree_file.add(file);
			{/section}
	
			tree.add(tree_file);
		{/if}

	{else}
		{if $info[p].subpackagetutorial}
			var subpackagetree = new WebFXTreeItem('<span class="sub-package">{$info[p].subpackagetutorialtitle|strip_tags|escape:"quotes"}</span>', '{$info[p].subpackagetutorialnoa}');
		{else}
			var subpackagetree = new WebFXTreeItem('<span class="sub-package">{$info[p].subpackage}</span>', '{$packagedoc|escape:"quotes"}');
		{/if}

		subpackagetree.openIcon = 'media/images/package.png';
		subpackagetree.icon = 'media/images/package.png';

		{if $info[p].tutorials}
			var tree_tutorial = new WebFXTreeItem('Tutorial(s)/Manual(s)', '');
			tree_tutorial.openIcon = 'media/images/tutorial_folder.png';
			tree_tutorial.icon = 'media/images/tutorial_folder.png';
			tree.add(tree_tutorial);
			
			{if $info[p].tutorials.pkg}
				var tree_inner_tutorial = new WebFXTreeItem('Package-level', '');
				tree_inner_tutorial.openIcon = 'media/images/package_folder.png';
				tree_inner_tutorial.icon = 'media/images/package_folder.png';
				tree_tutorial.add(tree_inner_tutorial);
				
				parent_node = tree_inner_tutorial;
				{section name=ext loop=$info[p].tutorials.pkg}
					{$info[p].tutorials.pkg[ext]}
				{/section}
			{/if}
			
			{if $info[p].tutorials.cls}
				var tree_inner_tutorial = new WebFXTreeItem('Class-level', '');
				tree_inner_tutorial.openIcon = 'media/images/class_folder.png';
				tree_inner_tutorial.icon = 'media/images/class_folder.png';
				tree_tutorial.add(tree_inner_tutorial);
				
				parent_node = tree_inner_tutorial;
				{section name=ext loop=$info[p].tutorials.cls}
					{$info[p].tutorials.cls[ext]}
				{/section}
			{/if}
			
			{if $info[p].tutorials.proc}
				var tree_inner_tutorial = new WebFXTreeItem('Function-level', '');
				tree_inner_tutorial.openIcon = 'media/images/function_folder.png';
				tree_inner_tutorial.icon = 'media/images/function_folder.png';
				tree_tutorial.add(tree_inner_tutorial);
				
				parent_node = tree_inner_tutorial;
				{section name=ext loop=$info[p].tutorials.proc}
					{$info[p].tutorials.proc[ext]}
				{/section}
			{/if}		
		{/if}
	
		{if $info[p].classes}
			var subpackagetree_classe = new WebFXTreeItem('Class(es)', '{$packagedoc|escape:"quotes"}');
			subpackagetree_classe.openIcon = 'media/images/class_folder.png';
			subpackagetree_classe.icon = 'media/images/class_folder.png';
			
			{section name=class loop=$info[p].classes}
				var classe = new WebFXTreeItem('{$info[p].classes[class].title|escape:"quotes"}', '{$info[p].classes[class].link|escape:"quotes"}');
				classe.openIcon = 'media/images/{if $info[p].classes[class].abstract}Abstract{/if}{if $info[p].classes[class].access == 'private'}Private{/if}Class.png';
				classe.icon = 'media/images/{if $info[p].classes[class].abstract}Abstract{/if}{if $info[p].classes[class].access == 'private'}Private{/if}Class.png';
				subpackagetree_classe.add(classe);
			{/section}
			
			subpackagetree.add(subpackagetree_classe);
		{/if}

		{if $info[p].functions}
			var subpackagetree_function = new WebFXTreeItem('Function(s)', '{$packagedoc|escape:"quotes"}');
			subpackagetree_function.openIcon = 'media/images/function_folder.png';
			subpackagetree_function.icon = 'media/images/function_folder.png';
			
			{section name=nonclass loop=$info[p].functions}
				var fic = new WebFXTreeItem('{$info[p].functions[nonclass].title|escape:"quotes"}', '{$info[p].functions[nonclass].link|escape:"quotes"}');
				fic.openIcon = 'media/images/Function.png';
				fic.icon = 'media/images/Function.png';
				subpackagetree_function.add(fic);
			{/section}
			
			subpackagetree.add(subpackagetree_function);
		{/if}
		
		{if $info[p].files}
			var subpackagetree_file = new WebFXTreeItem('File(s)', '{$packagedoc|escape:"quotes"}');
			subpackagetree_file.openIcon = 'media/images/folder.png';
			subpackagetree_file.icon = 'media/images/folder.png';
			
			{section name=nonclass loop=$info[p].files}
				var file = new WebFXTreeItem('{$info[p].files[nonclass].title|escape:"quotes"}', '{$info[p].files[nonclass].link|escape:"quotes"}');
				file.openIcon = 'media/images/Page.png';
				file.icon = 'media/images/Page.png';
				subpackagetree_file.add(file);
			{/section}
		
			subpackagetree.add(subpackagetree_file);
		{/if}
	
	  tree.add(subpackagetree);
	{/if}
{/section}

document.write(tree);
{rdelim}
</script>
</div>
<p class="notes">
	Generated by 
	<a href="{$phpdocwebsite}" target="_blank">phpDocumentor <span class="field">{$phpdocversion}</span></a>
</p>
</body>
</html>
