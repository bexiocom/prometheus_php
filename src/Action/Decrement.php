<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Action\Add.
 */

namespace Bexio\PrometheusPHP\Action;

use Bexio\PrometheusPHP\Action;
use Bexio\PrometheusPHP\StorageAdapter;
use Bexio\PrometheusPHP\Type\Decrementable;

/**
 * Decrement action.
 *
 * Decrements the given number from the metric.
 */
class Decrement implements Action
{
    /**
     * @var Decrementable
     */
    private $metric;

    /**
     * @param Decrementable $metric
     *
     * @return Decrement
     */
    public static function createFromValue(Decrementable $metric)
    {
        return new Decrement($metric);
    }

    /**
     * Constructor.
     *
     * @param Decrementable $metric
     */
    private function __construct(Decrementable $metric)
    {
        $this->metric = $metric;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(StorageAdapter $storage)
    {
        $storage->dec($this->metric);
    }
}
