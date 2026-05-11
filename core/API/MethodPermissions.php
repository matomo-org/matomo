<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\API;

use Piwik\Piwik;

class MethodPermissions
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

    /**
     * @param array{name: string, parameter: string|null}|null $permission
     */
    public function enforce(?array $permission, array $parameters): void
    {
        if (empty($permission)) {
            return;
        }

        $name = $permission['name'];
        $parameterName = $permission['parameter'];
        $parameterValue = $parameterName !== null ? ($parameters[$parameterName] ?? null) : null;

        switch ($name) {
            case 'superuser':
                Piwik::checkUserHasSuperUserAccess();
                break;
            case 'notAnonymous':
                Piwik::checkUserIsNotAnonymous();
                break;
            case 'someView':
                Piwik::checkUserHasSomeViewAccess();
                break;
            case 'someWrite':
                Piwik::checkUserHasSomeWriteAccess();
                break;
            case 'someAdmin':
                Piwik::checkUserHasSomeAdminAccess();
                break;
            case 'view':
                Piwik::checkUserHasViewAccess($parameterValue);
                break;
            case 'write':
                Piwik::checkUserHasWriteAccess($parameterValue);
                break;
            case 'admin':
                Piwik::checkUserHasAdminAccess($parameterValue);
                break;
            case 'superUserOrUser':
                Piwik::checkUserHasSuperUserAccessOrIsTheUser($parameterValue);
                break;
        }
    }

    /**
     * @param array{name: string, parameter: string|null}|null $permission
     */
    public function toReadableString(?array $permission): string
    {
        if (empty($permission)) {
            return '';
        }

        if (empty($permission['parameter'])) {
            return $permission['name'];
        }

        return sprintf('%s(%s)', $permission['name'], $permission['parameter']);
    }

    /**
     * @return array{name: string, parameter: string|null}|null
     */
    public function parseFromAttributes(\ReflectionMethod $method): ?array
    {
        $attributes = $method->getAttributes(\Piwik\Attributes\Permission::class);
        if (count($attributes) > 1) {
            throw new \InvalidArgumentException('Only one Permission attribute is supported per method.');
        }

        if (empty($attributes)) {
            return null;
        }

        /** @var \Piwik\Attributes\Permission $instance */
        $instance = $attributes[0]->newInstance();
        return $this->normalizePermission(
            $instance->getRequirement(),
            $instance->getParameter()
        );
    }

    /**
     * @return array{name: string, parameter: string|null}|null
     */
    public function resolveMethodPermissions(\ReflectionMethod $method): ?array
    {
        $permission = $this->parseFromAttributes($method);

        if (!empty($permission) && $permission['parameter'] !== null) {
            $this->assertParameterExistsOnMethod($method, $permission['parameter']);
        }

        return $permission;
    }

    private function assertParameterExistsOnMethod(\ReflectionMethod $method, string $parameterName): void
    {
        foreach ($method->getParameters() as $parameter) {
            if ($parameter->getName() === $parameterName) {
                return;
            }
        }

        throw new \InvalidArgumentException(sprintf(
            'Permission metadata on %s::%s references parameter "$%s" which does not exist on the method signature.',
            $method->getDeclaringClass()->getName(),
            $method->getName(),
            $parameterName
        ));
    }

    /**
     * @return array{name: string, parameter: string|null}
     */
    private function normalizePermission(string $requirement, ?string $parameter): array
    {
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

        return [
            'name' => $requirement,
            'parameter' => $parameter,
        ];
    }
}
