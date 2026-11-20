<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\API;

use Piwik\Http\BadRequestException;
use Piwik\Piwik;

/**
 * Thrown when one or more API method parameters do not pass their validator attributes, see
 * {@see ParameterValidator}.
 *
 * The message lists every violation, separated by `; ` (the JSON renderer flattens line breaks).
 */
class ParameterValidationException extends BadRequestException
{
    /**
     * @var list<array{parameter: string, message: string}>
     */
    private $violations;

    /**
     * @param list<array{parameter: string, message: string}> $violations
     */
    public function __construct(array $violations)
    {
        $this->violations = $violations;

        $messages = array_map(function (array $violation): string {
            return Piwik::translate('General_InvalidValueForParameter', [$violation['parameter']]) . ' ' . $violation['message'];
        }, $violations);

        parent::__construct(implode('; ', $messages));
    }

    /**
     * @return list<array{parameter: string, message: string}>
     */
    public function getViolations(): array
    {
        return $this->violations;
    }
}
