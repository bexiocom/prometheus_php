<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Type\Addable.
 */

namespace Bexio\PrometheusPHP\Type;

use Bexio\PrometheusPHP\MetricType;

/**
 * Metric qualifier for metrics which can grow in variable steps.
 */
interface Addable extends MetricType
{
    /**
     * Increase the metric value by the given amount.
     *
     * @param float $value
     */
    public function add($value);
}
