<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\PHPStan\Rules\data;

use Piwik\Validators\NumberRange;

abstract class ApiParameterValidationApi extends \Piwik\Plugin\API
{
    // starts with the self-check -> must NOT be flagged
    public function checked(#[NumberRange(1)] int $limit = 10): int
    {
        $this->checkParameters(__METHOD__, get_defined_vars());

        return $limit;
    }

    // validators but no self-check -> flagged
    public function missingSelfCheck(#[NumberRange(1)] int $limit = 10): int
    {
        return $limit;
    }

    // the self-check is not the first statement -> flagged
    public function selfCheckNotFirst(#[NumberRange(1)] int $limit = 10): int
    {
        $limit++;
        $this->checkParameters(__METHOD__, get_defined_vars());

        return $limit;
    }

    // __FUNCTION__ loses the declaring class -> flagged
    public function selfCheckWithFunctionName(#[NumberRange(1)] int $limit = 10): int
    {
        $this->checkParameters(__FUNCTION__, get_defined_vars());

        return $limit;
    }

    // the self-check passes only some arguments -> flagged
    public function selfCheckWithOtherArguments(#[NumberRange(1)] int $limit = 10): int
    {
        $this->checkParameters(__METHOD__, ['limit' => $limit]);

        return $limit;
    }

    // abstract, no body to check -> must NOT be flagged
    abstract public function abstractMethod(#[NumberRange(1)] int $limit = 10): int;

    // no validator attributes -> must NOT be flagged
    public function noValidators(bool $flag = false): bool
    {
        return $flag;
    }
}
