<?php

namespace Piwik\Plugins\Diagnostics\Diagnostic;

class PhpSettingsCheckService
{
    
    /** @var string */
    private $setting;
    
    /** @var array */
    private $requiredValues;
    
    /**
     * @param string $setting
     * @param int $requiredValue
     * @param string $operator
     */
    public function __construct($setting, $requiredValue, $operator = '=')
    {
        $this->setting = $setting;
        $this->addRequiredValue($requiredValue, $operator);
    }
    
    /**
     * @param int $requiredValue
     * @param string $operator
     *
     * @return $this
     */
    public function addRequiredValue($requiredValue, $operator)
    {
        if(!is_int($requiredValue)){
            throw new \InvalidArgumentException('Required value must be an integer.');
        }
        
        $this->requiredValues[] = array(
            'requiredValue' => $requiredValue,
            'operator' => $operator,
            'isOk' => null,
        );
        
        return $this;
    }
    
    /**
     * Checks required values against php.ini value.
     *
     * @return bool
     */
    public function check()
    {
        $currentValue = (int) ini_get($this->setting);
        
        $return = false;
        foreach($this->requiredValues as $key => $requiredValue){
            $this->requiredValues[$key]['isOk'] = version_compare($currentValue, $requiredValue['requiredValue'], $requiredValue['operator']);
            
            if($this->requiredValues[$key]['isOk']){
                $return = true;
            }
        }
        
        return $return;
    }
    
    public function __toString()
    {
        $checks = array();
        foreach($this->requiredValues as $requiredValue){
            $checks[] = $requiredValue['operator'] . ' ' . $requiredValue['requiredValue'];
        }
        
        return $this->setting . ' ' . implode(' || ', $checks);
    }
    
}