<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\AssetManager;

use lessc;
use PHPUnit\Framework\TestCase;

/**
 * Ensures the Less compiler used by Matomo supports CSS container queries
 * (the `@container` at-rule), which are enabled through a patch applied to
 * wikimedia/less.php (see patches/less-container-query.patch).
 */
class LessContainerQueryTest extends TestCase
{
    /**
     * @group Core
     */
    public function testContainerQueryIsCompiled()
    {
        $less = <<<'LESS'
@minWidth: 400px;
.wrapper {
  container-type: inline-size;
}
@container (min-width: @minWidth) {
  .card {
    color: red;
    .inner { font-weight: bold; }
  }
}
@container sidebar (min-width: 700px) {
  .card { color: blue; }
}
LESS;

        $compiler = new lessc();
        $compiler->setFormatter('classic');
        $compiled = $compiler->compile($less);

        // The @container at-rule is preserved (not rewritten to @media).
        self::assertStringContainsString('@container (min-width: 400px)', $compiled);
        self::assertStringContainsString('@container sidebar (min-width: 700px)', $compiled);

        // Less features still work inside the query: the variable is resolved
        // and nested selectors are flattened.
        self::assertStringContainsString('.card .inner', $compiled);

        // Regular declarations outside the query are unaffected.
        self::assertStringContainsString('container-type: inline-size', $compiled);
    }
}
