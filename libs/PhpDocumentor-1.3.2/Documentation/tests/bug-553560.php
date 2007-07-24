<?php
/** @package tests */

/**
* Base class for all output converters.
* A Converter takes output from the {@link Parser} and converts it to template-friendly output.  A converter for the standard phpDocumentor
* template, {@link HTMLConverter}, is provided with this release.  Future releases will have support for other formats and templates, including
* DocBook, XML, and possibly other HTML templates.  The converter takes output directly from {@link NewRender} and using {@link walk()} it
* "walks" over an array of phpDocumentor elements, as represented by descendants of {@link parserElement}
*
* a converter must define the abstract function Convert (an example is {@link HTMLConverter::Convert()}), a function that takes a passed object
* and uses it to generate structures for an output template, or directly generates output.  Since all elements have their DocBlocks linked
* directly, this allows for more logical parsing than earlier versions of phpDocumentor.
*
* A Converter is passed several data structures in its constructor, all of which are optional in output and may be handled in any way
* the converter would like.  These structures are arrays:
* <ul>
*	<li>array of methods by class (see {@link NewRender::$methods})</li>
*	<li>array of variables by class (see {@link NewRender::$vars})</li>
*	<li>array of links to documented elements (see {@link NewRender::$links})</li>
*	<li>array of class parents by name (see {@link NewRender::$classtree})</li>
*	<li>array of class packages by classname (see {@link NewRender::$classpackages})</li>
*	<li>array of packages to document (see {@link NewRender::$packageoutput})</li>
*	<li>array of extended classes by parent classname (see {@link NewRender::$class_children})</li>
*	<li>array of all documented elements by name (see {@link NewRender::$elements})</li>
*	<li>array of all documented elements by name, split by package (see {@link NewRender::$pkg_elements})</li>
*	<li>boolean option, set to true to parse all elements marked @access private (see {@link NewRender::$parsePrivate})</li>
*	<li>boolean option, set to true to stop informative output while parsing (good for cron jobs) (see {@link NewRender::$quietMode})</li>
* </ul>
* @package tests
* @abstract
* @see parserDocBlock, parserInclude, parserPage, parserClass, parserDefine, parserFunction, parserMethod, parserVar
*/
class iConverter
{

	function walk()
	{
	}
}

/**
* @package tests
*/
class iParser
{
}

class iHTMLConverter extends iConverter
{
	function Convert()
	{
	}
}

/**
* @package tests
*/
class iparserElement
{
}

/**
* @package tests
*/
class iNewRender
{
	/**
	* array of methods by package, subpackage and class
	* format:
	* array(packagename =>
	*       array(subpackagename =>
	*             array(classname =>
	*                   array(methodname1 => {@link parserMethod} class,
	*                         methodname2 => {@link parserMethod} class,...)
	*					     )
	*                  )
	*            )
	*      )
	* @var array
	* @see Converter
	*/
	var $methods = array();
	
	/**
	* array of class variables by package, subpackage and class
	* format:
	* array(packagename =>
	*       array(subpackagename =>
	*             array(classname =>
	*                   array(variablename1 => {@link parserMethod} class,
	*                         variablename2 => {@link parserMethod} class,...)
	*					     )
	*                  )
	*            )
	*      )
	* @var array
	* @see Converter
	*/
	var $vars = array();
	
	/**
	* set in {@link phpdoc.inc} to the value of the parserprivate commandline option.
	* If this option is true, elements with an @access private tag will be parsed and displayed
	* @var bool
	*/
	var $parsePrivate = false;
	
	/**
	* this variable is used to prevent parsing of private elements if $parsePrivate is false.
	* it is also used by the packageoutput setting to prevent parsing of elements that aren't in the
	* desired output packages
	* @see $packageoutput
	* @see $parsePrivate
	*/
	var $private_class = false;
	
	/**
	* the workhorse of linking.
	* This array is an array of link objects of format:
	* [package][subpackage][eltype][elname] = descendant of {@link abstractLink}
	* eltype can be page|include|function|define|class|method|var
	* if eltype is method or var, the array format is:
	* [package][subpackage][eltype][class][elname]
	* @var array
	* @see functionLink, pageLink, classLink, defineLink, methodLink, varLink
	*/
	var $links = array();
	
	/**
	* a tree of class inheritance by name.
	* format:
	* array(childname => parentname,
	*       childname1 => parentname1,
	*       rootname => 0, ...
	*      )
	* @var array
	* @see Converter::generateClassTreeFromClass()
	*/
	var $classtree = array();
	
	/**
	* used in {@link Converter::getClassPackage()} to inherit package from parent classes.
	* format:
	* array(classname => array(array(package,subpackage),
	*                          array(package1,subpackage1),....
	                          )
		   )
	* If a name conflict exists between two packages, automatic inheritance will not work, and the packages will need
	* to be documented separately.
	* @var array
	*/
	var $classpackages = array();
	
	/**
	* used to set the output directory
	* @see setTargetDir()
	*/
	var $targetDir;
	
	/**
	* array of class inheritance indexed by parent class and package
	*
	* Format:
	* array(Packagename => array(ParentClassname1 => array(Child1name,Child2name),
	*                            ParentClassname2 => array(Child1name,Child2name),...
	*                           )
	*      )
	* @see Converter::getRootTree()
	* @var array
	*/
	
	/**
	* An array of extended classes by package and parent class
	* Format:
	* array(packagename => array(parentclass => array(childclassname1,
	*                                                 childclassname2,...
	*                                                )
	*                       )
	*      )
	* @var array
	*/
	var $class_children = array();
	
	var $elements = array();
	
	var $pkg_elements = array();

	var $pages = array();

	/**
	* array of packages to parser and output documentation for, if not all packages should be documented
	* Format:
	* array(package1,package2,...)
	*   or false if not set
	* Use this option to limit output similar to ignoring files.  If you have some temporary files that you don't want to specify by name
	* but don't want included in output, set a package name for all the elements in your project, and set packageoutput to that name.
	* the default package will be ignored.  Parsing speed does not improve.  If you want to ignore files for speed reasons, use the ignore
	* command-line option
	* @see Io
	* @var mixed
	*/
	var $packageoutput = false;
	
	/**
	* the functions which handle output from the {@link Parser}
	* @see handleEvent(), handleDocBlock(), handlePage(), handleClass(), handleDefine(), handleFunction(), handleMethod(), handleVar(),
	*      handlePackagePage(), handleInclude()
	*/
	var $event_handlers = array(
			'docblock' => 'handleDocBlock',
			'page' => 'handlePage',
			'class' => 'handleClass',
			'define' => 'handleDefine',
			'function' => 'handleFunction',
			'method' => 'handleMethod',
			'var' => 'handleVar',
			'packagepage' => 'handlePackagePage',
			'include' => 'handleInclude',
			);
	
	/**
	* data contains parsed structures for the current page being parsed
	* @var parserData
	* @see parserData
	*/
	var $data;
	
	/**
	* set to the name of the package of the current class being parsed
	* @var string
	*/
	var $classpackage = 'default';
	
	/**
	* set to the name of the subpackage of the current class being parsed
	* @var string
	*/
	var $classsubpackage = '';
	
	/**
	* set in {@link phpdoc.inc} to the value of the quitemode commandline option.
	* If this option is true, informative output while parsing will not be displayed (documentation is unaffected)
	* @var bool
	*/
	var $quietMode = false;


}
?>