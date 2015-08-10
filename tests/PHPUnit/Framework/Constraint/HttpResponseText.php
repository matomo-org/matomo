<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Framework\Constraint;

class HttpResponseText extends \PHPUnit_Framework_Constraint
{
    private $actualCode;

    /**
     * @param string $value Expected response text.
     */
    public function __construct($value)
    {
        parent::__construct();
        $this->value = $value;
    }

    public function getResponse($url)
    {
        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => false,
            CURLOPT_TIMEOUT        => 1,
            CURLOPT_RETURNTRANSFER => true
        );

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = @curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns TRUE if the
     * constraint is met, FALSE otherwise.
     *
     * @param mixed $other Value or object to evaluate.
     * @return bool
     */
    public function matches($other)
    {
        $this->actualCode = $this->getResponse($other);

        return $this->value === $this->actualCode;
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        return 'does not return response text ' . $this->exporter->export($this->value) . ' it is ' . $this->actualCode;
    }
}?>