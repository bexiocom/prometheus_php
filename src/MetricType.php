<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP.
 */

namespace Bexio\PrometheusPHP;

/**
 * Common denominator for Metrics.
 *
 * @see https://prometheus.io/docs/concepts/metric_types/
 */
interface MetricType
{
    /**
     * @return Options
     */
    public function getOptions();

    /**
     * @return Action[]
     */
    public function getActions();

    /**
     * Clears the internal stack of change actions.
     */
    public function clear();
}
