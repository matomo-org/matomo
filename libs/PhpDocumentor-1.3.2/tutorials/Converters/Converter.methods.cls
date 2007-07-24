<refentry id="{@id}">
 <refnamediv>
  <refname>Writing a Converter, Methods</refname>
  <refpurpose>Learn what methods need to be defined in a new Converter</refpurpose>
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
  <title>Introduction to Converter Methods</title>
  <para>This Tutorial describes the methods that a New Converter must use to become fully functional.  A great deal of functionality is handled by the parent Converter class, and a child converter's only job is to convert sorted data from abstract classes into full-fledged output.</para>
  <para>There are literally no restrictions on how a converter does its job.  If the predefined {@link Converter::walk()} method does not do anything a converter needs to do, all one needs to do is override walk() and perform any necessary processing directly on the parsed elements.  Be forewarned that this is a bit like trying to fix a problem in a program by tinkering with the operating system, and we cannot be even in the slightest responsible for any suffering you might incur.</para>
  <para>Having said that nasty disclaimer, let's begin with the good news.  The only thing a standard converter needs to know is that it must define a few methods.  The only crucial methods to define are {@tutorial Converter.methods.cls#core.convert}, {@tutorial Converter.methods.cls#core.returnsee}, and {@tutorial Converter.methods.cls#core.returnlink}.</para>
  <para>The Convert() method expects it will be passed all parsed data.  Data will be passed either by file or by package, but in either case, all data will pass through Convert(), and so must be handled by it.  Good programming practice suggests having one method for every parsed element, and that is what the bundled Converters do.</para>
  <para>returnSee() is the core of phpDocumentor's advanced linking {@tutorial tags.see.pkg}, {@tutorial tags.tutorial.pkg}, {@tutorial tags.inlinetutorial.pkg} and {@tutorial tags.inlinelink.pkg}.  returnLink() handles hyperlinks to URLs on the internet.</para>
  <para>Beyond these three essential methods, there are a slew of methods that assist phpDocumentor in creating its advanced source highlighting feature, in creating an error log and todo list, and a few advanced functions that speed up conversion through a cache.  Beyond this, there are many helper functions that assist in the creation of indexes, class trees, inheritance and conflict information, and so on.  phpDocumentor does not restrict their use, to allow for future possibilities.</para>
  <para>For most cases, it is best to start writing a new converter by copying the code from one of the existing converters, and then begin modifying methods to generate the appropriate output.  In addition, this will leverage the existing use of the Smarty template engine to separate specifics of output formatting from the data source.  In theory, one could create a single converter and use many templates for creating different output, but this is often not possible because templates are still organized into files, and this is invariant.  In the future of phpDocumentor, we may have a base converter from which all other converters can extend, greatly simplifying development.  For now, development is still relatively easy, after the basic concepts behind phpDocumentor are grasped.</para>
 </refsect1>
 <refsect1 id="{@id core}">
  <title>Converter core methods that must be overridden</title>
  <refsect2 id="{@id convert}">
   <title>Convert()</title>
   <para>The {@link Converter::Convert()} method is called by {@link Converter::walk()} to process all Converter classes:
    <itemizedlist>
     <listitem><para>{@link parserData} - representation of a file</para></listitem>
     <listitem><para>{@link parserInclude} - representation of include statements</para></listitem>
     <listitem><para>{@link parserDefine} - representation of define statements</para></listitem>
     <listitem><para>{@link parserFunction} - representation of functions</para></listitem>
     <listitem><para>{@link parserGlobal} - representation of global variables</para></listitem>
     <listitem><para>{@link parserClass} - representation of a class</para></listitem>
     <listitem><para>{@link parserVar} - representation of class variables</para></listitem>
     <listitem><para>{@link parserMethod} - representation of class methods</para></listitem>
     <listitem><para>{@link parserPage} - representation of old Package Pages (deprecated)</para></listitem>
     <listitem><para>{@link parserTutorial} - representation of tutorials (like what you are reading now)</para></listitem>
    </itemizedlist>
    It is up to this method to distribute processing of these elements, or do any post-processing that is necessary.  All of the converters included with phpDocumentor process elements by passing them to individual Convert*() methods like {@link HTMLframesConverter::ConvertClass()}, and one can simply copy this style, or write a completely new method.
   </para>
   <para>Data is passed to the Converter organized by file, procedural elements first followed by class elements, unless {@link Converter::$sort_absolutely_everything} is set to true.  Then data is passed organized by package, with all files and procedural elements first, followed by all class elements.  The PDFdefaultConverter set <varname>$sort_absolutely_everything</varname> = true, and HTML converters set <varname>$sort_absolutely_everything</varname> = false</para>
  </refsect2>
  <refsect2 id="{@id returnsee}">
   <title>returnSee()</title>
   <para>This method takes a {@link abstractLink} class descendant and converts it to an output-format compatible link to an element's documentation.  HTML converters convert the link to an &lt;a href=&quot;&quot;&gt; tag, the XML converter changes it to a &lt;link linkend=&quot;&quot;&gt; tag, etc.  This function is also responsible for distinguishing between sections of documentation.  All information needed to distinguish between elements is included in the data members of a link class, it is up to the returnSee() method to translate that into a unique string identifier.  A good strategy is to write a function that takes a link class and returns a unique identifier as in {@link XMLDocBookConverter::getId()}, and then reference this function to grab identification strings for defining anchors in the generated output.</para>
  </refsect2>
  <refsect2 id="{@id returnlink}">
   <title>returnLink()</title>
   <para>This method takes a URL and converts it to an external link to that URL in the appropriate output format.  In HTML, the link will be a standard &lt;a href=&quot&quot&gt; tag.</para>
  </refsect2>
  <refsect2 id="{@id output}">
   <title>Output()</title>
   <para>This method is called at the end of the walk() method.  It may be used to generate output or do any necessary cleanup.  Nothing is required of Output, and it may do nothing as it does in the HTML converters, which write output continuously as converting, or perform special operations as in {@link CHMdefaultConverter::Output()}.</para>
  </refsect2>
  <refsect2 id="{@id convert-ric}">
   <title>Convert_RIC()</title>
   <para>This method is called to format the contents of README, INSTALL, CHANGELOG, NEWS, and FAQ files grabbed from the base parse directory.  This function allows standard distribution files to be embedded in generated documentation.  A Converter should format these files in a monospace font, if possible.</para>
  </refsect2>
  <refsect2 id="{@id converterrorlog}">
   <title>ConvertErrorLog()</title>
   <para>This method is called at the end of parsing to convert the error log into output for viewing later by the developer.  Error output is very useful for finding mistakes in documentation comments.  A simple solution is to copy the {@link HTMLframesConverter::ConvertErrorLog()} function and the errors.tpl template file to the new converter.  The error log should not be embedded in generated output, as no end-user wants to see that information.</para>
  </refsect2>
  <refsect2 id="{@id getfunctionlink}">
   <title>getFunctionLink()</title>
   <para>This function should call {@link Converter::getFunctionlink()}, and then use {@tutorial Converter.methods.cls#core.returnsee} to return a string containing a converter-specific link.  Code can literally be copied from any of the converters.  This function is called by the {@link parserFunction::getLink()} method.</para>
  </refsect2>
  <refsect2 id="{@id getclasslink}">
   <title>getClassLink()</title>
   <para>This function should call {@link Converter::getClasslink()}, and then use {@tutorial Converter.methods.cls#core.returnsee} to return a string containing a converter-specific link.  Code can literally be copied from any of the converters.  This function is called by the {@link parserClass::getLink()} method.</para>
  </refsect2>
  <refsect2 id="{@id getdefinelink}">
   <title>getDefineLink()</title>
   <para>This function should call {@link Converter::getDefinelink()}, and then use {@tutorial Converter.methods.cls#core.returnsee} to return a string containing a converter-specific link.  Code can literally be copied from any of the converters.  This function is called by the {@link parserDefine::getLink()} method.</para>
  </refsect2>
  <refsect2 id="{@id getgloballink}">
   <title>getGlobalLink()</title>
   <para>This function should call {@link Converter::getGloballink()}, and then use {@tutorial Converter.methods.cls#core.returnsee} to return a string containing a converter-specific link.  Code can literally be copied from any of the converters.  This function is called by the {@link parserGlobal::getLink()} method.</para>
  </refsect2>
  <refsect2 id="{@id getmethodlink}">
   <title>getMethodLink()</title>
   <para>This function should call {@link Converter::getMethodlink()}, and then use {@tutorial Converter.methods.cls#core.returnsee} to return a string containing a converter-specific link.  Code can literally be copied from any of the converters.  This function is called by the {@link parserMethod::getLink()} method.</para>
  </refsect2>
  <refsect2 id="{@id getvarlink}">
   <title>getVarLink()</title>
   <para>This function should call {@link Converter::getVarlink()}, and then use {@tutorial Converter.methods.cls#core.returnsee} to return a string containing a converter-specific link.  Code can literally be copied from any of the converters.  This function is called by the {@link parserVar::getLink()} method.</para>
  </refsect2>
 </refsect1>
 <refsect1 id="{@id override}">
  <title>Abstract Methods that must be overridden</title>
  <refsect2 id="{@id endpage}">
   <title>endPage()</title>
   <para>This method is called by the {@link Converter::walk()} method when all procedural and class elements on a page have been processed.  The purpose is to allow the converter to reset any state variables that apply to the page.  endPage() is not called by {@link Converter::walk_everything()} and so if your converter sets {@link Converter::$sort_absolutely_everything} = true, you will not need to implement this method.</para>
  </refsect2>
  <refsect2 id="{@id endclass}">
   <title>endClass()</title>
   <para>This method is called by the {@link Converter::walk()} method when all class elements (methods, variables) in a class have been processed.  The purpose is to allow the converter to reset any state variables that apply to the page.  endPage() is not called by {@link Converter::walk_everything()} and so if your converter sets {@link Converter::$sort_absolutely_everything} = true, you will not need to implement this method.</para>
  </refsect2>
  <refsect2 id="{@id formatindex}">
   <title>formatIndex()</title>
   <para>This method is called before processing any elements, and is not required to do anything.  The intent is to allow processing of a global element index to occur in a separate method, which logically separates activities performed by the Converter.  See the {@link HTMLframesConverter::formatIndex()} method for details on one possible implementation</para>
  </refsect2>
  <refsect2 id="{@id formatpkgindex}">
   <title>formatPkgIndex()</title>
   <para>Like formatIndex(), this method is called before processing any elements, and is not required to do anything.  The intent is to allow processing of a package-level index to occur in a separate method, which logically separates activities performed by the Converter.  See the {@link HTMLframesConverter::formatPkgIndex()} method for details on one possible implementation</para>
  </refsect2>
  <refsect2 id="{@id formatleftindex}">
   <title>formatLeftIndex()</title>
   <para>Like formatIndex(), this oddly-named method is called before processing any elements, and is not required to do anything.  The name comes from the original JavaDoc design of putting an index in the lower left frame of output.  The indexes needed by this function are automatically generated based on the value of {@link Converter::$leftindex}.  These indexes are arrays of links organized by package and subpackage.  Left indexes can be generated for files (procedural pages), functions, classes, constants (define statements), and global variables.</para>
  </refsect2>
  <refsect2 id="{@id formattutorialtoc}">
   <title>formatTutorialTOC()</title>
   <para>This method is used to format the automatically generated table of contents from an {@tutorial tags.inlinetoc.pkg} in a tutorial.  The data structure passed is an array generated by {@link parserTocInlineTag::Convert()} that contains each entry.  All that formatTutorialTOC needs to do is structure the inherent hierarchy of the original DocBook tutorial source according to the requirements of the output format.  This is remarkably simple, and can often be implemented simply by passing the entire array to a template engine, as HTMLframesConverter::formatTutorialTOC() does.</para>
  </refsect2>
  <refsect2 id="{@id getroottree}">
   <title>getRootTree()</title>
   <para>This is a very fast non-recursive method that generates a string containing class trees.  In earlier phpDocumentor versions, the getRootTree() method was recursive, and both consumed too much memory and was very slow.  It was also easier to understand.  The data structure processed in the current version of phpDocumentor still contains the same information, but requires a precise algorithm to process.  The good news is that we have worked out this algorithm for you, and to implement this method in a new converter, you need only copy the code from one of the existing converters.  See {@link Converter::getRootTree()} for low-level details.</para>
  </refsect2>
  <refsect2 id="{@id smartyinit}">
   <title>SmartyInit()</title>
   <para>The primary template engine for phpDocumentor is the {@link http://smarty.php.net Smarty} template engine.  This utility function is used to initialize a new Smarty template object with pre-initialized variables.  The Converter::newSmarty initializes several variables, see {@link Converter::newSmarty()} for details.  If your converter needs to have variables available to every template, extend this function, and use {@link Smarty::assign()} to assign values to variables.  Note that the function must use code that returns a reference like:</para>
   <para>
    <programlisting>
    <![CDATA[
    function &SmartyInit(&$templ)
    {
        $somevalue = "whatever you want, babe";
        $templ->assign("variable",$somevalue);
        return $templ;
    }
    ]]>
    </programlisting>
   </para>
  </refsect2>
  <refsect2 id="{@id writesource}">
   <title>writeSource()</title>
   <para></para>
  </refsect2>
  <refsect2 id="{@id writeexample}">
   <title>writeExample()</title>
   <para></para>
  </refsect2>
  <refsect2 id="{@id unmangle}">
   <title>unmangle()</title>
   <para>This function is used by the pre-PHP 4.2.3 {@tutorial tags.inlinesource.pkg} tag's {@link parserInlineSourceTag::Convert()} method to process output from {@link highlight_string()}.  In non-HTML converters, it should return an empty string.  For advanced source highlighting, implement highlightSource(), highlightDocBlockSource().</para>
  </refsect2>
 </refsect1>
 <refsect1 id="{@id advanced}">
  <title>Methods that may optionally be overridden</title>
  <refsect2 id="{@id checkstate}">
   <title>checkState()</title>
  </refsect2>
  <refsect2 id="{@id getstate}">
   <title>getState()</title>
  </refsect2>
  <refsect2 id="{@id type-adjust}">
   <title>type_adjust()</title>
   <para>This method is passed type names from {@tutorial tags.param.pkg}/{@tutorial tags.return.pkg}/{@tutorial tags.var.pkg} tags and can be used to enclose types in special formatting.  The only converter that really uses this capability is the DocBook converter.  Since the type name can be a link class, it is possible to determine what kind of element the type is, a constant, class, or anything that can be documented.  For example, the DocBook converter encloses classes in &lt;classname&gt; tags, constants in &lt;constant&gt; tags, variables in &lt;varname&gt; tags, and also will enclose functions in &lt;function&gt; tags.</para>
  </refsect2>
  <refsect2 id="{@id postprocess}">
   <title>postProcess()</title>
   <para>This method takes a string and should &quot;escape&quot; any illegal characters.  For instance, in HTML, all &quot;&lt;&quot; characters must be escaped to &quot;&amp;lt;&quot; entities.  This is to prevent documentation comments from being interpreted as text modifiers in the output format.</para>
  </refsect2>
  <refsect2 id="{@id gettutorialid}">
   <title>getTutorialId()</title>
   <para>This method is used to pass back a valid ID for a tutorial section.  In HTML, most of the id is coded in the file containing the data, and only the section and subsection need to be used to differentiate sections.  In DocBook, the id is packages.categoryname.packagename.subpackagename.class/filename.section.subsection, a much more involved scenario.</para>
  </refsect2>
 </refsect1>
 <refsect1 id="{@id utility}">
  <title>Utility methods</title>
  <refsect2 id="{@id getsortedclasstreefromclass}">
   <title>getSortedClassTreeFromClass()</title>
   <para>This method is used by {@tutorial Converter.methods.cls#override.getroottree} to create class trees for a package.  Also see the docs in {@link Converter::getSortedClassTreeFromClass()}</para>
  </refsect2>
  <refsect2 id="{@id hastutorial}">
   <title>hasTutorial()</title>
   <para>This helper method is used to determine whether a particular tutorial has been parsed, and can be used to perform association between parsed elements and their tutorials</para>
  </refsect2>
  <refsect2 id="{@id gettutorialtree}">
   <title>getTutorialTree()</title>
   <para>This helper method returns a recursive data structure containing all tutorials in the table-of-contents style hierarchy that they should have in final output.  This is used to create a table of contents for tutorials in the left frame of the HTML Converters, and in the PDF Converter, to determine indentation level in the top-level table of contents.</para>
  </refsect2>
  <refsect2 id="{@id vardump-tree}">
   <title>vardump_tree()</title>
   <para>This function is a utility function.  It performs what looks like a {@link var_dump()} of any recursive data structure, but if it encounters an object, it only prints out its class and name if the $name data member exists.  This allows checking of the tree structure without having to wade through pages of class information, and was very useful for debugging, so we added it for your use.</para>
  </refsect2>
 </refsect1>
</refentry>
