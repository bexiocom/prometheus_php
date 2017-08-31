<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Action\Add.
 */

namespace Bexio\PrometheusPHP\Action;

use Bexio\PrometheusPHP\Action;
use Bexio\PrometheusPHP\StorageAdapter;
use Bexio\PrometheusPHP\Type\Addable;
use Bexio\PrometheusPHP\Type\Observable;

/**
 * Observe action.
 *
 * Observe the given number and updates the according bucket/quantile of the metric.
 */
class Observe implements Action
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
     * @param Observable $metric
     * @param float      $value
     *
     * @return Observe
     */
    public static function createFromValue(Observable $metric, $value)
    {
        return new Observe($metric, $value);
    }

    /**
     * Constructor.
     *
     * @param Observable $metric
     * @param float      $value
     */
    private function __construct(Observable $metric, $value)
    {
        $this->metric = $metric;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(StorageAdapter $storage)
    {
        $storage->observe($this->metric, $this->value);
    }
}
