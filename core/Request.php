<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use InvalidArgumentException;

/**
 * Provides (type safe) access methods for request parameters.
 *
 * Ensure to handle parameters received with this class with care.
 * Especially parameters received as string, array or json might contain malicious content. Those should never be used
 * raw in templates or other output.
 *
 * Note: For security reasons this class will automatically remove null byte sequences from string values.
 *
 * @api
 */
class Request
{
    /**
     * @var array
     */
    protected $requestParameters;

    private static $exceptionMsg = "The parameter '%s' isn't set in the Request and a default value wasn't provided.";

    public function __construct(array $requestParameters)
    {
        $this->requestParameters = $requestParameters;
    }

    /**
     * Creates a request object using GET and POST parameters of the current request
     *
     * @return static
     */
    public static function fromRequest(): self
    {
        return new self($_GET + $_POST);
    }

    /**
     * Creates a request object using only GET parameters of the current request
     *
     * @return static
     */
    public static function fromGet(): self
    {
        return new self($_GET);
    }

    /**
     * Creates a request object using only POST parameters of the current request
     *
     * @return static
     */
    public static function fromPost(): self
    {
        return new self($_POST);
    }

    /**
     * Creates a request object using the parameters that can be extracted from the provided query string
     *
     * @return static
     */
    public static function fromQueryString(string $queryString): self
    {
        $requestParameters = [];
        parse_str($queryString, $requestParameters);

        // If a querystring is provided urlencode'd parse_str will not be able to parse it correctly.
        // A querystring like `method%3dVisitsSummary.get%26idSite%3d1` would result in
        // an array like `['method=VisitsSummary.get&idSite=1' => '']`
        // In this case we try to parse the urldecode'd string to get proper results
        // Note: We can't always perform a urldecode, as this might otherwise destroy urlencoded values containing a &
        if (1 === count($requestParameters) && '' === end($requestParameters)) {
            $requestParameters = [];
            parse_str(urldecode($queryString), $requestParameters);
        }

        return new self($requestParameters);
    }

    /**
     * Returns the requested parameter from the request object.
     * If the requested parameter can't be found and no default is provided an exception will be thrown
     *
     * Note: It's recommend to use one of type-safe methods instead, if a certain type is expected:
     * @see getIntegerParameter
     * @see getFloatParameter
     * @see getStringParameter
     * @see getArrayParameter
     * @see getJSONParameter
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getParameter(string $name, $default = null)
    {
        if (!strlen($name)) {
            throw new InvalidArgumentException('Invalid request parameter. Parameter name required.');
        }

        if (
            array_key_exists($name, $this->requestParameters)
            && $this->requestParameters[$name] !== null
        ) {
            return $this->filterNullBytes($this->requestParameters[$name]);
        }

        if (null !== $default) {
            return $default;
        }

        throw new InvalidArgumentException(sprintf(self::$exceptionMsg, $name));
    }

    /**
     * Returns the requested parameter from the request object.
     * If no default is provided and the requested parameter either can't be found or is not of type integer an
     * exception will be thrown
     *
     * @param string $name
     * @param int|null $default
     * @return int
     * @throws InvalidArgumentException
     */
    public function getIntegerParameter(string $name, ?int $default = null): int
    {
        $parameter = $this->getParameter($name, $default);

        if ((is_string($parameter) || is_numeric($parameter)) && (string)$parameter === (string)(int)$parameter) {
            return (int)$parameter;
        }

        if (null !== $default) {
            return $default;
        }

        throw new InvalidArgumentException(sprintf(self::$exceptionMsg, $name));
    }

    /**
     * Returns the requested parameter from the request object.
     * If no default is provided and the requested parameter either can't be found or is not of type float an
     * exception will be thrown
     *
     * @param string $name
     * @param float|null $default
     * @return float
     * @throws InvalidArgumentException
     */
    public function getFloatParameter(string $name, ?float $default = null): float
    {
        $parameter = $this->getParameter($name, $default);

        if (is_float($parameter) || is_int($parameter)) {
            return (float)$parameter;
        }

        // Regex for all supported float notations in PHP (see https://www.php.net/manual/en/language.types.float.php)
        $floatRegex = "/^[-+]?((([0-9]+(_[0-9]+)*)|(([0-9]+(_[0-9]+)*)?\.([0-9]+(_[0-9]+)*))|(([0-9]+(_[0-9]+)*)\.([0-9]+(_[0-9]+)*)?))([eE][+-]?([0-9]+(_[0-9]+)*))?)$/";

        if (is_string($parameter) && preg_match($floatRegex, $parameter)) {
            // underscores would break numbers if not removed before
            return (float) str_replace('_', '', $parameter);
        }

        if (null !== $default) {
            return $default;
        }

        throw new InvalidArgumentException(sprintf(self::$exceptionMsg, $name));
    }

    /**
     * Returns the requested parameter from the request object.
     * If no default is provided and the requested parameter either can't be found or is not of type string an
     * exception will be thrown
     *
     * @param string $name
     * @param string|null $default
     * @return string
     * @throws InvalidArgumentException
     */
    public function getStringParameter(string $name, ?string $default = null): string
    {
        $parameter = $this->getParameter($name, $default);

        if (is_string($parameter) || is_numeric($parameter)) {
            return $this->filterNullBytes((string)$parameter);
        }

        if (null !== $default) {
            return $default;
        }

        throw new InvalidArgumentException(sprintf(self::$exceptionMsg, $name));
    }

    /**
     * Returns the requested parameter from the request object.
     * If no default is provided and the requested parameter either can't be found or can't be converted to boolean
     * exception will be thrown
     *
     * Values accepted as bool-ish:
     * true: true, 'true', '1', 1
     * false: false, 'false', '0', 0
     *
     * @param string $name
     * @param bool|null $default
     * @return bool
     * @throws InvalidArgumentException
     */
    public function getBoolParameter(string $name, ?bool $default = null): bool
    {
        $parameter = $this->getParameter($name, $default);

        if ($parameter === false || $parameter === true) {
            return $parameter;
        }

        if ((\is_string($parameter) && \strtolower($parameter) === 'false') || $parameter === '0' || $parameter === 0) {
            return false;
        }

        if ((\is_string($parameter) && \strtolower($parameter) === 'true') || $parameter === '1' || $parameter === 1) {
            return true;
        }

        if (null !== $default) {
            return $default;
        }

        throw new InvalidArgumentException(sprintf(self::$exceptionMsg, $name));
    }

    /**
     * Returns the requested parameter from the request object.
     * If no default is provided and the requested parameter either can't be found or is not of type array an
     * exception will be thrown
     *
     * @param string $name
     * @param array|null $default
     * @return array
     * @throws InvalidArgumentException
     */
    public function getArrayParameter(string $name, ?array $default = null): array
    {
        $parameter = $this->getParameter($name, $default);

        if (is_array($parameter)) {
            return $this->filterNullBytes($parameter);
        }

        if (null !== $default) {
            return $default;
        }

        throw new InvalidArgumentException(sprintf(self::$exceptionMsg, $name));
    }

    /**
     * Returns the requested parameter from the request object.
     * If no default is provided and the requested parameter either can't be found or can't be json_decode'd an
     * exception will be thrown
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getJsonParameter(string $name, $default = null)
    {
        try {
            // Note we can't simply pass the default to getParameter here, in case the default would be string
            // we would otherwise try to parse it as json below, which might result in unexpected behavior
            $parameter = $this->getParameter($name);
        } catch (InvalidArgumentException $e) {
            $parameter = null;

            if ($default !== null) {
                return $default;
            }
        }

        if (is_string($parameter)) {
            $decodedValue = \json_decode($parameter, true);

            if ($decodedValue !== null && $decodedValue !== '') {
                return $this->filterNullBytes($decodedValue);
            }
        }

        if (null !== $default) {
            return $default;
        }

        throw new InvalidArgumentException(sprintf(self::$exceptionMsg, $name));
    }

    private function filterNullBytes($value)
    {
        if (is_array($value)) {
            $result = [];
            foreach ($value as $key => $arrayValue) {
                $result[$key] = $this->filterNullBytes($arrayValue);
            }
            return $result;
        } else {
            return is_string($value) ? Common::sanitizeNullBytes($value) : $value;
        }
    }

    /**
     * Returns an array containing all parameters of the request object
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->requestParameters;
    }
}
