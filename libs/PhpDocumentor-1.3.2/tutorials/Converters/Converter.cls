<refentry id="{@id}">
 <refnamediv>
  <refname>Writing a New Converter</refname>
  <refpurpose>Overview of how to write a new Converter</refpurpose>
 </refnamediv>
 <refsynopsisdiv>
  <authorgroup>
   <author>
    Joshua Eichorn
    <authorblurb>
     {@link mailto:jeichorn@phpdoc.org jeichorn@phpdoc.org}
    </authorblurb>
   </author>
   <author>
    Gregory Beaver
    <authorblurb>
     {@link mailto:cellog@sourceforge.com cellog@sourceforge.com}
    </authorblurb>
   </author>
  </authorgroup>
 </refsynopsisdiv>
 <refsect1 id="{@id intro}">
  <title>Introduction to Converters</title>
  <para>This documentation deals only with the advanced programming topic of creating a new output converter.  To learn how to use phpDocumentor, read the {@tutorial phpDocumentor/phpDocumentor.pkg}.  To learn how to extend an existing Converter, read {@tutorial Converters/Converters.pkg}</para>
 </refsect1>
 <refsect1 id="{@id concepts}">
  <title>Basic Concepts</title>
  <refsect2 id="{@id abstractdata}">
   <title>Abstract Parsing Data</title>
   <refsect3 id="{@id source}">
    <title>Source Code Elements</title>
    <para>A Converter's job is to take abstract data from parsing and create documentation.  What is the abstract data?  phpDocumentor is capable of documenting tutorials, and a php file and its re-usable or structurally important contents.  In other words, phpDocumentor can document:
     <itemizedlist>
      <listitem><para>A XML DocBook-based tutorial (see {@tutorial phpDocumentor/tutorials.pkg})</para></listitem>
      <listitem><para>Procedural Page (PHP source file)</para></listitem>
      <listitem><para>Include Statements</para></listitem>
      <listitem><para>Define Statements</para></listitem>
      <listitem><para>Global Variables</para></listitem>
      <listitem><para>Functions</para></listitem>
      <listitem><para>Classes</para></listitem>
      <listitem><para>Class Variables</para></listitem>
      <listitem><para>Class Methods</para></listitem>
     </itemizedlist>
    </para>
    <para>phpDocumentor represents these PHP elements using classes:
     <itemizedlist>
      <listitem><para>{@link parserTutorial}</para></listitem>
      <listitem><para>{@link parserData, parserPage}</para></listitem>
      <listitem><para>{@link parserInclude}</para></listitem>
      <listitem><para>{@link parserDefine}</para></listitem>
      <listitem><para>{@link parserGlobal}</para></listitem>
      <listitem><para>{@link parserFunction}</para></listitem>
      <listitem><para>{@link parserClass}</para></listitem>
      <listitem><para>{@link parserVar}</para></listitem>
      <listitem><para>{@link parserMethod}</para></listitem>
     </itemizedlist>
    </para>
    <para>This relationship between the source code and abstract data is quite clear, and very simple.  The data members of each abstract representation correspond with the information needed to display the documentation. All PHP elements contain a DocBlock, and this is represented by a class as well, {@link parserDocBlock}.  The elements of a DocBlock are simple:
     <itemizedlist>
      <listitem><para>Short Description, containing any number of inline tags</para></listitem>
      <listitem><para>Long Description, containing any number of inline tags</para></listitem>
      <listitem><para>Tags, some containing any number of inline tags in their general description field</para></listitem>
     </itemizedlist>
    </para>
    <para>phpDocumentor represents these elements using classes as well:
     <itemizedlist>
      <listitem><para>{@link parserDesc} for both short and long descriptions</para></listitem>
      <listitem><para>{@link parserInlineTag} for inline tags</para></listitem>
      <listitem><para>{@link parserTag} for regular tags</para></listitem>
     </itemizedlist>
    </para>
   </refsect3>
   <refsect3 id="{@id html}">
    <title>HTML-specific issues</title>
    <para>There are some other issues that Converters solve.  In HTML, a link is represented by an &lt;a&gt; tag, but in the PDF Converter, it is represented by a &lt;c:ilink&gt; tag.  How can we handle both cases?  Through another abstract class, the {@link abstractLink} and its descendants:
     <itemizedlist>
      <listitem><para>{@link tutorialLink}</para></listitem>
      <listitem><para>{@link pageLink}</para></listitem>
      <listitem><para>{@link defineLink}</para></listitem>
      <listitem><para>{@link globalLink}</para></listitem>
      <listitem><para>{@link functionLink}</para></listitem>
      <listitem><para>{@link classLink}</para></listitem>
      <listitem><para>{@link varLink}</para></listitem>
      <listitem><para>{@link methodLink}</para></listitem>
     </itemizedlist>
     Note the absence of an &quot;includeLink&quot; class - this is intentional, as only re-usable elements need to be linked.  An include statement is always attached to the file that it is in, and cannot be anywhere else.
    </para>
    <para>These abstract linking classes contain the information necessary to differentiate between the location of any of the element's documentation.  They are only used to allow linking to documentation, and not to source code.  A link is then converted to the appropriate text representation for the output format by {@link Converter::returnSee()} (a href=link for html, c:ilink:link for pdf, link linkend=link in xml:docbook, and so on).</para>
    <para>The other issues solved by a converter involves html in a DocBlock.  To allow better documentation, html is allowed in DocBlocks to format the output.  Unfortunately, this complicates output in other formats.  To solve this issue, phpDocumentor also parses out all allowed HTML (see {@tutorial phpDocumentor.howto.pkg#basics.desc} for more detailed information) into abstract structures:
     <itemizedlist>
      <listitem><para>&lt;b&gt; -- emphasize/bold text</para></listitem>
      <listitem><para>&lt;br&gt; -- hard line break, may be ignored by some converters</para></listitem>
      <listitem><para>&lt;code&gt; -- Use this to surround php code, some converters will highlight it</para></listitem>
      <listitem><para>&lt;i&gt; -- italicize/mark as important</para></listitem>
      <listitem><para>&lt;li&gt; -- list item</para></listitem>
      <listitem><para>&lt;ol&gt; -- ordered list</para></listitem>
      <listitem><para>&lt;p&gt; -- If used to enclose all paragraphs, otherwise it will be considered text</para></listitem>
      <listitem><para>&lt;pre&gt; -- Preserve line breaks and spacing, and assume all tags are text (like XML's CDATA)</para></listitem>
      <listitem><para>&lt;ul&gt; -- unordered list</para></listitem>
     </itemizedlist>
     is mapped to classes:
     <itemizedlist>
      <listitem><para>{@link parserB}</para></listitem>
      <listitem><para>{@link parserBr}</para></listitem>
      <listitem><para>{@link parserCode}</para></listitem>
      <listitem><para>{@link parserI}</para></listitem>
      <listitem><para>{@link parserList} - both types of lists are represented by this object, and each &lt;li&gt; is represented by an array item</para></listitem>
      <listitem><para>{@link parserPre}</para></listitem>
     </itemizedlist>
     &lt;p&gt; is represented by partitioning text into an array, where each array item is a new paragraph. (as in {@link parserDocBlock::$processed_desc})
    </para>
    <para>With these simple structures and a few methods to handle them, the process of writing a new converter is straightforward.</para>
   </refsect3>
   <refsect3 id="{@id output}">
    <title>Separation of data from display formatting</title>
    <para>phpDocumentor has been designed to keep as much formatting out of the source code as possible.  For many converters, there need be no new code written to support the conversion, as all output-specific information can be placed in template files.  However, the complexity of generating class trees does require the insertion of some code into the source, so at the bare minimum, the {@tutorial Converter.methods.cls#override.getroottree} method must be overridden.</para>
   </refsect3>
  </refsect2>
 </refsect1>
 <refsect1 id="{@id override}">
  <title>Methods that must be overridden</title>
  <para>Creating a new converter can be challenging, but should not be too complicated.  You need to override one data structure, {@link Converter::$leftindex}, to tell the Converter which of the individual element indexes your Converter will use.  You will also need to override a few methods for the Converter to work.  The most important relate to linking and output.</para>
  <para>A Converter must override these core methods:
   <itemizedlist>
    <listitem><para>{@tutorial Converter.methods.cls#core.convert} - take any descendant of parserElement or a parserPackagePage and convert it into output</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#core.returnsee} - takes a abstract link and returns a string that links to an element's documentation</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#core.returnlink} - takes a URL and text to display and returns an internet-enabled link</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#core.output} - generate output, or perform other cleanup activities</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#core.convert-ric} - Converts README/INSTALL/CHANGELOG file contents for inclusion in documentation</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#core.converterrorlog} - formats errors and warnings from {@link $phpDocumentor_errors}. see {@link HTMLframesConverter::ConvertErrorLog()}</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#core.getfunctionlink} - for all of the functions below, see {@link HTMLframesConverter::getFunctionLink()} for an example </para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#core.getclasslink}</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#core.getdefinelink}</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#core.getgloballink}</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#core.getmethodlink}</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#core.getvarlink}</para></listitem>
   </itemizedlist>
  </para>
  <para>A Converter may optionally implement these abstract methods:
   <itemizedlist>
    <listitem><para>{@tutorial Converter.methods.cls#override.endpage} - do any post-processing of procedural page elements, possibly output documentation for the page</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#override.endclass} - do any post-processing of class elements, possibly output documentation for the class</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#override.formatindex} - format the {@link $elements} array into an index see {@link HTMLframesConverter::generateElementIndex()}</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#override.formatpkgindex} - format the {@link $pkg_elements} array into an index see {@link HTMLframesConverter::generatePkgElementIndex()}</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#override.formatleftindex} - format the {@link $elements} array into an index see {@link HTMLframesConverter::formatLeftIndex()}</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#override.formattutorialtoc} - format the output of a {@}toc} tag, see {@link HTMLframesConverter::formatTutorialTOC()}</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#override.getroottree} - generates an output-specific tree of class inheritance</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#override.smartyinit} - initialize a {@link Smarty} template object</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#override.writesource} - write out highlighted source code for a parsed file</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#override.writeexample} - write out highlighted source code for an example</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#override.unmangle} - do any post-processing of class elements, possibly output documentation for the class</para></listitem>
   </itemizedlist>
  </para>
  <para>The following methods may need to be overridden for proper functionality, and are for advanced situations.
   <itemizedlist>
    <listitem><para>{@tutorial Converter.methods.cls#advanced.checkstate} - used by the {@link parserStringWithInlineTags::Convert()} cache to determine whether a cache hit or miss occurs</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#advanced.getstate} - used by the {@link parserStringWithInlineTags::Convert()} cache to save state for the next Convert() call</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#advanced.type-adjust} - used to enclose type names in proper tags as in {@link XMLDocBookConverter::type_adjust()}</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#advanced.postprocess} - called on all converted text, so that any illegal characters may be escaped.  The HTML Converters, for example,  pass all output through {@link htmlentities()}</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#advanced.gettutorialid} - called by the {@}id} inline tag to get the Converter's way of representing a document anchor</para></listitem>
   </itemizedlist>
  </para>
 </refsect1>
 <refsect1 id="{@id utility}">
  <title>Converter methods an extended converter should use</title>
  <para>
   <itemizedlist>
    <listitem><para>{@tutorial Converter.methods.cls#utility.getsortedclasstreefromclass} -- generating class trees by package</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#utility.hastutorial} -- use this to retrieve a tutorial, or determine if it exists</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#utility.gettutorialtree} -- use this to retrieve a hierarchical tree of tutorials that can be used to generate a table of contents for all tutorials</para></listitem>
    <listitem><para>{@tutorial Converter.methods.cls#utility.vardump-tree} -- use this to assist in debugging large tree structures of tutorials</para></listitem>
   </itemizedlist>
  </para>
 </refsect1>
</refentry>