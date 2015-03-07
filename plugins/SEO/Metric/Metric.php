<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SEO\Metric;

/**
 * Describes a SEO metric.
 */
class Metric
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $logo;

    /**
     * @var string|null
     */
    private $logoLink;

    /**
     * @var string|null
     */
    private $logoTooltip;

    /**
     * @var string|null
     */
    private $valueSuffix;

    /**
     * @param string $id
     * @param string $name Can be a string or a translation ID.
     * @param string $value Rank value.
     * @param string $logo URL to a logo.
     * @param string|null $logoLink
     * @param string|null $logoTooltip
     * @param string|null $valueSuffix
     */
    public function __construct($id, $name, $value, $logo, $logoLink = null, $logoTooltip = null, $valueSuffix = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->value = $value;
        $this->logo = $logo;
        $this->logoLink = $logoLink;
        $this->logoTooltip = $logoTooltip;
        $this->valueSuffix = $valueSuffix;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @return string|null
     */
    public function getLogoLink()
    {
        return $this->logoLink;
    }

    /**
     * @return string|null
     */
    public function getLogoTooltip()
    {
        return $this->logoTooltip;
    }

    /**
     * @return null|string
     */
    public function getValueSuffix()
    {
        return $this->valueSuffix;
    }

    /**
     * Allows the class to be serialized with var_export (in the cache).
     *
     * @param array $array
     * @return Metric
     */
    public static function __set_state($array)
    {
        return new self(
            $array['id'],
            $array['name'],
            $array['value'],
            $array['logo'],
            $array['logoLink'],
            $array['logoTooltip'],
            $array['valueSuffix']
        );
    }
}
