<?php /* Smarty version 2.6.18, created on 2007-08-30 11:23:07
         compiled from form.tpl */ ?>

<form <?php echo $this->_tpl_vars['form_data']['attributes']; ?>
>
<!-- Output hidden fields -->
<?php echo $this->_tpl_vars['form_data']['hidden']; ?>

<!-- Display the fields -->
<?php $_from = $this->_tpl_vars['list_elements']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['title'] => $this->_tpl_vars['data']):
?>
	<h3><?php echo $this->_tpl_vars['title']; ?>
</h3>
	<div class="centrer">
	<table class="centrer">
	<?php $_from = $this->_tpl_vars['data']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['fieldname']):
?>
				<?php if ($this->_tpl_vars['form_data'][$this->_tpl_vars['fieldname']]['label']): ?>
		<tr>
			<td><?php echo $this->_tpl_vars['form_data'][$this->_tpl_vars['fieldname']]['label']; ?>
</td>
			<td><?php echo $this->_tpl_vars['form_data'][$this->_tpl_vars['fieldname']]['html']; ?>
</td>
		</tr>
				<?php else: ?>
			<?php $_from = $this->_tpl_vars['form_data'][$this->_tpl_vars['fieldname']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['radio']):
?>
			<tr>
				<td><?php echo $this->_tpl_vars['radio']['label']; ?>
</td>
				<td><?php echo $this->_tpl_vars['radio']['html']; ?>
</td>
			</tr>
			<?php endforeach; endif; unset($_from); ?>
		<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>
	</table>
	</div>
<?php endforeach; endif; unset($_from); ?>
<div class="boutonsAction">

</div>
</form> 