<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Options.
 */

namespace Bexio\PrometheusPHP;

/**
 * Options bundles the options for creating most Metric types. Each metric implementation has its own options type,
 * but in most cases, it is just be an alias of this type (which might change when the requirement arises.)
 *
 * It is mandatory to set Name and Help to a non-empty string. All other fields are optional and can safely be left at
 * their zero value.
 *
 * Namespace, Subsystem, and Name are components of the fully-qualified name of the Metric (created by joining these
 * components with "_"). Only Name is mandatory, the others merely help structuring the name. Note that the
 * fully-qualified name of the metric must be a valid Prometheus metric name.
 */
abstract class Options
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $subsystem;

    /**
     * @var string
     */
    private $name;

    /**
     * Help provides information about this metric.
     *
     * Metrics with the same fully-qualified name must have the same Help string.
     *
     * @var string
     */
    private $help;

    /**
     * Fixed list of label of key value pairs attached to this metric.
     *
     * Metrics with the same fully-qualified name must have the same label names.
     *
     * Note that in most cases, labels have a value that varies during the lifetime of a process. Those labels are
     * usually managed with a metric collection (like CounterCollection or GaugeCollection). ConstLabels serve only
     * special purposes. One is for the special case where the value of a label does not change during the lifetime of
     * a process, e.g. if the revision of the running binary is put into a label. Another, more advanced purpose is if
     * more than one collector needs to collect metrics with the same fully-qualified name. In that case, those metrics
     * must differ in the values of their ConstLabels.
     *
     * If the value of a label never changes (not even between binaries), that label most likely should not be a label
     * at all (but part of the metric name).
     *
     * @var string[]
     */
    private $constantLabels;

    /**
     * Constructor.
     *
     * @param string   $name The metric name
     * @param string   $help The help information for this metric.
     * @param string   $namespace (optional) The metric namespace.
     * @param string   $subsystem (optional) The metric subsystem.
     * @param string[] $constantLabels (optional) Key value pairs of static metric labels.
     */
    public function __construct($name, $help, $namespace = null, $subsystem = null, array $constantLabels = array())
    {
        $this->namespace = $namespace;
        $this->subsystem = $subsystem;
        $this->name = $name;
        $this->help = $help;
        $this->constantLabels = $constantLabels;
    }

    /**
     * @return string
     */
    public function getFullyQualifiedName()
    {
        return implode('_', array_filter(array($this->namespace, $this->subsystem, $this->name)));
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getSubsystem()
    {
        return $this->subsystem;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * @return \string[]
     */
    public function getLabels()
    {
        return $this->constantLabels;
    }
}
