<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Tests\MetricCollectionTest.
 */

namespace Bexio\PrometheusPHP\Tests;

use Bexio\PrometheusPHP\Metric\CounterCollection;
use Bexio\PrometheusPHP\Metric\CounterOptions;
use Bexio\PrometheusPHP\Metric\GaugeCollection;
use Bexio\PrometheusPHP\Metric\GaugeOptions;
use Bexio\PrometheusPHP\MetricTypeCollection;
use Bexio\PrometheusPHP\Options;

class MetricCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function getWithLabelsData()
    {
        return array(
            'Counter' => array(
                CounterCollection::createFromValues('foo', 'bar', array('foo')),
                array('foo' => 'bar'),
                new CounterOptions('foo', 'bar', null, null, array('foo' => 'bar')),
            ),
            'Counter with default labels' => array(
                CounterCollection::createFromValues('foo', 'bar', array('foo', 'bar'), null, null, array(
                    'foo' => 'foo',
                )),
                array('bar' => 'bar'),
                new CounterOptions('foo', 'bar', null, null, array('foo' => 'foo', 'bar' => 'bar')),
            ),
            'Gauge' => array(
                GaugeCollection::createFromValues('foo', 'bar', array('foo')),
                array('foo' => 'bar'),
                new GaugeOptions('foo', 'bar', null, null, array('foo' => 'bar')),
            ),
            'Gauge with default labels' => array(
                GaugeCollection::createFromValues('foo', 'bar', array('foo', 'bar'), null, null, array(
                    'foo' => 'foo',
                )),
                array('bar' => 'bar'),
                new GaugeOptions('foo', 'bar', null, null, array('foo' => 'foo', 'bar' => 'bar')),
            ),
        );
    }

    /**
     * @param MetricTypeCollection $collection
     * @param string[]             $labels
     * @param Options              $options
     *
     * @dataProvider getWithLabelsData
     */
    public function testWithLabels(MetricTypeCollection $collection, array $labels, Options $options)
    {
        $metric = $collection->withLabels($labels);
        $this->assertEquals($options, $metric->getOptions());
    }
    public function getLabelMismatchData()
    {
        $labelNames = array('foo', 'bar');
        $defaultLabels = array('foo' => 'foo');
        return array(
            'Counter missing label' => array(
                CounterCollection::createFromValues('foo', 'bar', $labelNames),
                array('foo' => 'foo'),
            ),
            'Counter with default labels and missing label' => array(
                CounterCollection::createFromValues('foo', 'bar', $labelNames, null, null, $defaultLabels),
                array(),
            ),
            'Counter excess label' => array(
                CounterCollection::createFromValues('foo', 'bar', $labelNames),
                array('foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz'),
            ),
            'Counter with default labels and excess label' => array(
                CounterCollection::createFromValues('foo', 'bar', $labelNames, null, null, $defaultLabels),
                array('bar' => 'bar', 'baz' => 'baz'),
            ),
            'Gauge missing label' => array(
                GaugeCollection::createFromValues('foo', 'bar', $labelNames),
                array('foo' => 'foo'),
            ),
            'Gauge with default labels and missing label' => array(
                GaugeCollection::createFromValues('foo', 'bar', $labelNames, null, null, $defaultLabels),
                array(),
            ),
            'Gauge excess label' => array(
                GaugeCollection::createFromValues('foo', 'bar', $labelNames),
                array('foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz'),
            ),
            'Gauge with default labels and excess label' => array(
                GaugeCollection::createFromValues('foo', 'bar', $labelNames, null, null, $defaultLabels),
                array('bar' => 'bar', 'baz' => 'baz'),
            ),
        );
    }

    /**
     * @param MetricTypeCollection $collection
     * @param string[]             $labels
     *
     * @dataProvider getLabelMismatchData
     *
     * @expectedException \Bexio\PrometheusPHP\Exception\LabelMismatchException
     * @expectedExceptionMessage Can not get metric with label set differing from the one of the collection
     */
    public function testLabelMismatch(MetricTypeCollection $collection, array $labels)
    {
        $collection->withLabels($labels);
    }
}
