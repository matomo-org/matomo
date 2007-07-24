<refentry id="{@id}">
 <refnamediv>
  <refname>Using the PDFParser XML templating language</refname>
  <refpurpose>Learn how the PDFParser XML templating language is constructed and used</refpurpose>
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
  <title>PDFParser Introduction</title>
  <para>This documentation deals only with the PDFParser XML templating language.  To learn how to use phpDocumentor, read the {@tutorial phpDocumentor/phpDocumentor.pkg}.  To learn how to write a new Converter, read {@tutorial Converters.pkg}</para>
 </refsect1>
 <refsect1 id="{@id overview}">
  <title>Overview</title>
  <para>The PDF:default Converter uses the powerful Cezpdf class library written by Wayne Munro.  Although the Cezpdf library is very versatile and useful, it is designed to be used as PHP code, and does not allow access to its methods from the text passed to it.  The PDFParser class was written to solve this problem.  Through the use of a few basic XML-based tags, and the versatile &lt;pdffunction /&gt; tag, one can access any feature of the Cezpdf library and do anything that it can do.  This is what makes it possible to use Smarty templates without modifying any code in phpDocumentor, a primary goal of the 1.2.0 release.</para>
 </refsect1>
 <refsect1 id="{@id tags}">
  <title>PDFParser XML tags</title>
  <refsect2 id="{@id text}">
   <title>&lt;text&gt;</title>
   <para>The &lt;text&gt; tag is a block tag (requires a closing tag).  All output that is not a PDFParser tag must be enclosed in this tag.  These are the parameters to the text tag:
    <unorderedlist>
     <listitem><para>size (<emphasis>required</emphasis>) - font size</para></listitem>
     <listitem><para>any legal parameter to {@link Cezpdf::ezText()} (all optional):
      <unorderedlist>
       <listitem><para>left - indentation from the left margin</para></listitem>
       <listitem><para>right - indentation from the right margin</para></listitem>
       <listitem><para>aleft - absolute indentation from the left side of the page</para></listitem>
       <listitem><para>aright - absolute indentation from the right side of the page</para></listitem>
       <listitem><para>justification - one of left, right, center/centre, full</para></listitem>
       <listitem><para><emphasis>either</emphasis> leading <emphasis>or</emphasis> spacing</para></listitem>
      </unorderedlist>
     </para></listitem>
    </unorderedlist>
   </para>
   <para>Examples might include:
    <unorderedlist>
     <listitem><para>&lt;text size=&quot;11&quot;&gt;Hello World!&lt;/text&gt;</para></listitem>
     <listitem><para>&lt;text size=&quot;11&quot; justification=&quot;right&quot;&gt;Hello World!&lt;/text&gt;</para></listitem>
     <listitem><para>&lt;text size=&quot;11&quot; left=&quot;10&quot; right=&quot;10&quot; justification=&quot;center&quot;&gt;Hello World!&lt;/text&gt;</para></listitem>
    </unorderedlist>
   </para>
  </refsect2>
  <refsect2 id="{@id font}">
   <title>&lt;font&gt;</title>
   <para>Unlike HTML, the &lt;font /&gt; tag is a self-enclosed tag, and is only used to select the font face (and future versions will also select color).  Syntax is &lt;font face=&quot;fontface&quot; /&gt;  The fontface must be present in the  Converters/PDF/default/templates/fonts/ subdirectory as a fontface-php.afm and fontface.afm file, otherwise Cezpdf will cause all kinds of PHP warnings.  This is an issue with Cezpdf that we are assisting the author in fixing, and will not be a problem in the future.</para>
  </refsect2>
  <refsect2 id="{@id newpage}">
   <title>&lt;newpage&gt;</title>
   <para>This is the simplest tag, use it as &lt;newpage /&gt; to tell Cezpdf to jump to the top of the next page in output.</para>
  </refsect2>
  <refsect2 id="{@id pdffunction}">
   <title>&lt;pdffunction&gt;</title>
   <para>The &lt;pdffunction&gt; tag is the most versatile of the PDFParser tags.  This tag can be used to arbitrarily call any Cpdf or Cezpdf method.  Arguments can be passed to the function both as literal values and using dynamic variables, both class variables and temporary variables.  The method chosen is selected as if pdffunction were a namespace rather than a </para>
   <para>Here are the parameters to the pdffunction:
    <unorderedlist>
     <listitem><para>return (<emphasis>optional</emphasis>) set a temporary variable to the value returned from the function.  This parameter allows a later reference to the temporary variable like: &lt;pdffunction:getYPlusOffset return=&quot;newy&quot; offset=&quot;0&quot; /&gt; &lt;pdffunction:addJpegFromFile arg=&quot;logo.jpg&quot; x=&quot;250&quot; y=$newy /&gt;</para></listitem>
    </unorderedlist>
    any other parameter represents an argument.  Since the parameters passed are simply passed by value to the method, the names can be anything that is useful to understanding how they work.  &lt;pdffunction:ezStartPageNumbers x=&quot;500&quot; y=&quot;28&quot; size=&quot;10&quot; pos=&quot;&quot; pattern=&quot;{ldelim}PAGENUM{rdelim} of {ldelim}TOTALPAGENUM{rdelim}&quot; num=&quot;1&quot; /&gt; could just as easily be &lt;pdffunction:ezStartPageNumbers arg=&quot;500&quot; foo=&quot;28&quot; bar=&quot;10&quot; mylord=&quot;&quot; goodnessgracious=&quot;{ldelim}PAGENUM{rdelim} of {ldelim}TOTALPAGENUM{rdelim}&quot; num=&quot;1&quot; /&gt; and the code would still work splendidly.  The first way is obviously easier to handle, and so is recommended.
   </para>
   <para>Most arguments to the method will be literals, like numbers or strings, but some arguments may need to be more dynamic, based on the values returned from other pdffunction tags.  To do this, use the return parameter as described above, and reference the temporary variable as if it were a php variable like &lt;pdffunction:whatever arg=$tempvar /&gt;  It may even be necessary to access a data member of the pdf class.  In this case, use $this-&gt;datamember as in &lt;pdffunction:whatever arg=$this-&gt;datamember /&gt;</para>
  </refsect2>
 </refsect1>
</refentry>
