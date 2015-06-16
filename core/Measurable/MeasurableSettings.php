<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Measurable;

use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugin\Settings;
use Piwik\Measurable\Settings\Storage;
use Piwik\Settings\Setting;
use Piwik\Measurable\Type\TypeManager;

class MeasurableSettings extends Settings
{

    /**
     * @var int
     */
    private $idSite = null;

    /**
     * @var string
     */
    private $idType = null;

    /**
     * @param int $idSite The id of a site. If you want to get settings for a not yet created site just pass an empty value ("0")
     * @param string $idType If no typeId is given, the type of the site will be used.
     *
     * @throws \Exception
     */
    public function __construct($idSite, $idType)
    {
        $this->idSite = $idSite;
        $this->idType = $idType;
        $this->storage = new Storage(Db::get(), $this->idSite);
        $this->pluginName = 'MeasurableSettings';

        $this->init();
    }

    protected function init()
    {
        $typeManager = new TypeManager();
        $type = $typeManager->getType($this->idType);
        $type->configureMeasurableSettings($this);

        /**
         * This event is posted when generating settings for a Measurable (website). You can add any Measurable settings
         * that you wish to be shown in the Measurable manager (websites manager). If you need to add settings only for
         * eg MobileApp measurables you can use eg `$type->getId() === Piwik\Plugins\MobileAppMeasurable\Type::ID` and
         * add only settings if the condition is true.
         *
         * @since Piwik 2.14.0
         * @deprecated will be removed in Piwik 3.0.0
         *
         * @param MeasurableSettings $this
         * @param \Piwik\Measurable\Type $type
         * @param int $idSite
         */
        Piwik::postEvent('Measurable.initMeasurableSettings', array($this, $type, $this->idSite));
    }

    public function addSetting(Setting $setting)
    {
        if ($this->idSite && $setting instanceof MeasurableSetting) {
            $setting->writableByCurrentUser = Piwik::isUserHasAdminAccess($this->idSite);
        }

        parent::addSetting($setting);
    }

    public function save()
    {
        Piwik::checkUserHasAdminAccess($this->idSite);

        $typeManager = new TypeManager();
        $type = $typeManager->getType($this->idType);

        /**
         * Triggered just before Measurable settings are about to be saved. You can use this event for example
         * to validate not only one setting but multiple ssetting. For example whether username
         * and password matches.
         *
         * @since Piwik 2.14.0
         * @deprecated will be removed in Piwik 3.0.0
         *
         * @param MeasurableSettings $this
         * @param \Piwik\Measurable\Type $type
         * @param int $idSite
         */
        Piwik::postEvent('Measurable.beforeSaveSettings', array($this, $type, $this->idSite));

        $this->storage->save();
    }

}

