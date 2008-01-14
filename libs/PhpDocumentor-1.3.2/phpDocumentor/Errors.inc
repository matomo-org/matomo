<?php
/**
 * Error handling for phpDocumentor
 *
 * phpDocumentor :: automatic documentation generator
 * 
 * PHP versions 4 and 5
 *
 * Copyright (c) 2001-2006 Gregory Beaver
 * 
 * LICENSE:
 * 
 * This library is free software; you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation;
 * either version 2.1 of the License, or (at your option) any
 * later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package    phpDocumentor
 * @subpackage Errors
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  2001-2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id$
 * @filesource
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @see        parserDocBlock, parserInclude, parserPage, parserClass
 * @see        parserDefine, parserFunction, parserMethod, parserVar
 * @since      0.4
 */
/**
 * warning triggered when inheritance could be from more than one class
 */
define("PDERROR_MULTIPLE_PARENT",1);
/**
 * warning triggered when parent class doesn't exist
 */
define("PDERROR_PARENT_NOT_FOUND",2);
/**
 * warning triggered when an {@inline tag} is not terminated (no } before the * / ending the comment)
 */
define("PDERROR_UNTERMINATED_INLINE_TAG",3);
/**
 * warning triggered when inheritance could be from more than one class
 */
define("PDERROR_CLASS_EXISTS",4);
/**
 * warning triggered when inheritance could be from more than one class
 */
define("PDERROR_INHERITANCE_CONFLICT",5);
/**
 * warning triggered when a converter is passed to {@link phpDocumentor_IntermediateParser::addConverter()} that is not a class
 */
define("PDERROR_CONVERTER_NOT_FOUND",6);
/**
 * warning triggered when a converter is passed to {@link phpDocumentor_IntermediateParser::addConverter()} that is not a class
 */
define("PDERROR_NO_CONVERTERS",7);
/**
 * warning triggered when the arguments to @access are neither public nor private
 */
define("PDERROR_ACCESS_WRONG_PARAM",8);
/**
 * warning triggered when there are multiple @access tags in a docblock
 */
define("PDERROR_MULTIPLE_ACCESS_TAGS",9);
/**
 * warning triggered when there are multiple @return tags in a docblock
 */
define("PDERROR_MULTIPLE_RETURN_TAGS",10);
/**
 * warning triggered when there are multiple @var tags in a docblock
 */
define("PDERROR_MULTIPLE_VAR_TAGS",11);
/**
 * warning triggered when there are multiple @package tags in a docblock
 */
define("PDERROR_MULTIPLE_PACKAGE_TAGS",12);
/**
 * warning triggered when there are multiple @subpackage tags in a docblock
 */
define("PDERROR_MULTIPLE_SUBPACKAGE_TAGS",13);
/**
 * warning triggered when the package or subpackage name is illegal
 */
define("PDERROR_ILLEGAL_PACKAGENAME",14);
/**
 * warning triggered when there a @package tag is used in a function, define, method, var or include
 */
define("PDERROR_OVERRIDDEN_PACKAGE_TAGS",15);
/**
 * warning triggered when there a @subpackage tag is used in a function, define, method, var or include
 */
define("PDERROR_OVERRIDDEN_SUBPACKAGE_TAGS",16);
/**
 * warning triggered when classes in the same package have the same name
 */
define("PDERROR_CLASS_CONFLICT",17);
/**
 * warning triggered when classes in the same package have the same name
 */
define("PDERROR_UNKNOWN_TAG",18);
/**
 * warning triggered when there are multiple @name tags in a docblock
 */
define("PDERROR_MULTIPLE_NAME_TAGS",19);
/**
 * warning triggered when there are multiple @name tags in a docblock
 */
define("PDERROR_PACKAGEOUTPUT_DELETES_PARENT_FILE",20);
/**
 * warning triggered when there are multiple @name tags in a docblock
 */
define("PDERROR_GLOBAL_NOT_FOUND",21);
/**
 * warning triggered when there are multiple @name tags in a docblock
 */
define("PDERROR_MULTIPLE_GLOBAL_TAGS",22);
/**
 * warning triggered when there are multiple @name tags in a docblock
 */
define("PDERROR_MALFORMED_GLOBAL_TAG",23);
/**
 * warning triggered when an @ignore tag is used in a DocBlock preceding a method, variable, include, or global variable
 */
define("PDERROR_IGNORE_TAG_IGNORED",24);
/**
 * warning triggered when a duplicate element is encountered that will be ignored by the documentor
 */
define("PDERROR_ELEMENT_IGNORED",25);
/**
 * warning triggered when an entire page is ignored because of @access private
 */
define("PDERROR_PARSEPRIVATE",26);
/**
 * warning triggered when an entire page is ignored because of @access private
 */
define("PDERROR_UNKNOWN_COMMANDLINE",27);
/**
 * warning triggered when an entire page is ignored because of @access private
 */
define("PDERROR_NEED_WHITESPACE",28);
/**
 * warning triggered when an entire page is ignored because of @access private
 */
define("PDERROR_CLASS_PARENT_NOT_FOUND",29);
/**
 * warning triggered when a getClassByPackage is called and can't find the class
 */
define("PDERROR_CLASS_NOT_IN_PACKAGE",30);
/**
 * warning triggered when a { @source } inline tag is used in a docblock not preceding a function
 */
define("PDERROR_SOURCE_TAG_FUNCTION_NOT_FOUND",31);
/**
 * warning triggered when a docblock template is never turned off with /**#@-* / (no space)
 */
define("PDERROR_DB_TEMPLATE_UNTERMINATED",32);
/**
 * warning triggered when a docblock has an unmatched &lt;ol&gt; or &lt;ul&gt;
 */
define("PDERROR_UNMATCHED_LIST_TAG",33);
/**
 * warning triggered when another tag is nested in &lt;b&gt; - not allowed in phpDocumentor
 */
define("PDERROR_CANT_NEST_IN_B",34);
/**
 * warning triggered when a docbook tag is not properly matched
 */
define("PDERROR_UNMATCHED_TUTORIAL_TAG",35);
/**
 * warning triggered when an inline tag is found inside an xml tag name in a package page
 */
define("PDERROR_CANT_HAVE_INLINE_IN_TAGNAME",36);
/**
 * warning triggered when a tutorial is referenced via @tutorial/{ @tutorial} and is not found
 */
define("PDERROR_TUTORIAL_NOT_FOUND",37);
/**
 * warning triggered when a tutorial lists itself as a child tutorial
 */
define("PDERROR_TUTORIAL_IS_OWN_CHILD",38);
/**
 * warning triggered when a tutorial's child lists the parent tutorial as a child tutorial
 */
define("PDERROR_TUTORIAL_IS_OWN_GRANDPA",39);
/**
 * warning triggered when a tutorial's child in the .ini file doesn't exist in the
 * package and subpackage of the parent
 */
define("PDERROR_CHILD_TUTORIAL_NOT_FOUND",40);
/**
 * warning triggered when a <pdffunction:funcname /> tag is used in the PDF
 * Converter and no funcname is present (<pdffunction: />)
 */
define("PDERROR_PDFFUNCTION_NO_FUNC",41);
/**
 * warning triggered when a <pdffunction:funcname /> tag is used in the PDF
 * Converter and funcname is not a {@link Cezpdf} method
 */
define("PDERROR_PDF_METHOD_DOESNT_EXIST",42);
/**
 * warning triggered when a <pdffunction:funcname arg=$tempvar/> tag is used in the PDF
 * Converter and "tempvar" is not set from the return of a previous pdffunction tag
 */
define("PDERROR_PDF_TEMPVAR_DOESNT_EXIST",43);
/**
 * warning triggered when a subsection's title is asked for, but the subsection is not found
 */
define("PDERROR_TUTORIAL_SUBSECTION_NOT_FOUND",44);
/**
 * warning triggered when a subsection's title is asked for, but the subsection is not found
 */
define("PDERROR_UNTERMINATED_ATTRIB",45);
/**
 * warning triggered when no @package tag is used in a page-level or class-level DocBlock
 */
define("PDERROR_NO_PACKAGE_TAG",46);
/**
 * warning triggered when no @access private tag is used in a global variable/method/var
 * with _ as first char in name and --pear was specified
 */
define("PDERROR_PRIVATE_ASSUMED",47);
/**
 * warning triggered when an example's path from @example /path/to/example.php
 * is not found
 */
define("PDERROR_EXAMPLE_NOT_FOUND",48);
/**
 * warning triggered when an example's path from @example /path/to/example.php
 * is not found
 */
define("PDERROR_NO_CONVERTER_HANDLER",49);
/**
 * warning triggered when an example's path from @example /path/to/example.php
 * is not found
 */
define("PDERROR_INLINETAG_IN_SEE",50);
/**
 * warning triggered when an id attribute in a tutorial docbook tag is not
 * an {@}id} inline tag
 */
define("PDERROR_ID_MUST_BE_INLINE",51);
/**
 * warning triggered when an {@}internal}} tag is not closed
 */
define("PDERROR_INTERNAL_NOT_CLOSED",52);
/**
 * warning triggered when an {@}source} tag is found in a short description
 */
define("PDERROR_SOURCE_TAG_IGNORED",53);
/**
 * warning triggered when a child converter doesn't override getFormattedClassTrees()
 */
define("PDERROR_CONVERTER_OVR_GFCT",54);
/**
 * warning triggered when a package is already associated with a category, and
 * a new association is found
 */
define("PDERROR_PACKAGECAT_SET",55);
/**
 * warning triggered when text in a docblock list is not contained in
 * an <<li>> opening tag
 */
define("PDERROR_TEXT_OUTSIDE_LI",56);
/**
 * warning triggered when a DocBlock html tag is unclosed
 */
define("PDERROR_UNCLOSED_TAG",57);
/**
 * warning triggered by @filesource, if PHP < 4.3.0
 */
define("PDERROR_TAG_NOT_HANDLED",58);
/**
 * warning triggered by sourcecode="on", if PHP < 4.3.0
 */
define("PDERROR_SOURCECODE_IGNORED",59);
/**
 * warning triggered by an empty tag
 */
define("PDERROR_MALFORMED_TAG",60);
/**
 * warning triggered by more than 1 @category tag
 */
define("PDERROR_MULTIPLE_CATEGORY_TAGS",61);
/**
 * warning triggered by {@}inheritdoc} in a non-inheritable situation
 */
define("PDERROR_INHERITDOC_DONT_WORK_HERE",62);
/**
 * warning triggered by @example path/to/example with no title
 */
define("PDERROR_EMPTY_EXAMPLE_TITLE",63);
/**
 * warning triggered by non-existent template directory
 */
define("PDERROR_TEMPLATEDIR_DOESNT_EXIST",64);
/**
 * warning triggered by an unterminated entity in a tutorial
 */
define("PDERROR_UNTERMINATED_ENTITY",65);
/**
 * warning triggered by an unterminated entity in a tutorial
 */
define("PDERROR_BEAUTIFYING_FAILED",66);
/**
 * warning triggered by a function with no name
 *
 * <pre>
 * function ($params)
 * {
 * }
 * </pre>
 * triggers this error
 */
define("PDERROR_FUNCTION_HAS_NONAME",67);
/**
 * warning triggered by a page-level docblock preceding a source element
 *
 * <code>
 * <?php
 * /**
 *  * Page-level DocBlock
 *  * @package pagepackage
 *  *{@*}
 * include 'file.php';
 * </code>
 */
define("PDERROR_DOCBLOCK_CONFLICT", 68);
/**
 * warning triggered when a file does not contain a page-level docblock
 */
define("PDERROR_NO_PAGE_LEVELDOCBLOCK", 69);
/**
 * warning triggered when the first docblock in a file with a @package tag
 * precedes a class.  In this case, the class gets the docblock.
 */
define("PDERROR_DOCBLOCK_GOES_CLASS", 70);
/**
 * warning triggered in tutorial parsing if there is a missing {@id} inline tag
 */
define("PDERROR_NO_DOCBOOK_ID", 71);
/**
 * warning triggered if someone brilliant tries "class X extends X {"
 */
define("PDERROR_CANNOT_EXTEND_SELF", 72);
/**
 * warning triggered by improper "@uses {@link blah}"
 */
define("PDERROR_DUMB_USES",73);
/**
 * warning triggered if <<ul>> is nested inside <<ul>> and not <<li>>
 */
define("PDERROR_UL_IN_UL", 74);
/**
 * warning triggered if a command line option does not have a valid value passed in
 */
define("PDERROR_INVALID_VALUES", 75);
/**
 * warning triggered when {@}internal}} is nested inside another {@}internal}}
 */
define("PDERROR_NESTED_INTERNAL", 76);
/**
 * warning triggered when @todo is used on an include element
 */
define("PDERROR_NOTODO_INCLUDE", 77);
/**
 * warning triggered when the PHP version being used has dangerous bug/behavior
 */
 define("PDERROR_DANGEROUS_PHP_BUG_EXISTS", 80);

/**
 * Error messages for phpDocumentor parser warnings
 * @global array $GLOBALS['phpDocumentor_warning_descrip']
 * @name $phpDocumentor_warning_descrip
 */
$GLOBALS['phpDocumentor_warning_descrip'] =
    array(
        PDERROR_MULTIPLE_PARENT => 'Class %s has multiple possible parents, package inheritance aborted',
        PDERROR_PARENT_NOT_FOUND => 'Class %s parent %s not found',
        PDERROR_INHERITANCE_CONFLICT => 'Class %s in file %s has multiple possible parents named %s.  Cannot resolve name conflict,'."\n".' try ignoring a file that contains the conflicting parent class',
        PDERROR_UNKNOWN_TAG => 'Unknown tag "@%s" used',
        PDERROR_IGNORE_TAG_IGNORED => '@ignore tag used for %s element "%s" will be ignored',
        PDERROR_ELEMENT_IGNORED => "\n".'duplicate %s element "%s" in file %s will be ignored.'."\n".'Use an @ignore tag on the original if you want this case to be documented.',
        PDERROR_PARSEPRIVATE => "entire page %s ignored because of @access private.\nChoose -pp to enable parsing of private elements",
        PDERROR_CLASS_PARENT_NOT_FOUND => "class %s in package %s parent not found in @see parent::%s",
        PDERROR_CLASS_NOT_IN_PACKAGE => "class %s was not found in package %s",
        PDERROR_DB_TEMPLATE_UNTERMINATED => "docblock template never terminated with /**#@-*/",
        PDERROR_PDF_METHOD_DOESNT_EXIST => '<pdffunction:%s /> called, but pdf method "%s" doesn\'t exist',
        PDERROR_TUTORIAL_NOT_FOUND => "tutorial \"%s\" not found, does it exist?",
        PDERROR_CHILD_TUTORIAL_NOT_FOUND => 'child tutorial "%s" listed in %s not found in parent package "%s" subpackage "%s"',
        PDERROR_TUTORIAL_SUBSECTION_NOT_FOUND => 'tutorial %s subsection "%s" doesn\'t exist, but its title was asked for',
        PDERROR_NO_PACKAGE_TAG => 'no @package tag was used in a DocBlock for %s %s',
        PDERROR_PRIVATE_ASSUMED => '%s "%s" is assumed to be @access private because its name starts with _, but has no @access tag',
        PDERROR_EXAMPLE_NOT_FOUND => 'example file "%s" does not exist',
        PDERROR_SOURCE_TAG_IGNORED => '{@source} can only be used in the long description, not in the short description: "%s"',
        PDERROR_PACKAGECAT_SET => 'package %s is already in category %s, will now replace with category %s',
        PDERROR_SOURCECODE_IGNORED => 'sourcecode command-line option is ignored for PHP versions < 4.3.0',
        PDERROR_INHERITDOC_DONT_WORK_HERE => '{@inheritdoc} can only be used in the docblock of a child class',
        PDERROR_EMPTY_EXAMPLE_TITLE => 'Example file found at "%s" has no title, using "%s"',
        PDERROR_DOCBLOCK_CONFLICT => 'Page-level DocBlock precedes "%s %s", use another DocBlock to document the source element',
        PDERROR_NO_PAGE_LEVELDOCBLOCK => 'File "%s" has no page-level DocBlock, use @package in the first DocBlock to create one',
        PDERROR_DOCBLOCK_GOES_CLASS => 'DocBlock would be page-level, but precedes class "%s", use another DocBlock to document the file',
        PDERROR_NO_DOCBOOK_ID => 'Tutorial section %s "%s" has no id="{@id subsection}" tag ({@id} for refentry)',
        PDERROR_BEAUTIFYING_FAILED => 'Beautifying failed: %s',
        PDERROR_NOTODO_INCLUDE => '@todo on an include element is ignored (line %s, file %s)',
        );



//********************************************************



/**
 * Error messages for phpDocumentor parser errors
 * @global array $GLOBALS['phpDocumentor_error_descrip']
 * @name $phpDocumentor_error_descrip
 */
$GLOBALS['phpDocumentor_error_descrip'] = 
    array(
        PDERROR_UNTERMINATED_INLINE_TAG => 'Inline tag {@%s} in tag %s is unterminated, "%s"',
        PDERROR_CLASS_EXISTS => 'Class %s already exists in package "%s"',
        PDERROR_CONVERTER_NOT_FOUND => 'Converter %s specified by --output command-line option is not a class',
        PDERROR_NO_CONVERTERS => 'No Converters have been specified by --output command-line option',
        PDERROR_ACCESS_WRONG_PARAM => '@access was passed neither "public" nor "private."  Was passed: "%s"',
        PDERROR_MULTIPLE_ACCESS_TAGS => 'DocBlock has multiple @access tags, illegal. ignoring additional tag "@access %s"',
        PDERROR_MULTIPLE_RETURN_TAGS => 'DocBlock has multiple @return tags, illegal. ignoring additional tag "@return %s %s"',
        PDERROR_MULTIPLE_VAR_TAGS => 'DocBlock has multiple @var tags, illegal. ignoring additional tag "@var %s %s"',
        PDERROR_MULTIPLE_PACKAGE_TAGS => 'DocBlock has multiple @package tags, illegal. ignoring additional tag "@package %s"',
        PDERROR_MULTIPLE_SUBPACKAGE_TAGS => 'DocBlock has multiple @subpackage tags, illegal. ignoring additional tag "@subpackage %s"',
        PDERROR_ILLEGAL_PACKAGENAME => '@%s tag has illegal %s name "%s"',
        PDERROR_OVERRIDDEN_PACKAGE_TAGS => '%s %s\'s DocBlock has @package tag, illegal. ignoring tag "@package %s"',
        PDERROR_OVERRIDDEN_SUBPACKAGE_TAGS => '"%s" %s\'s DocBlock has @subpackage tags, illegal. ignoring tag "@subpackage %s"',
        PDERROR_CLASS_CONFLICT => 'class "%s" has multiple declarations in package %s, in file %s and file %s, documentation will have output errors!',
        PDERROR_MULTIPLE_NAME_TAGS => 'DocBlock has multiple @name tags, illegal. ignoring additional tag "@name %s"',
        PDERROR_PACKAGEOUTPUT_DELETES_PARENT_FILE => '-po (packageoutput) option deletes parent file "%s" containing class "%s."'."\n".'  Try using --defaultpackagename (-dn) %s to include the parent file in the same package as the class',
        PDERROR_GLOBAL_NOT_FOUND => 'global variable %s specified in @global tag was never found',
        PDERROR_MULTIPLE_GLOBAL_TAGS => '@global define tag already used for global variable "%s", ignoring @global %s',
        PDERROR_MALFORMED_GLOBAL_TAG => 'incorrect @global syntax.  Should be @global vartype $varname or @global vartype description',
        PDERROR_UNKNOWN_COMMANDLINE => 'Unknown command-line option "%s" encountered, use phpdoc -h for help',
        PDERROR_NEED_WHITESPACE => 'phpDocumentor programmer error - wordparser whitespace set to false in handleDocBlock, notify developers.  You should never see this error',
        PDERROR_SOURCE_TAG_FUNCTION_NOT_FOUND => '{@source} tag used in a docblock that isn\'t preceding a function',
        PDERROR_UNMATCHED_LIST_TAG => 'unmatched ol or ul tag in DocBlock, parsing will be incorrect',
        PDERROR_CANT_NEST_IN_B => 'Can\'t nest a code, pre, ul, or ol tag in a b tag in phpDocumentor DocBlock (%s tag nested)',
        PDERROR_UNMATCHED_TUTORIAL_TAG => 'While parsing extended documentation, "%s" tag was matched with "%s" endtag, missing endtag'."\ntag contents:\"%s\"",
        PDERROR_CANT_HAVE_INLINE_IN_TAGNAME => 'Can\'t have an inline tag inside a package page XML tag!',
        PDERROR_TUTORIAL_IS_OWN_CHILD => 'Tutorial %s lists itself as its own child in %s, illegal',
        PDERROR_TUTORIAL_IS_OWN_GRANDPA => 'Tutorial %s\'s child %s lists %s as its child in %s, illegal',
        PDERROR_PDFFUNCTION_NO_FUNC => 'Invalid pdffunction syntax: "<pdffunction: />", should be "<pdffunction:functionname [arg="value"...]/>"',
        PDERROR_PDF_TEMPVAR_DOESNT_EXIST => '<pdffunction:%s arg=%s /> called but temporary variable "%s" doesn\'t exist',
        PDERROR_UNTERMINATED_ATTRIB => 'Tutorial tag %s attribute %s is unterminated, current value "%s"',
        PDERROR_NO_CONVERTER_HANDLER => 'Handler for element of type "%s" called, but %s is not a method of %s',
        PDERROR_INLINETAG_IN_SEE => 'Inline tags are not allowed in a @see tag',
        PDERROR_ID_MUST_BE_INLINE => '<%s id="%s"> must be <%s id="{@id %s}">',
        PDERROR_INTERNAL_NOT_CLOSED => '{@internal was never terminated with }}',
        PDERROR_CONVERTER_OVR_GFCT => 'Converter "%s" must override getFormattedClassTrees() but doesn\'t',
        PDERROR_TEXT_OUTSIDE_LI => 'Text cannot be outside of li tag in a DocBlock list, parsing will be incorrect',
        PDERROR_UNCLOSED_TAG => 'Unclosed %s tag in DocBlock, parsing will be incorrect',
        PDERROR_TAG_NOT_HANDLED => '"%s" tag is not available in PHP versions less than 4.3.0, tag ignored',
        PDERROR_MALFORMED_TAG => '"%s" tag was used without any parameters, illegal',
        PDERROR_MULTIPLE_CATEGORY_TAGS => 'package has multiple @category tags, ignoring "@category %s"',
        PDERROR_TEMPLATEDIR_DOESNT_EXIST => 'template directory "%s" does not exist',
        PDERROR_UNTERMINATED_ENTITY => 'entity &%s is unterminated',
        PDERROR_FUNCTION_HAS_NONAME => 'function has no name (PHP error - test your file before parsing!)',
        PDERROR_CANNOT_EXTEND_SELF => 'class %s cannot extend itself - TEST YOUR CODE BEFORE PARSING',
        PDERROR_DUMB_USES => '@uses can only link to string data',
        PDERROR_UL_IN_UL => 'ul/ol tags cannot be directly nested inside ul/ol, nest inside li',
        PDERROR_INVALID_VALUES => 'command %s was passed "%s" but must be one of %s',
        PDERROR_NESTED_INTERNAL => '{@internal}} cannot be nested inside {@internal}}',
        PDERROR_DANGEROUS_PHP_BUG_EXISTS => 'Dangerous PHP Bug exists in PHP version %s that can be triggered by this parse (see PHP Bug #%s and PEAR Bug #%s)',
        );

/**
 * encapsulates warning information
 * @package phpDocumentor
 * @subpackage Errors
 */
class RecordWarning
{
    /**
     * name of global variable that descriptors for this warning/error is kept
     * @var string
     */
    var $type = 'phpDocumentor_warning_descrip';
    /**
     * file this error occurred in
     * @var string
     */
    var $file = false;
    /**
     * line number of the file this error occurred in
     * @var integer
     */
    var $linenum;
    /**
     * error string
     * @var string
     */
    var $data;
    /**
     * error number
     * @see Errors.inc
     * @var string
     */
    var $num;
    /**
     * @param string $file filename this error occurred in ({@link $file})
     * @param integer $linenum line number this error occurred on ({@link $linenum})
     * @param integer $num Error number defined in {@link Errors.inc}
     * @param string $data... variable number of strings, up to 4, to sprintf based on the error number
     */
    function RecordWarning($file,$linenum,$num)
    {
        $this->file = $file;
        $this->linenum = $linenum;
        $a = array('','','','');
        if (func_num_args()>3)
        {
            for($i=3;$i<func_num_args();$i++)
            {
                $a[$i - 3] = func_get_arg($i);
            }
        }

        $this->num = $num;
        $this->data = sprintf($GLOBALS[$this->type][$this->num],$a[0],$a[1],$a[2],$a[3]);
        $this->output();
    }
    
    /**
     * prints the warning
     */
    function output($string = false)
    {
        if ($string)
        {
            if ($this->file)
            return "WARNING in $this->file on line $this->linenum: $this->data\n";
            else
            return "WARNING: $this->data\n";
        }
        if ($this->file)
        phpDocumentor_out("WARNING in $this->file on line $this->linenum: $this->data\n");
        else
        phpDocumentor_out("WARNING: $this->data\n");
        flush();
    }
}

/**
 * encapsulates error information
 * @package phpDocumentor
 * @subpackage Errors
 */
class RecordError extends RecordWarning
{
    /**
     * name of global variable that descriptors for this warning/error is kept
     * @var string
     */
    var $type = 'phpDocumentor_error_descrip';

    /**
     * prints the error
     */
    function output($string = false)
    {
        if ($string)
        {
            if ($this->file)
            return "\n\tERROR in $this->file on line $this->linenum: $this->data\n";
            else
            return "\n\tERROR: $this->data\n";
        }
        if ($this->file)
        phpDocumentor_out("\n\tERROR in $this->file on line $this->linenum: $this->data\n");
        else
        phpDocumentor_out("\n\tERROR: $this->data\n");
        flush();
    }
}

/**
 * contains all the errors/warnings
 * @see $errors, $warnings
 * @package phpDocumentor
 * @subpackage Errors
 */
class ErrorTracker
{
    /**
     * array of {@link RecordError}s
     * @var array
     */
    var $errors = array();
    /**
     * array of {@link RecordWarning}s
     * @var array
     */
    var $warnings = array();
    /**
     * @var string
     */
    var $curfile = '';
    /**
     * @var integer
     */
    var $linenum = 0;
    
    /**
     * index in {@link $errors} of last error triggered
     * @var integer|false
     */
    var $lasterror = false;
    
    /**
     * index in {@link $warnings} of last warning triggered
     * @var integer|false
     */
    var $lastwarning = false;
    
    /**
     * This function subscribes to two events in the Parser in order to keep track of line number information and file name.
     * @param integer $num parser-passed event (see {@link PHPDOCUMENTOR_EVENT_NEWLINENUM, PHPDOCUMENTOR_EVENT_NEWFILE})
     * @param mixed $data either a line number if $num is PHPDOCUMENTOR_EVENT_NEWLINENUM or a file name if $num is PHPDOCUMENTOR_EVENT_NEWFILE
     */
    function handleEvent($num,$data)
    {
        switch($num)
        {
            case PHPDOCUMENTOR_EVENT_NEWLINENUM :
                $this->linenum = $data;
            break;
            case PHPDOCUMENTOR_EVENT_NEWFILE :
                $this->linenum = 0;
                $this->curfile = $data;
            break;
            // debugging
            case 1000000635 :
                phpDocumentor_out($this->curfile.' has '.$this->linenum.' lines'."\n");
                flush();
            break;
        }
    }

    /**
     * add a new warning to the {@link $warnings} array
     * @param integer $num error number from {@link Errors.inc}
     * @param string $data... up to 4 string parameters to sprintf() into the error string for error number $num
     */
    function addWarning($num)
    {
        $a = array('','','','');
        if (func_num_args()>1)
        {
            for($i=1;$i<func_num_args();$i++)
            {
                $a[$i - 1] = func_get_arg($i);
            }
        }
        $this->warnings[] = new RecordWarning($this->curfile,$this->linenum,$num,$a[0],$a[1],$a[2],$a[3]);
        $this->lastwarning = count($this->warnings) - 1;
    }

    /**
     * add a new error to the {@link $errors} array
     * @param integer $num error number from {@link Errors.inc}
     * @param string $data... up to 4 string parameters to sprintf() into the error string for error number $num
     */
    function addError($num)
    {
        $a = array('','','','');
        if (func_num_args()>1)
        {
            for($i=1;$i<func_num_args();$i++)
            {
                $a[$i - 1] = func_get_arg($i);
            }
        }
        $this->errors[] = new RecordError($this->curfile,$this->linenum,$num,$a[0],$a[1],$a[2],$a[3]);
        $this->lasterror = count($this->errors) - 1;
    }
    
    /**
     * add a new error to the {@link $errors} array and returns the error string
     * @param integer $num error number from {@link Errors.inc}
     * @param string $data... up to 4 string parameters to sprintf() into the error string for error number $num
     */
    function addErrorReturn($num)
    {
        $a = array('','','','');
        if (func_num_args()>1)
        {
            for($i=1;$i<func_num_args();$i++)
            {
                $a[$i - 1] = func_get_arg($i);
            }
        }
        $this->errors[] = new RecordError($this->curfile,$this->linenum,$num,$a[0],$a[1],$a[2],$a[3],false);
        $this->lasterror = count($this->errors) - 1;
    }
    
    /**
     * Get sorted array of all warnings in parsing/conversion
     * @return array
     */
    function &returnWarnings()
    {
        usort($this->warnings,array($this,"errorsort"));
        return $this->warnings;
    }
    
    /**
     * Get sorted array of all non-fatal errors in parsing/conversion
     * @return array
     */
    function &returnErrors()
    {
        usort($this->errors,array($this,"errorsort"));
        return $this->errors;
    }
    
    /**
     * @access private
     * @param RecordError|RecordWarning
     * @param RecordError|RecordWarning
     */
    function errorsort($a, $b)
    {
        if (!$a->file) return -1;
        if (!$b->file) return 1;
        if ($a->file == $b->file)
        {
            if ($a->linenum == $b->linenum) return 0;
            if ($a->linenum < $b->linenum) return -1;
            return 1;
        }
        return strnatcasecmp($a->file,$b->file);
    }
    
    /**
     * Get the error message of the last error
     * @return string
     */
    function returnLastError()
    {
        return $this->errors[$this->lasterror]->output(true);
    }

    /**
     * Get the warning message of the last warning
     * @return string
     */
    function returnLastWarning()
    {
        return $this->warnings[$this->lastwarning]->output(true);
    }
}

/**
 * @global ErrorTracker $GLOBALS['phpDocumentor_errors']
 * @name $phpDocumentor_errors
 */
$GLOBALS['phpDocumentor_errors'] = new ErrorTracker;

/**
 * @param integer $num error number from {@link Errors.inc}
 * @param string $data... up to 4 string parameters to sprintf() into the error string for error number $num
 * @see ErrorTracker::addError()
 */
function addError($num)
{
    global $phpDocumentor_errors;
    $a = array('','','','');
    if (func_num_args()>1)
    {
        for($i=1;$i<func_num_args();$i++)
        {
            $a[$i - 1] = func_get_arg($i);
        }
    }
    $phpDocumentor_errors->addError($num,$a[0],$a[1],$a[2],$a[3]);
}

/**
 * like {@link addError()} but exits parsing
 * @param integer $num error number from {@link Errors.inc}
 * @param string $data... up to 4 string parameters to sprintf() into the error string for error number $num
 * @global ErrorTracker repository for all errors generated by phpDocumentor
 * @see ErrorTracker::addError()
 */
function addErrorDie($num)
{
    global $phpDocumentor_errors;
    $a = array('','','','');
    if (func_num_args()>1)
    {
        for($i=1;$i<func_num_args();$i++)
        {
            $a[$i - 1] = func_get_arg($i);
        }
    }
    $phpDocumentor_errors->addErrorReturn($num,$a[0],$a[1],$a[2],$a[3]);
    die($phpDocumentor_errors->returnLastError());
}

/**
 * @param integer $num warning number from {@link Errors.inc}
 * @param string $data... up to 4 string parameters to sprintf() into the error string for error number $num
 * @global ErrorTracker repository for all errors generated by phpDocumentor
 * @see ErrorTracker::addWarning()
 */
function addWarning($num)
{
    global $phpDocumentor_errors;
    $a = array('','','','');
    if (func_num_args()>1)
    {
        for($i=1;$i<func_num_args();$i++)
        {
            $a[$i - 1] = func_get_arg($i);
        }
    }

    $phpDocumentor_errors->addWarning($num,$a[0],$a[1],$a[2],$a[3]);
}
?>
