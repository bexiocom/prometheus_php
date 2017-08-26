<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Action\Add.
 */

namespace Bexio\PrometheusPHP\Action;

use Bexio\PrometheusPHP\Action;
use Bexio\PrometheusPHP\StorageAdapter;
use Bexio\PrometheusPHP\Type\Addable;
use Bexio\PrometheusPHP\Type\Incrementable;

/**
 * Increment action.
 *
 * Increments the given number to the metric.
 */
class Increment implements Action
{
    /**
     * @var Addable
     */
    private $metric;

    /**
     * @param Incrementable $metric
     *
     * @return Increment
     */
    public static function createFromValue(Incrementable $metric)
    {
        return new Increment($metric);
    }

    /**
     * Constructor.
     *
     * @param Incrementable $metric
     */
    private function __construct(Incrementable $metric)
    {
        $this->metric = $metric;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(StorageAdapter $storage)
    {
        $storage->inc($this->metric);
    }
}
