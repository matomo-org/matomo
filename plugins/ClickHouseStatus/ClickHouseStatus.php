<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ClickHouseStatus;

/**
 * ClickHouse POC plumbing (DEV-20678): exposes a superuser-only status page proving the
 * app can reach the ClickHouse service and run DDL/DML/SELECT through the configured
 * client. Screenshot-asserted by tests/UI/ClickHouseStatus_spec.js so the standard UI
 * test pipeline exercises the ClickHouse connection.
 */
class ClickHouseStatus extends \Piwik\Plugin
{
}
