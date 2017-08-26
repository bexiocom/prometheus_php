<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Type\Subtractable.
 */

namespace Bexio\PrometheusPHP\Type;

use Bexio\PrometheusPHP\MetricType;

/**
 * Metric qualifier for metrics which can be decremented in variable steps.
 */
interface Subtractable extends MetricType
{
    /**
     * Decrease the metric value by the given amount.
     *
     * @param float $value
     */
    public function sub($value);
}
