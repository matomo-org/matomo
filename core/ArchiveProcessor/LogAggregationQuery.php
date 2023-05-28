<?php
/**
 * Copyright (C) InnoCraft Ltd - All rights reserved.
 *
 * NOTICE:  All information contained herein is, and remains the property of InnoCraft Ltd.
 * The intellectual and technical concepts contained herein are protected by trade secret or copyright law.
 * Redistribution of this information or reproduction of this material is strictly forbidden
 * unless prior written permission is obtained from InnoCraft Ltd.
 *
 * You shall use this code only in accordance with the license agreement obtained from InnoCraft Ltd.
 *
 * @link https://www.innocraft.com/
 * @license For license details see https://www.innocraft.com/license
 */

namespace Piwik\ArchiveProcessor;

/**
 * TODO
 */
class LogAggregationQuery
{
    /**
     * @var string
     */
    private $table;

    /**
     * ['dimension name' => 'dimension sql']
     *
     * @var array
     */
    private $dimensions;

    /**
     * @var ['metric name' => 'metric sql']
     */
    private $metrics;

    public function __construct(string $table)
    {
        $this->table = $table;
        // TODO
    }
    // TODO
}
