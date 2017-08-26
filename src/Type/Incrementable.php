<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Type\Incrementable.
 */

namespace Bexio\PrometheusPHP\Type;

use Bexio\PrometheusPHP\MetricType;

/**
 * Metric qualifier for metrics which can be incremented.
 */
interface Incrementable extends MetricType
{
    /**
     * Increase the metric value by one.
     */
    public function inc();
}
