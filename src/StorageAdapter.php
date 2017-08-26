<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\StorageAdapter.
 */

namespace Bexio\PrometheusPHP;

use Bexio\PrometheusPHP\Type\Addable;
use Bexio\PrometheusPHP\Type\Decrementable;
use Bexio\PrometheusPHP\Type\Incrementable;
use Bexio\PrometheusPHP\Type\Settable;
use Bexio\PrometheusPHP\Type\Subtractable;

/**
 * A storage adapter is the place to store metric samples.
 */
interface StorageAdapter
{
    /**
     * @param Incrementable $metric
     */
    public function inc(Incrementable $metric);

    /**
     * @param Decrementable $metric
     */
    public function dec(Decrementable $metric);

    /**
     * @param Settable $metric
     * @param float $value
     */
    public function set(Settable $metric, $value);

    /**
     * @param Addable $metric
     * @param float  $value
     */
    public function add(Addable $metric, $value);

    /**
     * @param Subtractable $metric
     * @param float $value
     */
    public function sub(Subtractable $metric, $value);

    /**
     * @param MetricType $metric
     *
     * @return Sample
     */
    public function collectSample(MetricType $metric);
}
