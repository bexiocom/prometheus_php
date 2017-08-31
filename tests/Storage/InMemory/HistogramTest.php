<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Tests\Storage\InMemory\HistogramTest.
 */

namespace Bexio\PrometheusPHP\Tests\Storage\InMemory;

use Bexio\PrometheusPHP\Metric\Counter;
use Bexio\PrometheusPHP\Metric\CounterCollection;
use Bexio\PrometheusPHP\Metric\Histogram;
use Bexio\PrometheusPHP\Metric\HistogramCollection;
use Bexio\PrometheusPHP\Sample;
use Bexio\PrometheusPHP\Storage\ArrayStorage;
use Bexio\PrometheusPHP\Storage\InMemory;

class HistogramTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemory
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new InMemory(array(
            'foo_bar_baz' => array(
                '{"le":1}' => 7,
                '{"le":10}' => 5,
                '{"le":"+Inf"}' => 3,
                '{"foo":"bar","le":1}' => 11,
                '{"foo":"bar","le":10}' => 7,
                '{"foo":"bar","le":"+Inf"}' => 5,
                '{"foo":"baz","le":10}' => 17,
            ),
            'foo_bar_baz_sum' => array(
                ArrayStorage::DEFAULT_VALUE_INDEX => 228,
                '{"foo":"bar"}' => 467,
                '{"foo":"baz"}' => 85,
            ),
        ));
    }

    /**
     * @return array
     */
    public function getSingleData()
    {
        $defaultLowBucketResults = array(8, 8 + 5, 8 + 5 + 3);
        $defaultHighBucketResults = array(7, 7 + 6, 7 + 6 + 3);
        $defaultInfBucketResults = array(7, 7 + 5, 7 + 5 + 4);
        $labeledLowBucketResults = array(12, 12 + 7, 12 + 7 + 5);
        $labeledHighBucketResults = array(11, 11 + 8, 11 + 8 + 5);
        $labeledInfBucketResults = array(11, 11 + 7, 11 + 7 + 6);
        $nullLowBucketResults = array(1 , 1 , 1);
        $nullHighBucketResults = array(0 , 1 , 1);
        $nullInfBucketResults = array(0 , 0 , 1);

        return array(
            array(HistogramTest::getDefaultHistogram(), 0.001, $defaultLowBucketResults, 228.001),
            array(HistogramTest::getDefaultHistogram(), 0.999, $defaultLowBucketResults, 228.999),
            array(HistogramTest::getDefaultHistogram(), 1, $defaultLowBucketResults, 229),
            array(HistogramTest::getDefaultHistogram(), 1.001, $defaultHighBucketResults, 229.001),
            array(HistogramTest::getDefaultHistogram(), 9.999, $defaultHighBucketResults, 237.999),
            array(HistogramTest::getDefaultHistogram(), 10, $defaultHighBucketResults, 238),
            array(HistogramTest::getDefaultHistogram(), 11.001, $defaultInfBucketResults, 239.001),
            array(HistogramTest::getDefaultHistogram(), 11, $defaultInfBucketResults, 239),
            array(HistogramTest::getLabeledHistogram(), 0.001, $labeledLowBucketResults, 467.001),
            array(HistogramTest::getLabeledHistogram(), 0.999, $labeledLowBucketResults, 467.999),
            array(HistogramTest::getLabeledHistogram(), 1, $labeledLowBucketResults, 468),
            array(HistogramTest::getLabeledHistogram(), 1.001, $labeledHighBucketResults, 468.001),
            array(HistogramTest::getLabeledHistogram(), 9.999, $labeledHighBucketResults, 476.999),
            array(HistogramTest::getLabeledHistogram(), 10, $labeledHighBucketResults, 477),
            array(HistogramTest::getLabeledHistogram(), 11.001, $labeledInfBucketResults, 478.001),
            array(HistogramTest::getLabeledHistogram(), 11, $labeledInfBucketResults, 478),
            array(Histogram::createFromValues('foo', null, array(1, 10)), 0.001, $nullLowBucketResults, 0.001),
            array(Histogram::createFromValues('foo', null, array(1, 10)), 0.999, $nullLowBucketResults, 0.999),
            array(Histogram::createFromValues('foo', null, array(1, 10)), 1, $nullLowBucketResults, 1),
            array(Histogram::createFromValues('foo', null, array(1, 10)), 1.001, $nullHighBucketResults, 1.001),
            array(Histogram::createFromValues('foo', null, array(1, 10)), 9.999, $nullHighBucketResults, 9.999),
            array(Histogram::createFromValues('foo', null, array(1, 10)), 10, $nullHighBucketResults, 10),
            array(Histogram::createFromValues('foo', null, array(1, 10)), 11.001, $nullInfBucketResults, 11.001),
            array(Histogram::createFromValues('foo', null, array(1, 10)), 11, $nullInfBucketResults, 11),
        );
    }

    /**
     * @param Histogram $metric
     * @param float     $value       The observed value.
     * @param float[]   $expected    The expected bucket values.
     * @param float     $expectedSum The expected histogram sum.
     *
     * @dataProvider getSingleData
     */
    public function testSingle(Histogram $metric, $value, array $expected, $expectedSum)
    {
        $metric->observe($value);
        $this->subject->persist($metric);
        $samples = $this->subject->collectSamples($metric);
        $count = end($expected);
        $expected[] = $expectedSum;
        $expected[] = $count;
        $this->assertEquals(count($expected), count($samples));
        for ($i = 0; $i < count($expected); $i++) {
            $this->assertEquals($expected[$i], $samples[$i]->getValue());
        }
    }

    /**
     * Test retrieval of collection samples.
     */
    public function testCollectionSamples()
    {
        $collection = HistogramCollection::createFromValues('baz', 'Just a counter collection for testing', array(
            'foo',
        ), array(1, 10), 'foo', 'bar');

        $samples = $this->subject->collectSamples($collection);

        $options = $collection->getOptions();

        $this->assertEquals(array(
            Sample::createFromOptions($options->withLabels(array('le' => 1)), 7),
            Sample::createFromOptions($options->withLabels(array('le' => 10)), 12),
            Sample::createFromOptions($options->withLabels(array('le' => '+Inf')), 15),
            Sample::createFromValues('foo_bar_baz_sum', array(), 228),
            Sample::createFromValues('foo_bar_baz_count', array(), 15),
            Sample::createFromOptions($options->withLabels(array('foo' => 'bar', 'le' => 1)), 11),
            Sample::createFromOptions($options->withLabels(array('foo' => 'bar', 'le' => 10)), 18),
            Sample::createFromOptions($options->withLabels(array('foo' => 'bar', 'le' => '+Inf')), 23),
            Sample::createFromValues('foo_bar_baz_sum', array('foo' => 'bar'), 467),
            Sample::createFromValues('foo_bar_baz_count', array('foo' => 'bar'), 23),
            Sample::createFromOptions($options->withLabels(array('foo' => 'baz', 'le' => 1)), 0),
            Sample::createFromOptions($options->withLabels(array('foo' => 'baz', 'le' => 10)), 17),
            Sample::createFromOptions($options->withLabels(array('foo' => 'baz', 'le' => '+Inf')), 17),
            Sample::createFromValues('foo_bar_baz_sum', array('foo' => 'baz'), 85),
            Sample::createFromValues('foo_bar_baz_count', array('foo' => 'baz'), 17),
        ), $samples);
    }

    /**
     * @return Histogram
     */
    private static function getDefaultHistogram()
    {
        return Histogram::createFromValues('baz', null, array(1,10), 'foo', 'bar');
    }

    /**
     * @return Histogram
     */
    private static function getLabeledHistogram()
    {
        return Histogram::createFromValues('baz', null, array(1,10), 'foo', 'bar', array(
            'foo' => 'bar',
        ));
    }
}
