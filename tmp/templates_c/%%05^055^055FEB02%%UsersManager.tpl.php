<?php /* Smarty version 2.6.18, created on 2007-09-03 13:57:39
         compiled from UsersManager/templates/UsersManager.tpl */ ?>
<script type="text/javascript" src="libs/jquery/jquery.js"></script>

<?php echo '

<style>
#access td, #users td
{ 
	spacing: 0px;
	padding: 2px 5px 5px 4px; 
	border: 1px solid #660000; 
	width:100px;
}


#ajaxError{
	color:red;
	text-align:center;
	font-weight:bold;
	width:550px;
	border: 3px solid red;
	margin: 10px;	
	padding: 10px;
}

#addrow img {
	 vertical-align:middle;
}
#addrow a {
	 text-decoration:none;
}

#accessUpdated{
	display:none;
	border:2px solid green;
	color:green;
	width:100px;
	text-align:center;
}

</style>
'; ?>


<h2>Access</h2>

<div id="sites">
<form method="get" action="<?php echo $this->_tpl_vars['formUrl']; ?>
" id="accessSites">
	<input type="hidden" name="module" value="UsersManager">
	<p>Sites: <select id="selectIdsite" name="idsite" onchange="this.form.submit()">
	
	<optgroup label="All websites">
	   	<option label="All websites" value="-1" <?php if ($this->_tpl_vars['idSiteSelected'] == -1): ?> selected="selected"<?php endif; ?>>Apply to all websites</option>
	</optgroup>
	<optgroup label="Sites">
	   <?php $_from = $this->_tpl_vars['websites']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['info']):
?>
	   		<option value="<?php echo $this->_tpl_vars['info']['idsite']; ?>
" <?php if ($this->_tpl_vars['idSiteSelected'] == $this->_tpl_vars['info']['idsite']): ?> selected="selected"<?php endif; ?>><?php echo $this->_tpl_vars['info']['name']; ?>
</option>
	   <?php endforeach; endif; unset($_from); ?>
	</optgroup>
	
	</select></p>
</form>
</div>

<table id="access">
<tr>
	<td>User</td>
	<td>No access</td>
	<td>View</td>
	<td>Admin</td>
</tr>
<?php $_from = $this->_tpl_vars['usersAccessByWebsite']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['login'] => $this->_tpl_vars['access']):
?>
<?php $this->assign('accesValid', "<img src='plugins/UsersManager/images/ok.png' class='accessGranted'>"); ?>
<?php $this->assign('accesInvalid', "<img src='plugins/UsersManager/images/no-access.png' class='updateAccess'>"); ?>
<tr>
	<td id='login'><?php echo $this->_tpl_vars['login']; ?>
</td>
	<td id='noaccess'><?php if ($this->_tpl_vars['access'] == 'noaccess' && $this->_tpl_vars['idSiteSelected'] != -1): ?><?php echo $this->_tpl_vars['accesValid']; ?>
<?php else: ?><?php echo $this->_tpl_vars['accesInvalid']; ?>
<?php endif; ?>&nbsp;</td>
	<td id='view'><?php if ($this->_tpl_vars['access'] == 'view' && $this->_tpl_vars['idSiteSelected'] != -1): ?><?php echo $this->_tpl_vars['accesValid']; ?>
<?php else: ?><?php echo $this->_tpl_vars['accesInvalid']; ?>
<?php endif; ?>&nbsp;</td>
	<td id='admin'><?php if ($this->_tpl_vars['access'] == 'admin' && $this->_tpl_vars['idSiteSelected'] != -1): ?><?php echo $this->_tpl_vars['accesValid']; ?>
<?php else: ?><?php echo $this->_tpl_vars['accesInvalid']; ?>
<?php endif; ?>&nbsp;</td>
</tr>
<?php endforeach; endif; unset($_from); ?>
</table>

<div id="accessUpdated">Done!</div>


<h2>Users</h2>

<div id="ajaxError" style="display:none"></div>
<div id="ajaxLoading" style="display:none">Loading... <img src="themes/default/loading.gif"></div>
<table id="users">
    <tbody>
        <tr>
            <td>Login</td>
            <td>Password</td>
            <td>Email</td>
            <td>Alias</td>
            <td>Edit</td>
            <td>Delete</td>
        </tr>
        <?php $_from = $this->_tpl_vars['users']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i'] => $this->_tpl_vars['user']):
?>
        <tr class="editable" id="row<?php echo $this->_tpl_vars['i']; ?>
">
            <td id="userLogin" class="editable"><?php echo $this->_tpl_vars['user']['login']; ?>
</td>
            <td id="password" class="editable">-</td>
            <td id="email" class="editable"><?php echo $this->_tpl_vars['user']['email']; ?>
</td>
            <td id="alias" class="editable"><?php echo $this->_tpl_vars['user']['alias']; ?>
</td>
            <td><img src='plugins/UsersManager/images/edit.png' class="edituser" id="row<?php echo $this->_tpl_vars['i']; ?>
" href='#'></td>
            <td><img src='plugins/UsersManager/images/remove.png' class="deleteuser" id="row<?php echo $this->_tpl_vars['i']; ?>
" value="Delete"></td>
        </tr>
        <?php endforeach; endif; unset($_from); ?>
    </tbody>
    
</table>
<div id="addrow"><img src='plugins/UsersManager/images/add.png'> <a href="#">Add a new user</a></div>
<script type="text/javascript" src="plugins/UsersManager/templates/UsersManager.js"></script>