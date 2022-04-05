<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Processor;

use Monolog\Utils;

/**
 * Processes a record's message according to PSR-3 rules
 *
 * It replaces {foo} with the value from $context['foo']
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class PsrLogMessageProcessor implements ProcessorInterface
{
    const SIMPLE_DATE = "Y-m-d\TH:i:s.uP";

    /** @var string|null */
    private $dateFormat;

    /** @var bool */
    private $removeUsedContextFields;

    /**
     * @param string|null $dateFormat              The format of the timestamp: one supported by DateTime::format
     * @param bool        $removeUsedContextFields If set to true the fields interpolated into message gets unset
     */
    public function __construct($dateFormat = null, $removeUsedContextFields = false)
    {
        $this->dateFormat = $dateFormat;
        $this->removeUsedContextFields = $removeUsedContextFields;
    }

    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        if (false === strpos($record['message'], '{')) {
            return $record;
        }

        $replacements = array();
        foreach ($record['context'] as $key => $val) {
            $placeholder = '{' . $key . '}';
            if (strpos($record['message'], $placeholder) === false) {
                continue;
            }

            if (is_null($val) || is_scalar($val) || (is_object($val) && method_exists($val, "__toString"))) {
                $replacements[$placeholder] = $val;
            } elseif ($val instanceof \DateTime) {
                $replacements[$placeholder] = $val->format($this->dateFormat ?: static::SIMPLE_DATE);
            } elseif (is_object($val)) {
                $replacements[$placeholder] = '[object '.Utils::getClass($val).']';
            } elseif (is_array($val)) {
                $replacements[$placeholder] = 'array'.Utils::jsonEncode($val, null, true);
            } else {
                $replacements[$placeholder] = '['.gettype($val).']';
            }

            if ($this->removeUsedContextFields) {
                unset($record['context'][$key]);
            }
        }

        print_r($record['message']);@ob_flush();
        $record['message'] = strtr($record['message'], $replacements);

        return $record;
    }
}
