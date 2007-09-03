<?php /* Smarty version 2.6.18, created on 2007-09-03 13:57:31
         compiled from file:/home/matt/dev/piwiktrunk/libs/Smarty/debug.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'assign_debug_info', 'file:/home/matt/dev/piwiktrunk/libs/Smarty/debug.tpl', 3, false),array('function', 'cycle', 'file:/home/matt/dev/piwiktrunk/libs/Smarty/debug.tpl', 119, false),array('modifier', 'escape', 'file:/home/matt/dev/piwiktrunk/libs/Smarty/debug.tpl', 102, false),array('modifier', 'string_format', 'file:/home/matt/dev/piwiktrunk/libs/Smarty/debug.tpl', 105, false),array('modifier', 'debug_print_var', 'file:/home/matt/dev/piwiktrunk/libs/Smarty/debug.tpl', 121, false),)), $this); ?>
<?php echo smarty_function_assign_debug_info(array(), $this);?>

<?php ob_start(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <title>Smarty Debug Console</title>
<?php echo '
<style type="text/css">
/* <![CDATA[ */
body, h1, h2, td, th, p {
    font-family: sans-serif;
    font-weight: normal;
    font-size: 0.9em;
    margin: 1px;
    padding: 0;
}

h1 {
    margin: 0;
    text-align: left;
    padding: 2px;
    background-color: #f0c040;
    color:  black;
    font-weight: bold;
    font-size: 1.2em;
 }

h2 {
    background-color: #9B410E;
    color: white;
    text-align: left;
    font-weight: bold;
    padding: 2px;
    border-top: 1px solid black;
}

body {
    background: black; 
}

p, table, div {
    background: #f0ead8;
} 

p {
    margin: 0;
    font-style: italic;
    text-align: center;
}

table {
    width: 100%;
}

th, td {
    font-family: monospace;
    vertical-align: top;
    text-align: left;
    width: 50%;
}

td {
    color: green;
}

.odd {
    background-color: #eeeeee;
}

.even {
    background-color: #fafafa;
}

.exectime {
    font-size: 0.8em;
    font-style: italic;
}

#table_assigned_vars th {
    color: blue;
}

#table_config_vars th {
    color: maroon;
}
/* ]]> */
</style>
'; ?>

</head>
<body>

<h1>Smarty Debug Console</h1>

<h2>included templates &amp; config files (load time in seconds)</h2>

<div>
<?php unset($this->_sections['templates']);
$this->_sections['templates']['name'] = 'templates';
$this->_sections['templates']['loop'] = is_array($_loop=$this->_tpl_vars['_debug_tpls']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['templates']['show'] = true;
$this->_sections['templates']['max'] = $this->_sections['templates']['loop'];
$this->_sections['templates']['step'] = 1;
$this->_sections['templates']['start'] = $this->_sections['templates']['step'] > 0 ? 0 : $this->_sections['templates']['loop']-1;
if ($this->_sections['templates']['show']) {
    $this->_sections['templates']['total'] = $this->_sections['templates']['loop'];
    if ($this->_sections['templates']['total'] == 0)
        $this->_sections['templates']['show'] = false;
} else
    $this->_sections['templates']['total'] = 0;
if ($this->_sections['templates']['show']):

            for ($this->_sections['templates']['index'] = $this->_sections['templates']['start'], $this->_sections['templates']['iteration'] = 1;
                 $this->_sections['templates']['iteration'] <= $this->_sections['templates']['total'];
                 $this->_sections['templates']['index'] += $this->_sections['templates']['step'], $this->_sections['templates']['iteration']++):
$this->_sections['templates']['rownum'] = $this->_sections['templates']['iteration'];
$this->_sections['templates']['index_prev'] = $this->_sections['templates']['index'] - $this->_sections['templates']['step'];
$this->_sections['templates']['index_next'] = $this->_sections['templates']['index'] + $this->_sections['templates']['step'];
$this->_sections['templates']['first']      = ($this->_sections['templates']['iteration'] == 1);
$this->_sections['templates']['last']       = ($this->_sections['templates']['iteration'] == $this->_sections['templates']['total']);
?>
    <?php unset($this->_sections['indent']);
$this->_sections['indent']['name'] = 'indent';
$this->_sections['indent']['loop'] = is_array($_loop=$this->_tpl_vars['_debug_tpls'][$this->_sections['templates']['index']]['depth']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['indent']['show'] = true;
$this->_sections['indent']['max'] = $this->_sections['indent']['loop'];
$this->_sections['indent']['step'] = 1;
$this->_sections['indent']['start'] = $this->_sections['indent']['step'] > 0 ? 0 : $this->_sections['indent']['loop']-1;
if ($this->_sections['indent']['show']) {
    $this->_sections['indent']['total'] = $this->_sections['indent']['loop'];
    if ($this->_sections['indent']['total'] == 0)
        $this->_sections['indent']['show'] = false;
} else
    $this->_sections['indent']['total'] = 0;
if ($this->_sections['indent']['show']):

            for ($this->_sections['indent']['index'] = $this->_sections['indent']['start'], $this->_sections['indent']['iteration'] = 1;
                 $this->_sections['indent']['iteration'] <= $this->_sections['indent']['total'];
                 $this->_sections['indent']['index'] += $this->_sections['indent']['step'], $this->_sections['indent']['iteration']++):
$this->_sections['indent']['rownum'] = $this->_sections['indent']['iteration'];
$this->_sections['indent']['index_prev'] = $this->_sections['indent']['index'] - $this->_sections['indent']['step'];
$this->_sections['indent']['index_next'] = $this->_sections['indent']['index'] + $this->_sections['indent']['step'];
$this->_sections['indent']['first']      = ($this->_sections['indent']['iteration'] == 1);
$this->_sections['indent']['last']       = ($this->_sections['indent']['iteration'] == $this->_sections['indent']['total']);
?>&nbsp;&nbsp;&nbsp;<?php endfor; endif; ?>
    <font color=<?php if ($this->_tpl_vars['_debug_tpls'][$this->_sections['templates']['index']]['type'] == 'template'): ?>brown<?php elseif ($this->_tpl_vars['_debug_tpls'][$this->_sections['templates']['index']]['type'] == 'insert'): ?>black<?php else: ?>green<?php endif; ?>>
        <?php echo ((is_array($_tmp=$this->_tpl_vars['_debug_tpls'][$this->_sections['templates']['index']]['filename'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</font>
    <?php if (isset ( $this->_tpl_vars['_debug_tpls'][$this->_sections['templates']['index']]['exec_time'] )): ?>
        <span class="exectime">
        (<?php echo ((is_array($_tmp=$this->_tpl_vars['_debug_tpls'][$this->_sections['templates']['index']]['exec_time'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.5f") : smarty_modifier_string_format($_tmp, "%.5f")); ?>
)
        <?php if ($this->_sections['templates']['index'] == 0): ?>(total)<?php endif; ?>
        </span>
    <?php endif; ?>
    <br />
<?php endfor; else: ?>
    <p>no templates included</p>
<?php endif; ?>
</div>

<h2>assigned template variables</h2>

<table id="table_assigned_vars">
    <?php unset($this->_sections['vars']);
$this->_sections['vars']['name'] = 'vars';
$this->_sections['vars']['loop'] = is_array($_loop=$this->_tpl_vars['_debug_keys']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['vars']['show'] = true;
$this->_sections['vars']['max'] = $this->_sections['vars']['loop'];
$this->_sections['vars']['step'] = 1;
$this->_sections['vars']['start'] = $this->_sections['vars']['step'] > 0 ? 0 : $this->_sections['vars']['loop']-1;
if ($this->_sections['vars']['show']) {
    $this->_sections['vars']['total'] = $this->_sections['vars']['loop'];
    if ($this->_sections['vars']['total'] == 0)
        $this->_sections['vars']['show'] = false;
} else
    $this->_sections['vars']['total'] = 0;
if ($this->_sections['vars']['show']):

            for ($this->_sections['vars']['index'] = $this->_sections['vars']['start'], $this->_sections['vars']['iteration'] = 1;
                 $this->_sections['vars']['iteration'] <= $this->_sections['vars']['total'];
                 $this->_sections['vars']['index'] += $this->_sections['vars']['step'], $this->_sections['vars']['iteration']++):
$this->_sections['vars']['rownum'] = $this->_sections['vars']['iteration'];
$this->_sections['vars']['index_prev'] = $this->_sections['vars']['index'] - $this->_sections['vars']['step'];
$this->_sections['vars']['index_next'] = $this->_sections['vars']['index'] + $this->_sections['vars']['step'];
$this->_sections['vars']['first']      = ($this->_sections['vars']['iteration'] == 1);
$this->_sections['vars']['last']       = ($this->_sections['vars']['iteration'] == $this->_sections['vars']['total']);
?>
        <tr class="<?php echo smarty_function_cycle(array('values' => "odd,even"), $this);?>
">
            <th>{$<?php echo ((is_array($_tmp=$this->_tpl_vars['_debug_keys'][$this->_sections['vars']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
}</th>
            <td><?php echo smarty_modifier_debug_print_var($this->_tpl_vars['_debug_vals'][$this->_sections['vars']['index']]); ?>
</td></tr>
    <?php endfor; else: ?>
        <tr><td><p>no template variables assigned</p></td></tr>
    <?php endif; ?>
</table>

<h2>assigned config file variables (outer template scope)</h2>

<table id="table_config_vars">
    <?php unset($this->_sections['config_vars']);
$this->_sections['config_vars']['name'] = 'config_vars';
$this->_sections['config_vars']['loop'] = is_array($_loop=$this->_tpl_vars['_debug_config_keys']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['config_vars']['show'] = true;
$this->_sections['config_vars']['max'] = $this->_sections['config_vars']['loop'];
$this->_sections['config_vars']['step'] = 1;
$this->_sections['config_vars']['start'] = $this->_sections['config_vars']['step'] > 0 ? 0 : $this->_sections['config_vars']['loop']-1;
if ($this->_sections['config_vars']['show']) {
    $this->_sections['config_vars']['total'] = $this->_sections['config_vars']['loop'];
    if ($this->_sections['config_vars']['total'] == 0)
        $this->_sections['config_vars']['show'] = false;
} else
    $this->_sections['config_vars']['total'] = 0;
if ($this->_sections['config_vars']['show']):

            for ($this->_sections['config_vars']['index'] = $this->_sections['config_vars']['start'], $this->_sections['config_vars']['iteration'] = 1;
                 $this->_sections['config_vars']['iteration'] <= $this->_sections['config_vars']['total'];
                 $this->_sections['config_vars']['index'] += $this->_sections['config_vars']['step'], $this->_sections['config_vars']['iteration']++):
$this->_sections['config_vars']['rownum'] = $this->_sections['config_vars']['iteration'];
$this->_sections['config_vars']['index_prev'] = $this->_sections['config_vars']['index'] - $this->_sections['config_vars']['step'];
$this->_sections['config_vars']['index_next'] = $this->_sections['config_vars']['index'] + $this->_sections['config_vars']['step'];
$this->_sections['config_vars']['first']      = ($this->_sections['config_vars']['iteration'] == 1);
$this->_sections['config_vars']['last']       = ($this->_sections['config_vars']['iteration'] == $this->_sections['config_vars']['total']);
?>
        <tr class="<?php echo smarty_function_cycle(array('values' => "odd,even"), $this);?>
">
            <th>{#<?php echo ((is_array($_tmp=$this->_tpl_vars['_debug_config_keys'][$this->_sections['config_vars']['index']])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
#}</th>
            <td><?php echo smarty_modifier_debug_print_var($this->_tpl_vars['_debug_config_vals'][$this->_sections['config_vars']['index']]); ?>
</td></tr>
    <?php endfor; else: ?>
        <tr><td><p>no config vars assigned</p></td></tr>
    <?php endif; ?>
</table>
</body>
</html>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('debug_output', ob_get_contents());ob_end_clean(); ?>
<?php if (isset ( $this->_tpl_vars['_smarty_debug_output'] ) && $this->_tpl_vars['_smarty_debug_output'] == 'html'): ?>
    <?php echo $this->_tpl_vars['debug_output']; ?>

<?php else: ?>
<script type="text/javascript">
// <![CDATA[
    if ( self.name == '' ) {
       var title = 'Console';
    }
    else {
       var title = 'Console_' + self.name;
    }
    _smarty_console = window.open("",title.value,"width=680,height=600,resizable,scrollbars=yes");
    _smarty_console.document.write('<?php echo ((is_array($_tmp=$this->_tpl_vars['debug_output'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : smarty_modifier_escape($_tmp, 'javascript')); ?>
');
    _smarty_console.document.close();
// ]]>
</script>
<?php endif; ?>