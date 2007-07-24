<refentry id="{@id}">
 <refnamediv>
  <refname>Writing templates for the HTMLframes Converter</refname>
  <refpurpose>Learn which template variables are available for use in an HTML:frames template</refpurpose>
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
  <title>HTML:frames:* Introduction</title>
  <para>This documentation deals only with adding a new template to the HTMLframes Converter.  To learn how to use phpDocumentor, read the {@tutorial phpDocumentor/phpDocumentor.pkg}.  To learn how to write a new Converter, read {@tutorial Converters.pkg}</para>
 </refsect1>
 <refsect1 id="{@id overview}">
  <title>Overview</title>
  <para>The HTML:frames converter uses all of the values from the generic Converter (see {@tutorial template.vars.cls}), and adds a few more.</para>
  <para>All templates have the standard smarty template variables available to them, as well as these:
   <unorderedlist>
    <listitem><para><varname>{$subdir }</varname> string - the combination of &quot;../&quot; that will lead to the base output directory, used for linking to other generated documentation.</para></listitem>
    <listitem><para><varname>{$packageindex}</varname> array - a list of all the packages and their index pages in the format array('link' =&gt; link to the index page, 'title' =&gt; package name)</para></listitem>
   </unorderedlist>
  </para>
 </refsect1>
 <refsect1 id="{@id template}">
  <title>Template Variables</title>
  <refsect2 id="{@id source}">
   <title>Source Code formatting - filesource.tpl</title>
   <para>When source code is generated via the {@tutorial tags.filesource.pkg} tag, the output is passed into these templates variables:</para>
   <para>
    <unorderedlist>
     <listitem><para><varname>{$source}</varname> string - the fully formatted source code, use as is</para></listitem>
     <listitem><para><varname>{$package}</varname> string - the package name of the source file</para></listitem>
     <listitem><para><varname>{$subpackage}</varname> string - the subpackage name of the source file</para></listitem>
     <listitem><para><varname>{$name}</varname> string - the name of the source file file.ext</para></listitem>
     <listitem><para><varname>{$source_loc}</varname> string - the relative path to the file, used for include() statements</para></listitem>
     <listitem><para><varname>{$docs}</varname> string - a link to the documentation for this file</para></listitem>
    </unorderedlist>
   </para>
  </refsect2>
  <refsect2 id="{@id example}">
   <title>Example Code formatting - examplesource.tpl</title>
   <para>When an example file is specified via the {@tutorial tags.example.pkg} tag, the output is passed into these templates variables:</para>
   <para>
    <unorderedlist>
     <listitem><para><varname>{$source}</varname> string - the fully formatted source code, use as is</para></listitem>
     <listitem><para><varname>{$file}</varname> string - the full path to the file</para></listitem>
     <listitem><para><varname>{$title}</varname> string - the title of this example, from the @example tag</para></listitem>
    </unorderedlist>
   </para>
  </refsect2>
  <refsect2 id="{@id left}">
   <title>Left Frame formatting - left_frame.tpl</title>
   <para>The HTML:frames Converter places a table of contents in the left frame.  This table of contents contains links to class trees, alphabetical indexes, tutorials, and elements organized by category (class/procedural code).</para>
   <refsect3 id="{@id basic}">
    <title>Template variables passed to left_frame.tpl</title>
    <para>Each left_frame.tpl consists of one package's documentation.  Basic template variables passed to left_frame.tpl include:</para>
    <para>
     <unorderedlist>
      <listitem><para><varname>{$info}</varname> array - the table of contents, see {@tutorial HTMLframesConverter.cls#template.left.info} for contents</para></listitem>
      <listitem><para><varname>{$package}</varname> string - package this table of contents documents</para></listitem>
      <listitem><para><varname>{$hastutorials}</varname> boolean - true if this package has external tutorials (see {@tutorial tutorials.pkg})</para></listitem>
      <listitem><para><varname>{$hastodos}</varname> boolean - true if there are any {@tutorial tags.todo.pkg} tags in this package</para></listitem>
      <listitem><para><varname>{$todolink}</varname> string - file.html name of the todo list.</para></listitem>
      <listitem><para><varname>{$classtreepage}</varname> string - name of the file containing class trees, minus .html</para></listitem>
      <listitem><para><varname>{$elementindex}</varname> string - name of the file containing the alphabetical index for this package, minus .html</para></listitem>
      <listitem><para><varname>{$packagedoc}</varname> string|false - link to package-level documentation, or false if there is none</para></listitem>
     </unorderedlist>
    </para>
   </refsect3>
   <refsect3 id="{@id info}">
    <title>The {$info} template variable</title>
    <para>The {$info} variable is an array of arrays, each indexed by subpackage.  Even packages with no explicit {@tutorial tags.subpackage.pkg} tags have a subpackage named '' (empty string).  Elements in the main package can be detected using the smarty code:
     <programlisting role="html">{section name=p loop=$info}{if $info[p].subpackage == ''}...</programlisting>
    </para>
    <para>
     The {$info} variable contains these sub-variables (assuming that {section name=p loop=$info} was used):
     <unorderedlist>
      <listitem><para><varname>{$info[p].package}</varname> string - package name</para></listitem>
      <listitem><para><varname>{$info[p].subpackage}</varname> string - subpackage name</para></listitem>
      <listitem><para><varname>{$info[p].packagedoc}</varname> string - link to old format .html-based package-level documentation (deprecated)</para></listitem>
      <listitem><para><varname>{$info[p].packagetutorial}</varname> string - link to new package-level documentation, or subpackage-level documentation for subpackages</para></listitem>
      <listitem><para><varname>{$info[p].packagetutorialnoa}</varname> string - link to new package-level documentation, or subpackage-level documentation for subpackages, without enclosing &lt;a href=&quot;link to docs&quot;&gt; HTML tag.  Use this to do special link handling (opening a new window, opening in the _right frame, etc.)</para></listitem>
      <listitem><para><varname>{$info[p].packagetutorialtitle}</varname> string - title of package-level documentation</para></listitem>
      <listitem><para><varname>{$info[p].files}</varname>/<varname>{$info[p].classes}</varname>/<varname>{$info[p].functions}</varname> array - links to documentation array('link' =&gt; html link, 'title' =&gt; name of element)</para></listitem>
      <listitem><para><varname>{$info[p].tutorials}</varname> string - Formatted tutorial table of contents, see {@tutorial HTMLframesConverter.cls#template.left.tutorial}</para></listitem>
     </unorderedlist>
    </para>
   </refsect3>
   <refsect3 id="{@id tutorial}">
    <title>Tutorial table of contents formatting - tutorial_tree.tpl</title>
    <para>The tutorial table of contents is formatted using the recursive tutorial_tree.tpl template.  This template has only a few variables:
     <unorderedlist>
      <listitem><para><varname>{$name}</varname> string - abstract name of the tutorial (tutorial.pkg becomes tutorialpkg)</para></listitem>
      <listitem><para><varname>{$parent}</varname> string|false - {$name} from parent tutorial, or false if no parent tutorial</para></listitem>
      <listitem><para><varname>{$main.title}</varname> string - the title of the tutorial.  Use as {main.title|striptags}</para></listitem>
      <listitem><para><varname>{$main.link}</varname> string - link to the tutorial's documentation</para></listitem>
      <listitem><para><varname>{$haskids}</varname> boolean - true if this tutorial has sub-tutorials</para></listitem>
      <listitem><para><varname>{$kids}</varname> string - fully formatted list of child tutorials</para></listitem>
      <listitem><para><varname>{$subtree}</varname> boolean - true if this is a sub-tutorial tree.  Use to modify formatting for sub-tutorial listings.</para></listitem>
     </unorderedlist>
    </para>
    <para>The tutorial_tree.tpl file should represent the hierarchy of tutorials through some form of a nested html tag, such as a nested list (like the default converter), or a nested DHTML menu (like the phpedit or earthli templates).</para>
   </refsect3>
  </refsect2>
  <refsect2 id="{@id classtrees}">
   <title>Class trees - classtrees.tpl</title>
   <para>Class trees are a visual representation of Class inheritance to help facilitate understanding of how a project is organized.  Class trees have only a few template variables:
    <unorderedlist>
     <listitem><para><varname>{$package}</varname> string - the package that base classes are found in</para></listitem>
     <listitem><para><varname>{$classtrees}</varname> array - a pre-formatted list of class trees, organized by base class.  Format is: array('class' =&gt; name of class, 'classtree' =&gt; fully formatted class tree)</para></listitem>
    </unorderedlist>
   </para>
  </refsect2>
  <refsect2 id="{@id top}">
   <title>Package index - top_frame.tpl</title>
   <para>
    The top frame is used to display links to the highest level of documentation, the package table of contents, and the project-wide README/CHANGELOG/FAQ/NEWS/INSTALL files.
    <unorderedlist>
     <listitem><para><varname>{$packages}</varname> array - A listing of all packages documented, in format array('link' =&gt; name of the left_frame.tpl-generated package table of contents, 'title' =&gt; name of the package)</para></listitem>
     <listitem><para><varname>{$ric}</varname> array - all of the README/INSTALL/FAQ/NEWS/CHANGELOG files found, in format array('file' =&gt; name of the generated output, 'name' =&gt; displayable name of the parsed file)</para></listitem>
    </unorderedlist>
   </para>
  </refsect2>
  <refsect2 id="{@id indexes}">
   <title>Alphabetical Indexes - elementindex.tpl/pkgelementindex.tpl</title>
   <para>phpDocumentor's generated alphabetical element indexes are organized into an index of all elements in all packages (elementindex.tpl), and indexes of the elements in each individual package (pkgelementindex.tpl).  Their variables are the same, except for the addition of the <varname>{$package}</varname> variable in pkgelementindex.tpl.  Here are the template variables:</para>
   <para>
    <unorderedlist>
     <listitem><para><varname>{$index}</varname> array - the index items, see {@tutorial HTMLframesConverter.cls#template.indexes.index}</para></listitem>
     <listitem><para><varname>{$letters}</varname> array - a listing of each letter heading that should be used in the index</para></listitem>
    </unorderedlist>
   </para>
   <refsect3 id="{@id index}">
    <title>{$index} contents</title>
    <para>The {$index} variable is an array of index listings organized by their first letter.  All documented elements whose name begins with the letter A will be grouped into the sub-array 'a' => array(listing,listing,...).  The listing is organized as follows:</para>
    <para>
     <unorderedlist>
      <listitem><para><varname>{$index[xxx].name}</varname> string - name of the documented element</para></listitem>
      <listitem><para><varname>{$index[xxx].title}</varname> string - One of Variable, Global, Method, Function, Constant, Page, Include</para></listitem>
      <listitem><para><varname>{$index[xxx].link}</varname> string - hyperlink to the element's documentation, or the file included for include statements</para></listitem>
      <listitem><para><varname>{$index[xxx].listing}</varname> string - A fully-formatted index listing in English.</para></listitem>
     </unorderedlist>
    </para>
   </refsect3>
  </refsect2>
  <refsect2 id="{@id miscellaneous}">
   <title>Miscellaneous Templates - ric.tpl/todolist.tpl/errors.tpl</title>
   <para>These specialized templates will only be used in certain cases.
    <unorderedlist>
     <listitem><para>ric.tpl - This template is only used when files such as README, INSTALL, or CHANGELOG are detected in the base parse directory.</para></listitem>
     <listitem><para>todolist.tpl - This template is only used when a {@tutorial tags.todo.pkg} tag is used in documentation</para></listitem>
     <listitem><para>errors.tpl - This template is always generated, but only contains content when there are parsing syntax errors or logical conflicts in the parsed documentation</para></listitem>
    </unorderedlist>
   </para>
   <refsect3 id="{@id ric}">
    <title>ric.tpl - README/INSTALL/CHANGELOG display</title>
    <para>This is a very simple template.</para>
    <para>
     <unorderedlist>
      <listitem><para><varname>{$name}</varname> string - name of the file (README, for example)</para></listitem>
      <listitem><para><varname>{$contents}</varname> string - contents of the file, ready for display (no modification needed)</para></listitem>
     </unorderedlist>
    </para>
   </refsect3>
   <refsect3 id="{@id todolist}">
    <title>todolist.tpl - listings of @todo tags</title>
    <para>This is also a very simple template, and requires only a {section} tag to process the input.</para>
    <para>
     <unorderedlist>
      <listitem><para><varname>{$todos}</varname> array - Format: array(packagename =&gt; array('link' =&gt; string element containing @todo tag, 'todos' =&gt; array(todo item 1, todo item 2,...)),...)</para></listitem>
     </unorderedlist>
    </para>
   </refsect3>
   <refsect3 id="{@id errors}">
    <title>errors.tpl - phpDocumentor parsing/conversion warnings and errors</title>
    <para>This file is a tool used to help find mistakes in your own documentation, and greatly simplifies determining whether there is a bug in your documentation or in phpDocumentor itself.</para>
    <para>The structure of the errors array passed to errors.tpl is somewhat complex to learn, but easy to use once learned.  Every error or warning is grouped by the file that triggered it.  In the case of post-parsing errors, they are grouped into the pseudo-file &quot;Post-parsing&quot;.  Within each file, warnings are grouped separately from errors.  Hence, the structure looks something like:
     <programlisting role="php">
     <![CDATA[
array('Post-parsing' => array('warnings' => array(
                           array('name' => name of warning,
                                 'listing' => description of warning),...
                                      )
                              'errors' => array(
                           array('name' => name of error,
                                 'listing' => description of error),...
                                      )
                       ),
      'file1.php' => array(same structure as 'Post-parsing'),...
     );
    ]]>
     </programlisting>
    </para>
    <para>
     <unorderedlist>
      <listitem><para><varname>{$all}</varname> array - This is the structure listed above</para></listitem>
      <listitem><para><varname>{$files}</varname> array - a listing of all the files, useful for making a table of contents</para></listitem>
      <listitem><para><varname>{$title}</varname> string - this is hard-coded in English.  Override by simple using {assign var=&quot;title&quot; value=&quot;whatever you want&quot;} before including header.tpl</para></listitem>
     </unorderedlist>
    </para>
   </refsect3>
  </refsect2>
  <refsect2 id="{@id elements}">
   <title>PHP element templates - differences from Standard Converter</title>
   <para>For the basic PHP elements, define, include, function, global variable, class, and so on (see {@tutorial elements.pkg}), there are very few differences between the templates packaged with the HTMLframesConverter and the basic Converter.</para>
   <refsect3 id="{@id var}">
    <title>Class variables - var.tpl</title>
    <para>The only additional variable is:
     <unorderedlist>
      <listitem><para><varname>{$var_dest}</varname> string - html anchor used to pinpoint this class variable's documentation in the class file's documentation.  Use as in: <programlisting role="html"><![CDATA[<a name="#{$var_dest}"></a>]]></programlisting></para></listitem>
     </unorderedlist>
    </para>
   </refsect3>
   <refsect3 id="{@id var}">
    <title>Class methods - method.tpl</title>
    <para>The only additional variable is:
     <unorderedlist>
      <listitem><para><varname>{$method_dest}</varname> string - html anchor used to pinpoint this class method's documentation in the class file's documentation.  Use as in: <programlisting role="html"><![CDATA[<a name="#{$method_dest}"></a>]]></programlisting></para></listitem>
     </unorderedlist>
    </para>
   </refsect3>
   <refsect3 id="{@id var}">
    <title>Functions - function.tpl</title>
    <para>The only additional variable is:
     <unorderedlist>
      <listitem><para><varname>{$function_dest}</varname> string - html anchor used to pinpoint this function's documentation in the procedural file's documentation.  Use as in: <programlisting role="html"><![CDATA[<a name="#{$function_dest}"></a>]]></programlisting></para></listitem>
     </unorderedlist>
    </para>
   </refsect3>
   <refsect3 id="{@id define}">
    <title>Defines - define.tpl</title>
    <para>The only additional variable is:
     <unorderedlist>
      <listitem><para><varname>{$define_link}</varname> string - html anchor used to pinpoint this define statement's documentation in the procedural file's documentation.  Use as in: <programlisting role="html"><![CDATA[<a name="#{$define_link}"></a>]]></programlisting></para></listitem>
     </unorderedlist>
    </para>
   </refsect3>
   <refsect3 id="{@id global}">
    <title>Global Variables - global.tpl</title>
    <para>The only additional variable is:
     <unorderedlist>
      <listitem><para><varname>{$global_link}</varname> string - html anchor used to pinpoint this global variable's documentation in the procedural file's documentation.  Use as in: <programlisting role="html"><![CDATA[<a name="#{$global_link}"></a>]]></programlisting></para></listitem>
     </unorderedlist>
    </para>
   </refsect3>
   <refsect3 id="{@id include}">
    <title>Include Statements - include.tpl</title>
    <para>The only additional variable is:
     <unorderedlist>
      <listitem><para><varname>{$include_file}</varname> string - html anchor used to pinpoint this include statement's documentation in the procedural file's documentation.  Use as in: <programlisting role="html"><![CDATA[<a name="#{$include_file}"></a>]]></programlisting></para></listitem>
     </unorderedlist>
    </para>
   </refsect3>
  </refsect2>
 </refsect1>
</refentry>
