<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Measurable;

class Type
{
    const ID = '';
    protected $name = 'General_Measurable';
    protected $namePlural = 'General_Measurables';
    protected $description = 'Default measurable type';
    protected $howToSetupUrl = '';

    public function isType($typeId)
    {
        // here we should add some point also check whether id matches any extended ID. Eg if
        // MetaSites extends Websites, then we expected $metaSite->isType('website') to be true (maybe)
        return $this->getId() === $typeId;
    }

    public function getId()
    {
        $id = static::ID;

        if (empty($id)) {
            $message = 'Type %s does not define an ID. Set the ID constant to fix this issue';;
            throw new \Exception(sprintf($message, get_called_class()));
        }

        return $id;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getNamePlural()
    {
        return $this->namePlural;
    }

    public function getHowToSetupUrl()
    {
        return $this->howToSetupUrl;
    }

    public function configureMeasurableSettings(MeasurableSettings $settings)
    {
    }
}

