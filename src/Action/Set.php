<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Action\Set.
 */

namespace Bexio\PrometheusPHP\Action;

use Bexio\PrometheusPHP\Action;
use Bexio\PrometheusPHP\StorageAdapter;
use Bexio\PrometheusPHP\Type\Settable;

/**
 * Set action.
 *
 * Sets the given number to the metric.
 */
class Set implements Action
{
    /**
     * @var Settable
     */
    private $metric;

    /**
     * @var float
     */
    private $value;

    /**
     * @param Settable $metric
     * @param float   $value
     *
     * @return Set
     */
    public static function createFromValue(Settable $metric, $value)
    {
        return new Set($metric, $value);
    }

    /**
     * Constructor.
     *
     * @param Settable $metric
     * @param float   $value
     */
    private function __construct(Settable $metric, $value)
    {
        $this->metric = $metric;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(StorageAdapter $storage)
    {
        $storage->Set($this->metric, $this->value);
    }
}
