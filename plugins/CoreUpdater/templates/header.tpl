<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Piwik &rsaquo; {'CoreUpdater_UpdateTitle'|translate}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="shortcut icon" href="plugins/CoreHome/templates/images/favicon.ico"/>

    <link rel="stylesheet" type="text/css" href="themes/default/simple_structure.css"/>
    <link rel="stylesheet" type="text/css" href="libs/jquery/themes/base/jquery-ui.css"/>
    <link rel="stylesheet" type="text/css" href="themes/default/styles.css"/>
    <link rel="stylesheet" type="text/css" href="plugins/CoreHome/templates/donate.css"></link>
    <link rel="stylesheet" type="text/css" href="plugins/CoreHome/templates/jquery.ui.autocomplete.css"></link>
    {literal}
        <style type="text/css">
            * {
                margin: 0;
                padding: 0;
            }

            .topBarElem {
                font-family: arial, sans-serif !important;
                font-size: 13px;
                line-height: 1.33;
            }

            #donate-form-container {
                margin: 0 0 2em 2em;
            }
        </style>
    {/literal}

    <script type="text/javascript" src="libs/jquery/jquery.js"></script>
    <script type="text/javascript" src="libs/jquery/jquery-ui.js"></script>
    <script type="text/javascript" src="plugins/CoreHome/templates/donate.js"></script>
    {if 'General_LayoutDirection'|translate =='rtl'}
        <link rel="stylesheet" type="text/css" href="themes/default/rtl.css"/>
    {/if}
    {loadJavascriptTranslations plugins='CoreHome'}
</head>
<body id="simple">
<div id="contentsimple">
    <div id="title"><img title='Piwik' alt="Piwik" src='themes/default/images/logo-header.png' style='margin-left:10px'/><span
                id="subh1"> # {'General_OpenSourceWebAnalytics'|translate}</span></div>
