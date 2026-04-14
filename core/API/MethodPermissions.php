<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\API;

use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
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
    public function parseFromDocComment($docComment): ?array
    {
        if (empty($docComment)) {
            return null;
        }

        preg_match_all('/@matomo-permission\s+([^\r\n*]+)/', $docComment, $matches);
        if (count($matches[1]) > 1) {
            throw new \InvalidArgumentException('Only one @matomo-permission tag is supported per method.');
        }

        if (empty($matches[1])) {
            return null;
        }

        return $this->normalizePermission(trim($matches[1][0]));
    }

    /**
     * @return array{name: string, parameter: string|null}|null
     */
    public function parseFromAttributes(\ReflectionMethod $method): ?array
    {
        if (!method_exists($method, 'getAttributes')) {
            return null;
        }

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

    public function resolveMethodPermissions(\ReflectionMethod $method): array
    {
        $docblockPermission = $this->parseFromDocComment($method->getDocComment());
        $attributePermission = $this->parseFromAttributes($method);

        if (!empty($docblockPermission) && !empty($attributePermission)) {
            if ($docblockPermission !== $attributePermission) {
                $this->logMismatchedPermissionMetadata($method, $docblockPermission, $attributePermission);
                $permission = null;
            } else {
                $permission = $docblockPermission;
            }
        } elseif (!empty($docblockPermission)) {
            $permission = $docblockPermission;
        } elseif (!empty($attributePermission)) {
            $permission = $attributePermission;
        } else {
            $permission = null;
        }

        return [
            'permission' => $permission,
            'hasPermissionMetadata' => !empty($permission),
        ];
    }

    /**
     * @return array{name: string, parameter: string|null}
     */
    private function normalizePermission(string $requirement, ?string $parameter = null): array
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

        return [
            'name' => $requirement,
            'parameter' => $parameter,
        ];
    }

    private function logMismatchedPermissionMetadata(\ReflectionMethod $method, array $docblockPermission, array $attributePermission): void
    {
        StaticContainer::get(LoggerInterface::class)->info(
            'Ignoring mismatched API permission metadata on {class}::{method}. Docblock={docblockPermission} Attribute={attributePermission}',
            [
                'class' => $method->getDeclaringClass()->getName(),
                'method' => $method->getName(),
                'docblockPermission' => $this->toReadableString($docblockPermission),
                'attributePermission' => $this->toReadableString($attributePermission),
            ]
        );
    }
}
