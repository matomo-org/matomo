<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live\ProfileSummary;

/**
 * Class ProfileSummaryAbstract
 *
 * This class can be implemented in a plugin to provide a new profile summary
 *
 * @api
 */
abstract class ProfileSummaryAbstract
{
    /**
     * Visitor profile information (will be automatically set)
     *
     * @var array
     */
    protected $profile = [];

    /**
     * Set profile information
     *
     * @param array $profile
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    /**
     * Returns the unique ID
     *
     * @return string
     */
    public function getId()
    {
        return static::class;
    }

    /**
     * Returns the descriptive name
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Renders and returns the summary
     *
     * **Example**
     *
     *     public function render() {
     *         if (empty($this->profile['crmData'])) {
     *             return '';
     *         }
     *
     *         $view = new View('@pluginName/summary.twig');
     *         $view->crmData = $this->profile['crmData];
     *         return $view->render();
     *     }
     *
     * @return string
     */
    abstract public function render();

    /**
     * Returns order indicator used to sort all summaries before displaying them
     *
     * @return int
     */
    abstract public function getOrder();
}