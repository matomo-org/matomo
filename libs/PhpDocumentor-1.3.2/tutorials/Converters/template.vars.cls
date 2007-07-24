<refentry id="{@id}">
 <refnamediv>
  <refname>Converter Default Template Variables</refname>
  <refpurpose>Basic Template Variables Available for Use by a New Converter</refpurpose>
 </refnamediv>
 <refsynopsisdiv>
  <author>
   Gregory Beaver
   <authorblurb>
    {@link mailto:cellog@sourceforge.com cellog@sourceforge.com}
   </authorblurb>
  </author>
 </refsynopsisdiv>
 {@toc}
 <refsect1 id="{@id intro}">
  <title>Introduction to Converter Template Variables</title>
  <para>As of phpDocumentor version 1.2.0rc1, all Converters share certain template variables in common.  This is made possible by the common use of the Smarty template engine.  Smarty makes it possible to truly separate data from its display, allowing ultimate control of the display through the templates.</para>
 </refsect1>
 <refsect1 id="{@id smarty}">
  <title>Smarty</title>
  <para>What is Smarty?  This is a versatile compiling template engine, and has its home page at {@link http://smarty.php.net}, please read the manual and the tutorials if you are unfamiliar with Smarty.  This tutorial only describes how to use the templates, and will be confusing if you have never used Smarty before.  In particular, you must understand fully how to use the {section} and {foreach} tags.  Please read {@link http://smarty.php.net/manual/en/language.function.section.php} and {@link http://smarty.php.net/manual/en/language.function.foreach.php}</para>
  <para>All template files have available to them these smarty variables:
   <unorderedlist>
    <listitem><para><varname>{$date}</varname> string - the current date and time, as formatted by {@link date() date}(&quot;r&quot;,{@link time() time})</para></listitem>
    <listitem><para><varname>{$maintitle}</varname> string - the contents of the {@tutorial phpDocumentor.howto.pkg#using.command-line.title} command-line switch</para></listitem>
    <listitem><para><varname>{$package}</varname> string - the package of the current template item</para></listitem>
    <listitem><para><varname>{$subpackage}</varname> string - the subpackage of the current template item</para></listitem>
    <listitem><para><varname>{$phpdocversion}</varname> string - the version number of phpDocumentor</para></listitem>
    <listitem><para><varname>{$phpdocwebsite}</varname> string - the official phpDocumentor website ({@link http://www.phpdoc.org})</para></listitem>
   </unorderedlist>
  </para>
 </refsect1>
 <refsect1 id="{@id elements}">
  <title>Common Source Code Element Template Variables</title>
  <para>Source code elements are described in {@tutorial elements.pkg}.  For most converters, documented elements are split up into two categories: class and procedural page.  The template variables that contain this information are also grouped in this manner.</para>
  <para>The source code elements (function, include, global variable, define, class, class variable, method) all have a few template variables in common.  They are:
   <unorderedlist>
    <listitem><para><varname>{$sdesc}</varname> string - Summary from the DocBlock</para></listitem>
    <listitem><para><varname>{$desc}</varname> string - Long description from the DocBlock</para></listitem>
    <listitem><para><varname>{$tags}</varname> array - Tags found in the DocBlock, array('keyword' => tagname, 'data' => description). access in this manner:
     <unorderedlist>
      <listitem><para>{section name=sec loop=$tags}{$tags[sec].keyword} = {$tags[sec].data}{/section}</para></listitem>
      <listitem><para>or</para></listitem>
      <listitem><para>{foreach from=$tags item=tag}{$tag.keyword} = {$tag.data}{/foreach}</para></listitem>
     </unorderedlist>
    </para></listitem>
    <listitem><para><varname>{$api_tags}</varname> array - Tags found in the DocBlock useful in documenting the API, array('keyword' => tagname, 'data' => description).  Access in the same manner as {$tags}</para></listitem>
    <listitem><para><varname>{$info_tags}</varname> array - Tags found in the DocBlock that are purely informational in nature, offering no particular use in documenting the actual software API, array('keyword' => tagname, 'data' => description).  Access in the same manner as {$tags}</para></listitem>
    <listitem><para><varname>{$utags}</varname> array - Unknown tags found in the DocBlock, array('keyword' => tagname, 'data' => description).  Access in the same manner as {$tags}</para></listitem>
    <listitem><para><varname>{$line_number}</varname> integer - The line number in the source code that this element appears</para></listitem>
    <listitem><para><varname>{$id}</varname> string - EXCEPT for include, all elements have a unique ID.  This should be used to create a document anchor that will allow hyperlinking to that element's documentation.</para></listitem>
   </unorderedlist>
  </para>
  <refsect2 id="{@id page}">
   <title>Procedural Page Conversion</title>
   <refsect3 id="{@id convertervars}">
    <title>Procedural Page Converter variables</title>
    <para>The {@link Converter::ConvertPage()} method sets up several important variables that are used by the other conversion methods.  These are:
     <unorderedlist>
      <listitem><para>{@link Converter::$page_data} - the Smarty template that contains all procedural page contents</para></listitem>
      <listitem><para>{@link Converter::$path} - the full path to the procedural page</para></listitem>
      <listitem><para>{@link Converter::$page} - the converter-safe name of the procedural page, can be used to uniquely name output</para></listitem>
      <listitem><para>{@link Converter::$curpage} - the {@link parserPage} representation of the current procedural page</para></listitem>
     </unorderedlist>
    </para>
   </refsect3>
   <refsect3 id="{@id templatevars}">
    <title>Procedural Page template variables</title>
    <para>There are many template variables assigned to the procedural page template:
     <unorderedlist>
      <listitem><para><varname>{$name}</varname> string - the name of the file &quot;file.php&quot;</para></listitem>
      <listitem><para><varname>{$source_location}</varname> string - the relative path of the file, needed for an include statement</para></listitem>
      <listitem><para><varname>{$classes}</varname> array - an array of classes defined in the file, see {@link Converter::getClassesOnPage()}</para></listitem>
      <listitem><para><varname>{$tutorial}</varname> string|false - a link to the tutorial associated with this page, or false if none</para></listitem>
      <listitem><para><varname>{$sdesc}</varname> string - summary of the procedural page from the Page-level DocBlock</para></listitem>
      <listitem><para><varname>{$desc}</varname> string - long description of the procedural page from the Page-level DocBlock</para></listitem>
      <listitem><para><varname>{$tags}</varname> array - array of all tags, array('keyword' => tagname, 'data' => description)</para></listitem>
      <listitem><para><varname>{$utags}</varname> array - array of all unknown tags, array('keyword' => tagname, 'data' => description).  This variable is unused in the templates packaged with phpDocumentor, and should be accessed in exactly the same manner that <varname>{$tags}</varname> is accessed.</para></listitem>
      <listitem><para>functions, includes, defines, globals array - these arrays contain all of the procedural elements defined in the file</para></listitem>
     </unorderedlist>
    </para>
   </refsect3>
  </refsect2>
  <refsect2 id="{@id functions}">
   <title>Function template variables</title>
   <para>The function and method template variables are nearly identical.  The only template variable that functions use that methods do not is <varname>{$function_conflicts}</varname>, as methods cannot have conflicts.  The function template variables are:
    <unorderedlist>
     <listitem><para><varname>{$function_name}</varname> string - simple name of the function</para></listitem>
     <listitem><para><varname>{$params}</varname> array - Function parameters, array('var' => parameter name, 'datatype' => parameter type, 'data' => parameter description).  The last two items are pulled from a {@tutorial tags.var.pkg} tag.</para></listitem>
     <listitem><para><varname>{$function_return}</varname> string - data type that the function returns</para></listitem>
     <listitem><para><varname>{$function_conflicts}</varname> array - array('conflicttype' => 'function', 'conflicts' => array(link to docs of conflicting function 1, ...))</para></listitem>
     <listitem><para><varname>{$function_call}</varname> string - pre-formatted function declaration, use as is</para></listitem>
     <listitem><para><varname>{$ifunction_call}</varname> array - special unformatted function declaration, use to customize for special purposes.  All of the templates distributed with phpDocumentor use this template variable.  Writing template code for this variable is complex.  It may be best to copy over code from existing templates and modify it as needed.</para></listitem>
    </unorderedlist>
    The <varname>{$ifunction_call}</varname> template variable is formatted by {@link parserFunction::getIntricateFunctionCall()}, and the documentation for that method describes its format in detail.
   </para>
   <para>All functions are appended to the <varname>{$functions}</varname> template variable of the procedural page that contains them.  In version 2.0, there will be greater flexibility possible with locating template variables.</para>
  </refsect2>
  <refsect2 id="{@id globals}">
   <title>Global variable template variables</title>
   <para>Global variables templates are pretty simple.  They contain the standard element template variables plus:
    <unorderedlist>
     <listitem><para><varname>{$global_name}</varname> string - Name of the global variable</para></listitem>
     <listitem><para><varname>{$global_type}</varname> string - Data type of the global variable</para></listitem>
     <listitem><para><varname>{$global_value}</varname> string- Initial value of the global variable as parsed from the source, if any</para></listitem>
     <listitem><para><varname>{$global_conflicts}</varname> array|false - array('conflicttype' => 'global variables', 'conflicts' => array(link to docs of conflicting global variable 1, ...))</para></listitem>
    </unorderedlist>
   </para>
   <para>All global variables are appended to the <varname>{$globals}</varname> template variable of the procedural page that contains them.  In version 2.0, there will be greater flexibility possible with locating template variables.</para>
  </refsect2>
  <refsect2 id="{@id defines}">
   <title>Define constants template variables</title>
   <para>Define constant templates are also pretty simple.  They contain the standard element template variables plus:
    <unorderedlist>
     <listitem><para><varname>{$define_name}</varname> string- Name of the constant</para></listitem>
     <listitem><para><varname>{$define_value}</varname> string- Value of the constant as parsed from the source</para></listitem>
     <listitem><para><varname>{$define_conflicts}</varname> array|false - array('conflicttype' => 'defines', 'conflicts' => array(link to docs of conflicting define 1, ...))</para></listitem>
    </unorderedlist>
   </para>
   <para>All define constants are appended to the <varname>{$defines}</varname> template variable of the procedural page that contains them.  In version 2.0, there will be greater flexibility possible with locating template variables.</para>
  </refsect2>
  <refsect2 id="{@id includes}">
   <title>Include statements template variables</title>
   <para>Include statement templates are the simplest of all.  They contain the standard element template variables plus:
    <unorderedlist>
     <listitem><para><varname>{$include_name}</varname> string - the kind of include (include/require/include_once/require_once)</para></listitem>
     <listitem><para><varname>{$include_value}</varname> string - The file included.  If the file is parsed, a link to its documentation will be passed instead of just the name of the file.</para></listitem>
    </unorderedlist>
   </para>
   <para>All include statements are appended to the <varname>{$includes}</varname> template variable of the procedural page that contains them.  In version 2.0, there will be greater flexibility possible with locating template variables.</para>
  </refsect2>
  <refsect2 id="{@id class}">
   <title>Class Conversion</title>
   <refsect3 id="{@id convertervars}">
    <title>Class Converter Variables</title>
    <para>Like ConvertPage(), the {@link Converter::ConvertClass()} method sets up a few important variables that are used by the other conversion methods.  These are:
     <unorderedlist>
      <listitem><para>{@link Converter::$class_data} - the Smarty template that contains all class contents for this class</para></listitem>
      <listitem><para>{@link Converter::$class} - the name of the current class</para></listitem>
     </unorderedlist>
    </para>
   </refsect3>
   <refsect3 id="{@id templatevars}">
    <title>Class Template Variables</title>
    <para>There are many template variables assigned to the procedural page template aside from the standard element template variables:
     <unorderedlist>
      <listitem><para><varname>{$class_name}</varname> string - the name of the class</para></listitem>
      <listitem><para><varname>{$package}</varname> string - the package of the class</para></listitem>
      <listitem><para><varname>{$source_location}</varname> string - the include path to the file that contains this class</para></listitem>
      <listitem><para><varname>{$children}</varname> array - array(array('link' => link to class that extends this class, 'sdesc' => summary of child class), ...)</para></listitem>
      <listitem><para><varname>{$class_tree}</varname> string - fully-formatted class tree (Converter-specific).  Use unmodified.</para></listitem>
      <listitem><para><varname>{$conflict}</varname> array - array('conflicttype' => 'classes', 'conflicts' => array(link to docs of conflicting class 1, ...))</para></listitem>
      <listitem><para><varname>{$ivars}</varname> array - complex array of all inherited variables, see {@link Converter::getFormattedInheritedVars()}</para></listitem>
      <listitem><para><varname>{$imethods}</varname> array - complex array of all inherited variables, see {@link Converter::getFormattedInheritedMethods()}</para></listitem>
      <listitem><para><varname>vars, methods</varname> array - these template variables will contain each of the class variables and method template variables.</para></listitem>
     </unorderedlist>
    </para>
   </refsect3>
  </refsect2>
  <refsect2 id="{@id vars}">
   <title>Class variable template variables</title>
   <para>The Class variable template variables are:
    <unorderedlist>
     <listitem><para><varname>{$var_name}</varname> string - simple name of the class variable</para></listitem>
     <listitem><para><varname>{$var_default}</varname> string - Default value of the class variable, if any</para></listitem>
     <listitem><para><varname>{$var_type}</varname> string - data type of the variable, from the {@tutorial tags.var.pkg} tag.</para></listitem>
     <listitem><para><varname>{$var_overrides}</varname> array - array('link' => link to documentation for overridden class variable, 'sdesc' => summary of overridden class variable)</para></listitem>
    </unorderedlist>
    The <varname>{$ifunction_call}</varname> template variable is formatted by {@link parserFunction::getIntricateFunctionCall()}, and the documentation for that method describes its format in detail.
   </para>
   <para>All class variables are appended to the <varname>{$vars}</varname> template variable of the class that contains them.  In version 2.0, there will be greater flexibility possible with locating template variables.</para>
  </refsect2>
  <refsect2 id="{@id methods}">
   <title>Function template variables</title>
   <para>The function and method template variables are nearly identical.  See {@tutorial template.vars.cls#elements.functions} for details.  The template variables that methods use and functions do not use are:
    <unorderedlist>
     <listitem><para><varname>{$constructor}</varname> boolean - true/false, true if the method is a constructor</para></listitem>
     <listitem><para><varname>{$descmethod}</varname> array - array(array('link'=>link to documentation for overriding method, 'sdesc'=> summary of the overriding method),...)</para></listitem>
     <listitem><para><varname>{$method_overrides}</varname> array - array('link'=>link to documentation for overridden method, 'sdesc'=> summary of the overridden method)</para></listitem>
    </unorderedlist>
   </para>
   <para>All methods are appended to the <varname>{$methods}</varname> template variable of the class that contains them.  In version 2.0, there will be greater flexibility possible with locating template variables.</para>
  </refsect2>
  <refsect2 id="{@id tutorials}">
   <title>Tutorial template variables</title>
   <para>
    <unorderedlist>
     <listitem><para><varname>{$contents}</varname> string - Converted contents of the tutorial</para></listitem>
     <listitem><para><varname>{$title}</varname> string - Title of the tutorial</para></listitem>
     <listitem><para><varname>{$nav}</varname> string - true/false, Whether any navigation links to other tutorials exist</para></listitem>
     <listitem><para><varname>{$up}</varname> string - link to the parent tutorial of this tutorial</para></listitem>
     <listitem><para><varname>{$uptitle}</varname> string - title of the parent tutorial</para></listitem>
     <listitem><para><varname>{$prev}</varname> string - link to the previous tutorial in the tutorial hierarchy</para></listitem>
     <listitem><para><varname>{$prevtitle}</varname> string - title of the previous tutorial</para></listitem>
     <listitem><para><varname>{$next}</varname> string - link to the next tutorial in the tutorial hierarchy</para></listitem>
     <listitem><para><varname>{$nexttitle}</varname> string - title of the next tutorial</para></listitem>
    </unorderedlist>
   </para>
  </refsect2>
 </refsect1>
</refentry>