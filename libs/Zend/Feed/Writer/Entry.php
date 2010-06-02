<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Entry.php 22065 2010-04-30 14:04:57Z padraic $
 */

/**
 * @see Zend_Date
 */
// require_once 'Zend/Date.php';

/**
 * @see Zend_Date
 */
// require_once 'Zend/Uri.php';

// require_once 'Zend/Feed/Writer/Source.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Writer_Entry
{

    /**
     * Internal array containing all data associated with this entry or item.
     *
     * @var array
     */
    protected $_data = array();
    
    /**
     * Registered extensions
     *
     * @var array
     */
    protected $_extensions = array();
    
    /**
     * Holds the value "atom" or "rss" depending on the feed type set when
     * when last exported.
     *
     * @var string
     */
    protected $_type = null;
    
    /**
     * Constructor: Primarily triggers the registration of core extensions and
     * loads those appropriate to this data container.
     *
     * @return void
     */
    public function __construct()
    {
        Zend_Feed_Writer::registerCoreExtensions();
        $this->_loadExtensions();
    }

    /**
     * Set a single author
     *
     * @param  int $index
     * @return string|null
     */
    public function addAuthor($name, $email = null, $uri = null)
    {
        $author = array();
        if (is_array($name)) {
            if (!array_key_exists('name', $name) 
                || empty($name['name']) 
                || !is_string($name['name'])
            ) {
                // require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('Invalid parameter: author array must include a "name" key with a non-empty string value');
            }
            $author['name'] = $name['name'];
            if (isset($name['email'])) {
                if (empty($name['email']) || !is_string($name['email'])) {
                    // require_once 'Zend/Feed/Exception.php';
                    throw new Zend_Feed_Exception('Invalid parameter: "email" array value must be a non-empty string');
                }
                $author['email'] = $name['email'];
            }
            if (isset($name['uri'])) {
                if (empty($name['uri']) 
                    || !is_string($name['uri']) 
                    || !Zend_Uri::check($name['uri'])
                ) {
                    // require_once 'Zend/Feed/Exception.php';
                    throw new Zend_Feed_Exception('Invalid parameter: "uri" array value must be a non-empty string and valid URI/IRI');
                }
                $author['uri'] = $name['uri'];
            }
        /**
         * @deprecated
         * Array notation (above) is preferred and will be the sole supported input from ZF 2.0
         */
        } else {
            if (empty($name['name']) || !is_string($name['name'])) {
                // require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('Invalid parameter: "name" must be a non-empty string value');
            }
            $author['name'] = $name;
            if (isset($email)) {
                if (empty($email) || !is_string($email)) {
                    // require_once 'Zend/Feed/Exception.php';
                    throw new Zend_Feed_Exception('Invalid parameter: "email" value must be a non-empty string');
                }
                $author['email'] = $email;
            }
            if (isset($uri)) {
                if (empty($uri) || !is_string($uri) || !Zend_Uri::check($uri)) {
                    // require_once 'Zend/Feed/Exception.php';
                    throw new Zend_Feed_Exception('Invalid parameter: "uri" value must be a non-empty string and valid URI/IRI');
                }
                $author['uri'] = $uri;
            }
        }
        $this->_data['authors'][] = $author;
    }

    /**
     * Set an array with feed authors
     *
     * @return array
     */
    public function addAuthors(array $authors)
    {
        foreach($authors as $author) {
            $this->addAuthor($author);
        }
    }
    
    /**
     * Set the feed character encoding
     *
     * @return string|null
     */
    public function setEncoding($encoding)
    {
        if (empty($encoding) || !is_string($encoding)) {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string');
        }
        $this->_data['encoding'] = $encoding;
    }

    /**
     * Get the feed character encoding
     *
     * @return string|null
     */
    public function getEncoding()
    {
        if (!array_key_exists('encoding', $this->_data)) {
            return 'UTF-8';
        }
        return $this->_data['encoding'];
    }

    /**
     * Set the copyright entry
     *
     * @return string|null
     */
    public function setCopyright($copyright)
    {
        if (empty($copyright) || !is_string($copyright)) {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string');
        }
        $this->_data['copyright'] = $copyright;
    }

    /**
     * Set the entry's content
     *
     * @return string|null
     */
    public function setContent($content)
    {
        if (empty($content) || !is_string($content)) {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string');
        }
        $this->_data['content'] = $content;
    }

    /**
     * Set the feed creation date
     *
     * @return string|null
     */
    public function setDateCreated($date = null)
    {
        $zdate = null;
        if (is_null($date)) {
            $zdate = new Zend_Date;
        } elseif (ctype_digit($date) && strlen($date) == 10) {
            $zdate = new Zend_Date($date, Zend_Date::TIMESTAMP);
        } elseif ($date instanceof Zend_Date) {
            $zdate = $date;
        } else {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid Zend_Date object or UNIX Timestamp passed as parameter');
        }
        $this->_data['dateCreated'] = $zdate;
    }

    /**
     * Set the feed modification date
     *
     * @return string|null
     */
    public function setDateModified($date = null)
    {
        $zdate = null;
        if (is_null($date)) {
            $zdate = new Zend_Date;
        } elseif (ctype_digit($date) && strlen($date) == 10) {
            $zdate = new Zend_Date($date, Zend_Date::TIMESTAMP);
        } elseif ($date instanceof Zend_Date) {
            $zdate = $date;
        } else {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid Zend_Date object or UNIX Timestamp passed as parameter');
        }
        $this->_data['dateModified'] = $zdate;
    }

    /**
     * Set the feed description
     *
     * @return string|null
     */
    public function setDescription($description)
    {
        if (empty($description) || !is_string($description)) {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string');
        }
        $this->_data['description'] = $description;
    }

    /**
     * Set the feed ID
     *
     * @return string|null
     */
    public function setId($id)
    {
        if (empty($id) || !is_string($id)) {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string');
        }
        $this->_data['id'] = $id;
    }

    /**
     * Set a link to the HTML source of this entry
     *
     * @return string|null
     */
    public function setLink($link)
    {
        if (empty($link) || !is_string($link) || !Zend_Uri::check($link)) {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string and valid URI/IRI');
        }
        $this->_data['link'] = $link;
    }

    /**
     * Set the number of comments associated with this entry
     *
     * @return string|null
     */
    public function setCommentCount($count)
    {
        if (empty($count) || !is_numeric($count) || (int) $count < 0) {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: "count" must be a non-empty integer number');
        }
        $this->_data['commentCount'] = (int) $count;
    }

    /**
     * Set a link to a HTML page containing comments associated with this entry
     *
     * @return string|null
     */
    public function setCommentLink($link)
    {
        if (empty($link) || !is_string($link) || !Zend_Uri::check($link)) {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: "link" must be a non-empty string and valid URI/IRI');
        }
        $this->_data['commentLink'] = $link;
    }

    /**
     * Set a link to an XML feed for any comments associated with this entry
     *
     * @return string|null
     */
    public function setCommentFeedLink(array $link)
    {
        if (!isset($link['uri']) || !is_string($link['uri']) || !Zend_Uri::check($link['uri'])) {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: "link" must be a non-empty string and valid URI/IRI');
        }
        if (!isset($link['type']) || !in_array($link['type'], array('atom', 'rss', 'rdf'))) {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: "type" must be one'
            . ' of "atom", "rss" or "rdf"');
        }
        if (!isset($this->_data['commentFeedLinks'])) {
            $this->_data['commentFeedLinks'] = array();
        }
        $this->_data['commentFeedLinks'][] = $link;
    }
    
    /**
     * Set a links to an XML feed for any comments associated with this entry.
     * Each link is an array with keys "uri" and "type", where type is one of:
     * "atom", "rss" or "rdf".
     *
     * @return string|null
     */
    public function setCommentFeedLinks(array $links)
    {
        foreach ($links as $link) {
            $this->setCommentFeedLink($link);
        }
    }

    /**
     * Set the feed title
     *
     * @return string|null
     */
    public function setTitle($title)
    {
        if (empty($title) || !is_string($title)) {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string');
        }
        $this->_data['title'] = $title;
    }

    /**
     * Get an array with feed authors
     *
     * @return array
     */
    public function getAuthors()
    {
        if (!array_key_exists('authors', $this->_data)) {
            return null;
        }
        return $this->_data['authors'];
    }

    /**
     * Get the entry content
     *
     * @return string
     */
    public function getContent()
    {
        if (!array_key_exists('content', $this->_data)) {
            return null;
        }
        return $this->_data['content'];
    }

    /**
     * Get the entry copyright information
     *
     * @return string
     */
    public function getCopyright()
    {
        if (!array_key_exists('copyright', $this->_data)) {
            return null;
        }
        return $this->_data['copyright'];
    }

    /**
     * Get the entry creation date
     *
     * @return string
     */
    public function getDateCreated()
    {
        if (!array_key_exists('dateCreated', $this->_data)) {
            return null;
        }
        return $this->_data['dateCreated'];
    }

    /**
     * Get the entry modification date
     *
     * @return string
     */
    public function getDateModified()
    {
        if (!array_key_exists('dateModified', $this->_data)) {
            return null;
        }
        return $this->_data['dateModified'];
    }

    /**
     * Get the entry description
     *
     * @return string
     */
    public function getDescription()
    {
        if (!array_key_exists('description', $this->_data)) {
            return null;
        }
        return $this->_data['description'];
    }

    /**
     * Get the entry ID
     *
     * @return string
     */
    public function getId()
    {
        if (!array_key_exists('id', $this->_data)) {
            return null;
        }
        return $this->_data['id'];
    }
    
    /**
     * Get a link to the HTML source
     *
     * @return string|null
     */
    public function getLink()
    {
        if (!array_key_exists('link', $this->_data)) {
            return null;
        }
        return $this->_data['link'];
    }


    /**
     * Get all links
     *
     * @return array
     */
    public function getLinks()
    {
        if (!array_key_exists('links', $this->_data)) {
            return null;
        }
        return $this->_data['links'];
    }

    /**
     * Get the entry title
     *
     * @return string
     */
    public function getTitle()
    {
        if (!array_key_exists('title', $this->_data)) {
            return null;
        }
        return $this->_data['title'];
    }

    /**
     * Get the number of comments/replies for current entry
     *
     * @return integer
     */
    public function getCommentCount()
    {
        if (!array_key_exists('commentCount', $this->_data)) {
            return null;
        }
        return $this->_data['commentCount'];
    }

    /**
     * Returns a URI pointing to the HTML page where comments can be made on this entry
     *
     * @return string
     */
    public function getCommentLink()
    {
        if (!array_key_exists('commentLink', $this->_data)) {
            return null;
        }
        return $this->_data['commentLink'];
    }

    /**
     * Returns an array of URIs pointing to a feed of all comments for this entry
     * where the array keys indicate the feed type (atom, rss or rdf).
     *
     * @return string
     */
    public function getCommentFeedLinks()
    {
        if (!array_key_exists('commentFeedLinks', $this->_data)) {
            return null;
        }
        return $this->_data['commentFeedLinks'];
    }
    
    /**
     * Add a entry category
     *
     * @param string $category
     */ 
    public function addCategory(array $category)
    {
        if (!isset($category['term'])) {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Each category must be an array and '
            . 'contain at least a "term" element containing the machine '
            . ' readable category name');
        }
        if (isset($category['scheme'])) {
            if (empty($category['scheme']) 
                || !is_string($category['scheme'])
                || !Zend_Uri::check($category['scheme'])
            ) {
                // require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('The Atom scheme or RSS domain of'
                . ' a category must be a valid URI');
            }
        }
        if (!isset($this->_data['categories'])) {
            $this->_data['categories'] = array();
        }
        $this->_data['categories'][] = $category;
    }
    
    /**
     * Set an array of entry categories
     *
     * @param array $categories
     */
    public function addCategories(array $categories)
    {
        foreach ($categories as $category) {
            $this->addCategory($category);
        }
    }
    
    /**
     * Get the entry categories
     *
     * @return string|null
     */
    public function getCategories()
    {
        if (!array_key_exists('categories', $this->_data)) {
            return null;
        }
        return $this->_data['categories'];
    }
    
    /**
     * Adds an enclosure to the entry. The array parameter may contain the
     * keys 'uri', 'type' and 'length'. Only 'uri' is required for Atom, though the
     * others must also be provided or RSS rendering (where they are required)
     * will throw an Exception.
     *
     * @param array $enclosures
     */
    public function setEnclosure(array $enclosure)
    {
        if (!isset($enclosure['uri'])) {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Enclosure "uri" is not set');
        }
        if (!Zend_Uri::check($enclosure['uri'])) {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Enclosure "uri" is not a valid URI/IRI');
        }
        $this->_data['enclosure'] = $enclosure;
    }
    
    /**
     * Retrieve an array of all enclosures to be added to entry.
     *
     * @return array
     */
    public function getEnclosure()
    {
        if (!array_key_exists('enclosure', $this->_data)) {
            return null;
        }
        return $this->_data['enclosure'];
    }
    
    /**
     * Unset a specific data point
     *
     * @param string $name
     */
    public function remove($name)
    {
        if (isset($this->_data[$name])) {
            unset($this->_data[$name]);
        }
    }
    
    /**
     * Get registered extensions
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->_extensions;
    }

    /**
     * Return an Extension object with the matching name (postfixed with _Entry)
     *
     * @param string $name
     * @return object
     */
    public function getExtension($name)
    {
        if (array_key_exists($name . '_Entry', $this->_extensions)) {
            return $this->_extensions[$name . '_Entry'];
        }
        return null;
    }
    
    /**
     * Set the current feed type being exported to "rss" or "atom". This allows
     * other objects to gracefully choose whether to execute or not, depending
     * on their appropriateness for the current type, e.g. renderers.
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->_type = $type;
    }
    
    /**
     * Retrieve the current or last feed type exported.
     *
     * @return string Value will be "rss" or "atom"
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Method overloading: call given method on first extension implementing it
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws Zend_Feed_Exception if no extensions implements the method
     */
    public function __call($method, $args)
    {
        foreach ($this->_extensions as $extension) {
            try {
                return call_user_func_array(array($extension, $method), $args);
            } catch (Zend_Feed_Writer_Exception_InvalidMethodException $e) {
            }
        }
        // require_once 'Zend/Feed/Exception.php';
        throw new Zend_Feed_Exception('Method: ' . $method
            . ' does not exist and could not be located on a registered Extension');
    }
    
    /**
     * Creates a new Zend_Feed_Writer_Source data container for use. This is NOT
     * added to the current feed automatically, but is necessary to create a
     * container with some initial values preset based on the current feed data.
     *
     * @return Zend_Feed_Writer_Source
     */
    public function createSource()
    {
        $source = new Zend_Feed_Writer_Source;
        if ($this->getEncoding()) {
            $source->setEncoding($this->getEncoding());
        }
        $source->setType($this->getType());
        return $source;
    }

    /**
     * Appends a Zend_Feed_Writer_Entry object representing a new entry/item
     * the feed data container's internal group of entries.
     *
     * @param Zend_Feed_Writer_Source $source
     */
    public function setSource(Zend_Feed_Writer_Source $source)
    {
        $this->_data['source'] = $source;
    }
    
    /**
     * @return Zend_Feed_Writer_Source
     */
    public function getSource()
    {
        if (isset($this->_data['source'])) {
            return $this->_data['source'];
        }
        return null;
    }

    /**
     * Load extensions from Zend_Feed_Writer
     *
     * @return void
     */
    protected function _loadExtensions()
    {
        $all = Zend_Feed_Writer::getExtensions();
        $exts = $all['entry'];
        foreach ($exts as $ext) {
            $className = Zend_Feed_Writer::getPluginLoader()->getClassName($ext);
            $this->_extensions[$ext] = new $className();
            $this->_extensions[$ext]->setEncoding($this->getEncoding());
        }
    }
}
