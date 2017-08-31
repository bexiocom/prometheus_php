<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Type\Observable.
 */

namespace Bexio\PrometheusPHP\Type;

use Bexio\PrometheusPHP\MetricType;

/**
 * Metric qualifier for metrics which observe a given value and update certain groups of values.
 */
interface Observable extends MetricType
{
    /**
     * Observes a value.
     *
     * @param float $value
     */
    public function observe($value);
}
