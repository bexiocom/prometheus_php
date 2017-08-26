<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Action\Add.
 */

namespace Bexio\PrometheusPHP\Action;

use Bexio\PrometheusPHP\Action;
use Bexio\PrometheusPHP\StorageAdapter;
use Bexio\PrometheusPHP\Type\Addable;
use Bexio\PrometheusPHP\Type\Subtractable;

/**
 * Subtract action.
 *
 * Subtracts the given number from the metric.
 */
class Subtract implements Action
{
    /**
     * @var Addable
     */
    private $metric;

    /**
     * @var float
     */
    private $value;

    /**
     * @param Subtractable $metric
     * @param float   $value
     *
     * @return Subtract
     */
    public static function createFromValue(Subtractable $metric, $value)
    {
        return new Subtract($metric, $value);
    }

    /**
     * Constructor.
     *
     * @param Subtractable $metric
     * @param float   $value
     */
    private function __construct(Subtractable $metric, $value)
    {
        $this->metric = $metric;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(StorageAdapter $storage)
    {
        $storage->sub($this->metric, $this->value);
    }
}
