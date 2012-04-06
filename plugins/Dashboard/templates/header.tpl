{* This header is for loading the dashboard in stand alone mode*}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
{loadJavascriptTranslations plugins='CoreHome Dashboard'}
{include file="CoreHome/templates/js_global_variables.tpl"}
{include file="CoreHome/templates/js_css_includes.tpl"}
{literal}
<style>
    #Dashboard {
        position:absolute;
        z-index:5;
        background: #f7f7f7;
        border: 1px solid #e4e5e4;
        padding:5px 10px 6px 10px;
        border-radius:4px;
        -moz-border-radius:4px;
        -webkit-border-radius:4px;
        color:#444;
        font-size:14px;
        cursor: pointer;
    }

    #Dashboard:hover {
        background:#f1f0eb;
        border-color:#a9a399;
    }

    #Dashboard > ul {
        list-style: square inside none;
    }

    #Dashboard > ul > li {
        padding: 0 10px;
        float: left;
    }

    #Dashboard a {
        color: #444;
        text-decoration: none;
        font-weight: normal;
    }

    #Dashboard > ul > li:hover , #Dashboard > ul > li:hover a,
    #Dashboard > ul > li.sfHover, #Dashboard > ul > li.sfHover a {
        color: #e87500;
        font-weight: bold;
    }
</style>
{/literal}
</head>
<body>
