<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Common;

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
    const STATUS_INFORMATIONAL = 'informational';

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
     * @param string $label
     * @param string $comment
     * @param bool $escapeComment
     * @return DiagnosticResult
     */
    public static function informationalResult($label, $comment = '', $escapeComment = true)
    {
        if ($comment === true) {
            $comment = '1';
        } elseif ($comment === false) {
            $comment = '0';
        }

        if ($escapeComment) {
            $comment = Common::fixLbrace(Common::sanitizeInputValue($comment));
        }

        return self::singleResult($label, self::STATUS_INFORMATIONAL, $comment);
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
