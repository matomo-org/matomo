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
 * @version    $Id: Atom.php 22098 2010-05-04 18:03:29Z padraic $
 */

/** @see Zend_Feed_Writer_Feed */
// require_once 'Zend/Feed/Writer/Feed.php';

/** @see Zend_Version */
// require_once 'Zend/Version.php';

/** @see Zend_Feed_Writer_Renderer_RendererInterface */
// require_once 'Zend/Feed/Writer/Renderer/RendererInterface.php';

/** @see Zend_Feed_Writer_Renderer_Entry_Atom */
// require_once 'Zend/Feed/Writer/Renderer/Entry/Atom.php';

/** @see Zend_Feed_Writer_Renderer_Entry_Atom_Deleted */
// require_once 'Zend/Feed/Writer/Renderer/Entry/Atom/Deleted.php';

/** @see Zend_Feed_Writer_Renderer_RendererAbstract */
// require_once 'Zend/Feed/Writer/Renderer/RendererAbstract.php';

// require_once 'Zend/Feed/Writer/Renderer/Feed/Atom/AtomAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Writer_Renderer_Feed_Atom
    extends Zend_Feed_Writer_Renderer_Feed_Atom_AtomAbstract
    implements Zend_Feed_Writer_Renderer_RendererInterface
{
    /**
     * Constructor
     * 
     * @param  Zend_Feed_Writer_Feed $container 
     * @return void
     */
    public function __construct (Zend_Feed_Writer_Feed $container)
    {
        parent::__construct($container);
    }

    /**
     * Render Atom feed
     * 
     * @return Zend_Feed_Writer_Renderer_Feed_Atom
     */
    public function render()
    {
        if (!$this->_container->getEncoding()) {
            $this->_container->setEncoding('UTF-8');
        }
        $this->_dom = new DOMDocument('1.0', $this->_container->getEncoding());
        $this->_dom->formatOutput = true;
        $root = $this->_dom->createElementNS(
            Zend_Feed_Writer::NAMESPACE_ATOM_10, 'feed'
        );
        $this->setRootElement($root);
        $this->_dom->appendChild($root);
        $this->_setLanguage($this->_dom, $root);
        $this->_setBaseUrl($this->_dom, $root);
        $this->_setTitle($this->_dom, $root);
        $this->_setDescription($this->_dom, $root);
        $this->_setImage($this->_dom, $root);
        $this->_setDateCreated($this->_dom, $root);
        $this->_setDateModified($this->_dom, $root);
        $this->_setGenerator($this->_dom, $root);
        $this->_setLink($this->_dom, $root);
        $this->_setFeedLinks($this->_dom, $root);
        $this->_setId($this->_dom, $root);
        $this->_setAuthors($this->_dom, $root);
        $this->_setCopyright($this->_dom, $root);
        $this->_setCategories($this->_dom, $root);
        $this->_setHubs($this->_dom, $root);
        
        foreach ($this->_extensions as $ext) {
            $ext->setType($this->getType());
            $ext->setRootElement($this->getRootElement());
            $ext->setDomDocument($this->getDomDocument(), $root);
            $ext->render();
        }
        
        foreach ($this->_container as $entry) {
            if ($this->getDataContainer()->getEncoding()) {
                $entry->setEncoding($this->getDataContainer()->getEncoding());
            }
            if ($entry instanceof Zend_Feed_Writer_Entry) {
                $renderer = new Zend_Feed_Writer_Renderer_Entry_Atom($entry);
            } else {
                if (!$this->_dom->documentElement->hasAttribute('xmlns:at')) {
                    $this->_dom->documentElement->setAttribute(
                        'xmlns:at', 'http://purl.org/atompub/tombstones/1.0'
                    );
                }
                $renderer = new Zend_Feed_Writer_Renderer_Entry_Atom_Deleted($entry);
            }
            if ($this->_ignoreExceptions === true) {
                $renderer->ignoreExceptions();
            }
            $renderer->setType($this->getType());
            $renderer->setRootElement($this->_dom->documentElement);
            $renderer->render();
            $element = $renderer->getElement();
            $imported = $this->_dom->importNode($element, true);
            $root->appendChild($imported);
        }
        return $this;
    }

}
