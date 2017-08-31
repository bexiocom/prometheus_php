<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\StorageAdapter.
 */

namespace Bexio\PrometheusPHP;

use Bexio\PrometheusPHP\Exception\StorageException;
use Bexio\PrometheusPHP\Type\Addable;
use Bexio\PrometheusPHP\Type\Decrementable;
use Bexio\PrometheusPHP\Type\Incrementable;
use Bexio\PrometheusPHP\Type\Observable;
use Bexio\PrometheusPHP\Type\Settable;
use Bexio\PrometheusPHP\Type\Subtractable;

/**
 * A storage adapter is the place to store metric samples.
 */
interface StorageAdapter
{

    /**
     * Applies all the change actions.
     *
     * @param MetricType $metric
     *
     * @throws StorageException
     */
    public function persist(MetricType $metric);

    /**
     * @param Incrementable $metric
     *
     * @throws StorageException
     */
    public function inc(Incrementable $metric);

    /**
     * @param Decrementable $metric
     *
     * @throws StorageException
     */
    public function dec(Decrementable $metric);

    /**
     * @param Settable $metric
     * @param float    $value
     *
     * @throws StorageException
     */
    public function set(Settable $metric, $value);

    /**
     * @param Addable $metric
     * @param float   $value
     *
     * @throws StorageException
     */
    public function add(Addable $metric, $value);

    /**
     * @param Subtractable $metric
     * @param float        $value
     *
     * @throws StorageException
     */
    public function sub(Subtractable $metric, $value);

    /**
     * @param Observable $metric
     * @param float      $value
     *
     * @throws StorageException
     */
    public function observe(Observable $metric, $value);

    /**
     * Collects samples of a metric.
     *
     * @param MetricType $metric
     *
     * @return Sample[]
     *
     * @throws StorageException
     */
    public function collectSamples(MetricType $metric);
}
