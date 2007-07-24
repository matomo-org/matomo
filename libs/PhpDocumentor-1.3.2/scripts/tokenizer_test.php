<?php
/**
 * @package tests
 */
/**
$fp = fopen("../phpDocumentor/Converter.inc","r");
$file = fread($fp,filesize("../phpDocumentor/Converter.inc"));
fclose($fp);
*/
$file = "
<?php
    function &newSmarty()
    {
        if (!isset(\$this->package_index))
        foreach(\$this->all_packages as \$key => \$val)
        {
            if (isset(\$this->pkg_elements[\$key]))
            {
                if (!isset(\$start)) \$start = \$key;
                \$this->package_index[] = array('link' => \"li_\$key.html\", 'title' => \$key);
            }
        }
        \$templ = new Smarty;
        \$templ->template_dir = \$this->smarty_dir . PATH_DELIMITER . 'templates';
        \$templ->compile_dir = \$this->smarty_dir . PATH_DELIMITER . 'templates_c';
        \$templ->config_dir = \$this->smarty_dir . PATH_DELIMITER . 'configs';
        \$templ->assign(\"packageindex\",\$this->package_index);
        \$templ->assign(\"phpdocversion\",PHPDOCUMENTOR_VER);
        \$templ->assign(\"phpdocwebsite\",PHPDOCUMENTOR_WEBSITE);
        \$templ->assign(\"package\",\$this->package);
        \$templ->assign(\"subdir\",'');
        return \$templ;
    }
?>
";
$tokens = token_get_all($file);

$nl_check = array(T_WHITESPACE,T_ENCAPSED_AND_WHITESPACE,T_COMMENT,T_DOC_COMMENT,T_OPEN_TAG,T_CLOSE_TAG,T_INLINE_HTML);
print '<pre>';
$line = 0;
foreach($tokens as $key => $val)
{
	if (is_array($val))
	{
		// seeing if we can get line numbers out of the beast
		if (in_array($val[0],$nl_check))
		{
			$line+=substr_count($val[1],"\n");
		}
		echo token_name($val[0])." => ".htmlentities($val[1])."\n";
	}
	else
	{
		echo "*** $val\n";
	}
}
echo "$line\n";
print '</pre>';
?>
