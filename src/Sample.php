<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Sample.
 */

namespace Bexio\PrometheusPHP;

class Sample
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $labels;

    /**
     * @var float
     */
    private $value;

    /**
     * @param Options $options
     * @param float   $value
     *
     * @return Sample
     */
    public static function createFromOptions(Options $options, $value)
    {
        return new Sample($options->getFullyQualifiedName(), $options->getLabels(), $value);
    }

    /**
     * Constructor.
     *
     * @param string    $name
     * @param \string[] $labels
     * @param float     $value
     */
    private function __construct($name, array $labels, $value)
    {
        $this->name = $name;
        $this->labels = $labels;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \string[]
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }
}
