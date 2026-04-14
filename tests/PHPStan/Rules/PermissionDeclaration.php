<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\PHPStan\Rules;

class PermissionDeclaration
{
    private const REQUIREMENTS_WITHOUT_PARAMETER = [
        'superuser' => true,
        'notAnonymous' => true,
        'someView' => true,
        'someWrite' => true,
        'someAdmin' => true,
    ];

    private const REQUIREMENTS_WITH_PARAMETER = [
        'view' => true,
        'write' => true,
        'admin' => true,
        'superUserOrUser' => true,
    ];

    /** @var string */
    private $name;

    /** @var string|null */
    private $parameter;

    private function __construct(string $name, ?string $parameter)
    {
        $this->name = $name;
        $this->parameter = $parameter;
    }

    public static function fromDocComment(?string $docComment): ?self
    {
        if (empty($docComment)) {
            return null;
        }

        preg_match_all('/@matomo-permission\s+([^\r\n*]+)/', $docComment, $matches);
        if (count($matches[1]) > 1) {
            throw new \InvalidArgumentException('Only one @matomo-permission tag is supported per method.');
        }

        if (empty($matches[1][0])) {
            return null;
        }

        return self::normalize(trim($matches[1][0]));
    }

    public static function fromAttribute(string $requirement, ?string $parameter): self
    {
        return self::normalize($requirement, $parameter);
    }

    public static function fromBodyCheck(string $requirement, ?string $parameter): self
    {
        return self::normalize($requirement, $parameter);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function equals(self $other): bool
    {
        return $this->name === $other->name
            && $this->parameter === $other->parameter;
    }

    public function toReadableString(): string
    {
        if ($this->parameter === null) {
            return $this->name;
        }

        return sprintf('%s(%s)', $this->name, $this->parameter);
    }

    private static function normalize(string $requirement, ?string $parameter = null): self
    {
        if (
            $parameter === null
            && preg_match('/^([A-Za-z][A-Za-z0-9]*)(?:\((\$?[A-Za-z_][A-Za-z0-9_]*)\))?$/', $requirement, $matches)
        ) {
            $requirement = $matches[1];
            $parameter = $matches[2] ?? null;
        }

        if ($parameter !== null && strpos($parameter, '$') === 0) {
            $parameter = substr($parameter, 1);
        }

        if (isset(self::REQUIREMENTS_WITHOUT_PARAMETER[$requirement])) {
            $parameter = null;
        } elseif (isset(self::REQUIREMENTS_WITH_PARAMETER[$requirement])) {
            if (empty($parameter)) {
                throw new \InvalidArgumentException(sprintf('Permission "%s" requires a parameter name.', $requirement));
            }
        } else {
            throw new \InvalidArgumentException(sprintf('Unsupported Matomo permission declaration "%s".', $requirement));
        }

        return new self($requirement, $parameter);
    }
}
