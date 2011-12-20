<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_ImageGraph
 */


/**
 *
 * @package Piwik_ImageGraph
 */
class Piwik_ImageGraph_StaticGraph_Exception extends Piwik_ImageGraph_StaticGraph
{
	private $exception;

	public function setException($exception)
	{
		$this->exception = $exception;
	}

	protected function getDefaultColors()
	{
		return array();
	}

	public function renderGraph()
	{
		$this->pData = new pData();
		$this->initpImage();

		$message = $this->exception->getMessage();
		$messageWidthHeight = $this->getTextWidthHeight($message, false);

		$this->pImage->drawText(
			0,
			$messageWidthHeight[self::HEIGHT_KEY],
			$message
		);
	}
}
