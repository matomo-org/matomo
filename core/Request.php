<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Exception;

class Request
{
    /**
     * @var array
     */
    protected $requestParameters;

    public function __construct(array $requestParameters)
    {
        $this->requestParameters = $requestParameters;
    }

    /**
     * Creates a request object using GET and POST parameters of der current request
     *
     * @return static
     */
    public static function fromRequest(): self
    {
        return new self($_GET + $_POST);
    }

    /**
     * Creates a request object using only GET parameters of der current request
     *
     * @return static
     */
    public static function fromGet(): self
    {
        return new self($_GET);
    }

    /**
     * Creates a request object using only POST parameters of der current request
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
     * @throws Exception
     */
    public function getParameter(string $name, $default = null)
    {
        if (!strlen($name)) {
            throw new Exception('Invalid request parameter. Parameter name required.');
        }

        if (
            array_key_exists($name, $this->requestParameters)
            && $this->requestParameters[$name] !== null
            && $this->requestParameters[$name] !== ''
        ) {
            return $this->requestParameters[$name];
        }

        if (null !== $default) {
            return $default;
        }

        throw new Exception("The parameter '$name' isn't set in the Request and a default value wasn't provided.");
    }

    /**
     * Returns the requested parameter from the request object.
     * If no default is provided and the requested parameter either can't be found or is not of type integer an
     * exception will be thrown
     *
     * @param string $name
     * @param int|null $default
     * @return int|null
     * @throws Exception
     */
    public function getIntegerParameter(string $name, ?int $default = null): ?int
    {
        $parameter = $this->getParameter($name, $default);

        if ((is_string($parameter) || is_numeric($parameter)) && (string)$parameter === (string)(int)$parameter) {
            return (int)$parameter;
        }

        if (null !== $default) {
            return $default;
        }

        throw new Exception("The parameter '$name' doesn't contain an integer and a default value wasn't provided.");
    }

    /**
     * Returns the requested parameter from the request object.
     * If no default is provided and the requested parameter either can't be found or is not of type float an
     * exception will be thrown
     *
     * @param string $name
     * @param float|null $default
     * @return float|null
     * @throws Exception
     */
    public function getFloatParameter(string $name, ?float $default = null): ?float
    {
        $parameter = $this->getParameter($name, $default);

        if (
            (is_string($parameter) || is_numeric($parameter)) &&
            ((string)$parameter === (string)(float)$parameter ||
            (string)$parameter === (string)(int)$parameter)
        ) {
            return (float)$parameter;
        }

        if (null !== $default) {
            return $default;
        }

        throw new Exception("The parameter '$name' doesn't contain a float and a default value wasn't provided.");
    }

    /**
     * Returns the requested parameter from the request object.
     * If no default is provided and the requested parameter either can't be found or is not of type string an
     * exception will be thrown
     *
     * @param string $name
     * @param string|null $default
     * @return string|null
     * @throws Exception
     */
    public function getStringParameter(string $name, ?string $default = null): ?string
    {
        $parameter = $this->getParameter($name, $default);

        if (is_string($parameter) || is_numeric($parameter)) {
            return (string)$parameter;
        }

        if (null !== $default) {
            return $default;
        }

        throw new Exception("The parameter '$name' doesn't contain a string and a default value wasn't provided.");
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
     * @return bool|null
     * @throws Exception
     */
    public function getBoolParameter(string $name, ?bool $default = null): ?bool
    {
        $parameter = $this->getParameter($name, $default);

        if ($parameter === false || $parameter === true) {
            return $parameter;
        }

        if ($parameter === 'false' || $parameter === '0' || $parameter === 0) {
            return false;
        }

        if ($parameter === 'true' || $parameter === '1' || $parameter === 1) {
            return true;
        }

        if (null !== $default) {
            return $default;
        }

        throw new Exception("The parameter '$name' doesn't contain a bool-ish value and a default value wasn't provided.");
    }

    /**
     * Returns the requested parameter from the request object.
     * If no default is provided and the requested parameter either can't be found or is not of type array an
     * exception will be thrown
     *
     * @param string $name
     * @param array|null $default
     * @return array|null
     * @throws Exception
     */
    public function getArrayParameter(string $name, ?array $default = null): ?array
    {
        $parameter = $this->getParameter($name, $default);

        if (is_array($parameter)) {
            return $parameter;
        }

        if (null !== $default) {
            return $default;
        }

        throw new Exception("The parameter '$name' doesn't contain an array and a default value wasn't provided.");
    }

    /**
     * Returns the requested parameter from the request object.
     * If no default is provided and the requested parameter either can't be found or can't be json_decode'd an
     * exception will be thrown
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @throws Exception
     */
    public function getJSONParameter(string $name, $default = null)
    {
        $parameter = $this->getParameter($name, $default);

        if (is_string($parameter)) {
            $decodedValue = \json_decode($parameter, true);

            if ($decodedValue !== null && $decodedValue !== '') {
                return $decodedValue;
            }
        }

        if (null !== $default) {
            return $default;
        }

        throw new Exception("The parameter '$name' doesn't contain a json encoded value and a default value wasn't provided.");
    }
}
