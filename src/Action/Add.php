<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Action\Add.
 */

namespace Bexio\PrometheusPHP\Action;

use Bexio\PrometheusPHP\Action;
use Bexio\PrometheusPHP\StorageAdapter;
use Bexio\PrometheusPHP\Type\Addable;

/**
 * Add action.
 *
 * Adds the given number to the metric.
 */
class Add implements Action
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
     * @param Addable $metric
     * @param float   $value
     *
     * @return Add
     */
    public static function createFromValue(Addable $metric, $value)
    {
        return new Add($metric, $value);
    }

    /**
     * Constructor.
     *
     * @param Addable $metric
     * @param float   $value
     */
    private function __construct(Addable $metric, $value)
    {
        $this->metric = $metric;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(StorageAdapter $storage)
    {
        $storage->add($this->metric, $this->value);
    }
}
