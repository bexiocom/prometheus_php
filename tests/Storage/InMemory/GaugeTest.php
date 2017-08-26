<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Tests\Storage\InMemory\GaugeTest.
 */

namespace Bexio\PrometheusPHP\Tests\Storage\InMemory;

use Bexio\PrometheusPHP\Metric\Gauge;
use Bexio\PrometheusPHP\Storage\InMemory;

class GaugeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemory
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new InMemory(array(
            'foo_bar_baz' => array(
                'default' => 3,
                '{"foo":"bar"}' => 5,
            ),
        ));
    }

    /**
     * @return array
     */
    public function getIncrementData()
    {
        return array(
            array(GaugeTest::getDefaultGauge(), 4),
            array(GaugeTest::getLabeledGauge(), 6),
        );
    }

    /**
     * @param Gauge   $metric
     * @param         $expected
     *
     * @dataProvider getIncrementData
     */
    public function testIncrement(Gauge $metric, $expected)
    {
        $this->subject->inc($metric);
        $sample = $this->subject->collectSample($metric);
        $this->assertEquals($expected, $sample->getValue());
    }

    /**
     * @return array
     */
    public function getDecrementData()
    {
        return array(
            array(GaugeTest::getDefaultGauge(), 2),
            array(GaugeTest::getLabeledGauge(), 4),
        );
    }

    /**
     * @param Gauge   $metric
     * @param         $expected
     *
     * @dataProvider getDecrementData
     */
    public function testDecrement(Gauge $metric, $expected)
    {
        $metric->dec();
        $this->subject->persist($metric);
        $sample = $this->subject->collectSample($metric);
        $this->assertEquals($expected, $sample->getValue());
    }

    /**
     * @return array
     */
    public function getAdditionData()
    {
        return array(
            array(GaugeTest::getDefaultGauge(), 5, 8),
            array(GaugeTest::getLabeledGauge(), 7, 12),
        );
    }

    /**
     * @param Gauge $metric
     * @param float $value
     * @param float $expected
     *
     * @dataProvider getAdditionData
     */
    public function testAddition(Gauge $metric, $value, $expected)
    {
        $metric->add($value);
        $this->subject->persist($metric);
        $sample = $this->subject->collectSample($metric);
        $this->assertEquals($expected, $sample->getValue());
    }

    /**
     * @return array
     */
    public function getSubtractionData()
    {
        return array(
            array(GaugeTest::getDefaultGauge(), 5, -2),
            array(GaugeTest::getLabeledGauge(), 3, 2),
        );
    }

    /**
     * @param Gauge $metric
     * @param float $value
     * @param float $expected
     *
     * @dataProvider getSubtractionData
     */
    public function testSubtraction(Gauge $metric, $value, $expected)
    {
        $metric->sub($value);
        $this->subject->persist($metric);
        $sample = $this->subject->collectSample($metric);
        $this->assertEquals($expected, $sample->getValue());
    }

    /**
     * @return array
     */
    public function getSetterData()
    {
        return array(
            array(GaugeTest::getDefaultGauge(), 5),
            array(GaugeTest::getLabeledGauge(), 3),
        );
    }

    /**
     * @param Gauge $metric
     * @param float $value
     *
     * @dataProvider getSetterData
     */
    public function testSetter(Gauge $metric, $value)
    {
        $metric->set($value);
        $this->subject->persist($metric);
        $sample = $this->subject->collectSample($metric);
        $this->assertEquals($value, $sample->getValue());
    }

    /**
     * @return array
     */
    public function getSamplesData()
    {
        return array(
            array(GaugeTest::getDefaultGauge(), 'foo_bar_baz', array(), 3),
            array(GaugeTest::getLabeledGauge(), 'foo_bar_baz', array('foo' => 'bar'), 5),
            array(Gauge::createFromValues('foo', null), 'foo', array(), null),
        );
    }

    /**
     * @param Gauge    $metric
     * @param string   $name
     * @param string[] $labels
     * @param float    $value
     *
     * @dataProvider getSamplesData
     */
    public function testSamples(Gauge $metric, $name, array $labels, $value)
    {
        $sample = $this->subject->collectSample($metric);
        $this->assertEquals($name, $sample->getName());
        $this->assertEquals($labels, $sample->getLabels());
        $this->assertEquals($value, $sample->getValue());
    }

    /**
     * @return Gauge
     */
    private static function getDefaultGauge()
    {
        return Gauge::createFromValues('baz', null, 'foo', 'bar');
    }

    /**
     * @return Gauge
     */
    private static function getLabeledGauge()
    {
        return Gauge::createFromValues('baz', null, 'foo', 'bar', array(
            'foo' => 'bar',
        ));
    }
}
