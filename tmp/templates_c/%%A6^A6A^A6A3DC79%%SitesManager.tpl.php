<?php /* Smarty version 2.6.18, created on 2007-09-03 13:48:31
         compiled from SitesManager/templates/SitesManager.tpl */ ?>
<?php echo '
<style>
* {
font-family:Trebuchet MS,arial,sans-serif;
}

textarea{
	font-family: Trebuchet MS, Verdana;
	font-size:0.85em;
	
}

#editSites{
	valign:top;
}
</style>
'; ?>

<script type="text/javascript" src="libs/jquery/jquery.js"></script>

<h2>Sites</h2>
<div id="ajaxError" style="display:none"></div>
<div id="ajaxLoading" style="display:none">Loading... <img src="themes/default/loading.gif"></div>

<table id="editSites" border=1 cellpadding="10">
    <tbody>
		<tr>
		<td>Id</td>
		<td>Name</td>
		<td>URLs</td>
		</tr>
		
		<?php $_from = $this->_tpl_vars['sites']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i'] => $this->_tpl_vars['site']):
?>
		<tr id="row<?php echo $this->_tpl_vars['i']; ?>
">
			<td id="idSite"><?php echo $this->_tpl_vars['site']['idsite']; ?>
</td>
			<td id="name" class="editableSite"><?php echo $this->_tpl_vars['site']['name']; ?>
</td>
			<td id="urls" class="editableSite"><?php $_from = $this->_tpl_vars['site']['alias_urls']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['url']):
?><?php echo $this->_tpl_vars['url']; ?>
<br><?php endforeach; endif; unset($_from); ?></td>       
			<td><img src='plugins/UsersManager/images/edit.png' class="editSite" id="row<?php echo $this->_tpl_vars['i']; ?>
" href='#'></td>
		    <td><img src='plugins/UsersManager/images/remove.png' class="deleteSite" id="row<?php echo $this->_tpl_vars['i']; ?>
" value="Delete"></td>
		      
		</tr>
		<?php endforeach; endif; unset($_from); ?>
		
    </tbody>
</table>
<div id="addRowSite"><img src='plugins/UsersManager/images/add.png'> <a href="#">Add a new Site</a></div>

<script type="text/javascript" src="plugins/UsersManager/templates/UsersManager.js"></script>
<script type="text/javascript" src="plugins/SitesManager/templates/SitesManager.js"></script>