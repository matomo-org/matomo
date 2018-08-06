<?php

namespace Piwik\Plugins\Diagnostics\Diagnostic;

class RequiredPhpSetting
{
    
    /** @var string */
    private $setting;
    
    /** @var array */
    private $requiredValues;
    
    /** @var string */
    private $errorResult = DiagnosticResult::STATUS_ERROR;
    
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
            'isValid' => null,
        );
        
        return $this;
    }
    
    /**
     * @param $errorResult
     *
     * @return $this
     */
    public function setErrorResult($errorResult)
    {
        if ($errorResult !== DiagnosticResult::STATUS_WARNING && $errorResult !== DiagnosticResult::STATUS_ERROR) {
            throw new \InvalidArgumentException('Error result must be either DiagnosticResult::STATUS_WARNING or DiagnosticResult::STATUS_ERROR.');
        }
        
        $this->errorResult = $errorResult;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getErrorResult()
    {
        return $this->errorResult;
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
            $this->requiredValues[$key]['isValid'] = version_compare($currentValue, $requiredValue['requiredValue'], $requiredValue['operator']);
            
            if($this->requiredValues[$key]['isValid']){
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
        
        return $this->setting . ' ' . implode(' OR ', $checks);
    }
    
}
