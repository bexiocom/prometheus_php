<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Type\Settable.
 */

namespace Bexio\PrometheusPHP\Type;

use Bexio\PrometheusPHP\MetricType;

/**
 * Metric qualifier for metrics which can be set to arbitrary numbers.
 */
interface Settable extends MetricType
{
    /**
     * Set the metric value to the given number.
     *
     * @param float $value
     */
    public function set($value);
}
