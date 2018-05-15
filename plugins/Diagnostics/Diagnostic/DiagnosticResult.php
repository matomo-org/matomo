<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Diagnostic;

/**
 * The result of a diagnostic.
 *
 * @api
 */
class DiagnosticResult
{
    const STATUS_ERROR = 'error';
    const STATUS_WARNING = 'warning';
    const STATUS_OK = 'ok';

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $longErrorMessage = '';

    /**
     * @var DiagnosticResultItem[]
     */
    private $items = array();

    public function __construct($label)
    {
        $this->label = $label;
    }

    /**
     * @param string $label
     * @param string $status
     * @param string $comment
     * @return DiagnosticResult
     */
    public static function singleResult($label, $status, $comment = '')
    {
        $result = new self($label);
        $result->addItem(new DiagnosticResultItem($status, $comment));
        return $result;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return DiagnosticResultItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    public function addItem(DiagnosticResultItem $item)
    {
        $this->items[] = $item;
    }

    /**
     * @param DiagnosticResultItem[] $items
     */
    public function setItems(array $items)
    {
        $this->items = $items;
    }

    /**
     * @return string
     */
    public function getLongErrorMessage()
    {
        return $this->longErrorMessage;
    }

    /**
     * @param string $longErrorMessage
     */
    public function setLongErrorMessage($longErrorMessage)
    {
        $this->longErrorMessage = $longErrorMessage;
    }

    /**
     * Returns the worst status of the items.
     *
     * @return string One of the `STATUS_*` constants.
     */
    public function getStatus()
    {
        $status = self::STATUS_OK;

        foreach ($this->getItems() as $item) {
            if ($item->getStatus() === self::STATUS_ERROR) {
                return self::STATUS_ERROR;
            }

            if ($item->getStatus() === self::STATUS_WARNING) {
                $status = self::STATUS_WARNING;
            }
        }

        return $status;
    }
}
