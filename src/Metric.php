<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Metric.
 */

namespace Bexio\PrometheusPHP;

/**
 * Base class for Metrics.
 */
abstract class Metric implements MetricType
{
    /**
     * @var Options
     */
    private $options;

    /**
     * @var Action[]
     */
    protected $actions;

    /**
     * Constructor.
     *
     * @param Options $options
     */
    protected function __construct(Options $options)
    {
        $this->options = $options;
        $this->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->actions = array();
    }
}
