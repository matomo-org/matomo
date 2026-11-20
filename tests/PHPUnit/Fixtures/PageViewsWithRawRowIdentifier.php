<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

use Piwik\DataTable;
use Piwik\DataTable\Filter\Sort;
use Piwik\DI;
use Piwik\Plugins\Actions\API as ActionsApi;
use Piwik\Request;

/**
 * Adds one site with several pageviews and gives Actions.getPageUrls a report specific row
 * identifier, held as row metadata that is never passed through the label sanitization.
 *
 * No core report uses a row identifier other than 'label', so this is the only way to reach the
 * code that builds a comparison label from a row identifier.
 */
class PageViewsWithRawRowIdentifier extends OneVisitSeveralPageViews
{
    public const ROW_IDENTIFIER_COLUMN = 'rawRowId';

    /**
     * Row identifier of the first row. Deliberately contains markup, so a test can tell whether
     * it reaches the browser as markup or as text.
     */
    public const FIRST_ROW_IDENTIFIER = 'row zero <b>markup</b>';

    public function provideContainerConfig()
    {
        return [
            'observers.global' => DI::add([
                ['API.Request.intercept', DI::value(
                    function (&$returnedValue, $finalParameters, $pluginName, $methodName) {
                        if ($pluginName !== 'Actions' || $methodName !== 'getPageUrls') {
                            return;
                        }

                        $request = Request::fromRequest();

                        // called directly so this observer does not see its own request again
                        $table = ActionsApi::getInstance()->getPageUrls(
                            $request->getIntegerParameter('idSite'),
                            $request->getStringParameter('period'),
                            $request->getStringParameter('date'),
                            $request->getStringParameter('segment', '')
                        );

                        $table->filter(Sort::class, ['nb_hits']);
                        $table->filter(function (DataTable $table) {
                            $table->setMetadata(
                                DataTable::ROW_IDENTIFIER_METADATA_NAME,
                                self::ROW_IDENTIFIER_COLUMN
                            );

                            $position = 0;
                            foreach ($table->getRows() as $row) {
                                $row->setMetadata(
                                    self::ROW_IDENTIFIER_COLUMN,
                                    $position === 0 ? self::FIRST_ROW_IDENTIFIER : 'row ' . $position
                                );
                                $position++;
                            }
                        });

                        $returnedValue = $table;
                    }
                )],
            ]),
        ];
    }
}
