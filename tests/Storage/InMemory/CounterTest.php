<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Tests\Storage\InMemory\CounterTest.
 */

namespace Bexio\PrometheusPHP\Tests\Storage\InMemory;

use Bexio\PrometheusPHP\Metric\Counter;
use Bexio\PrometheusPHP\Storage\InMemory;

class CounterTest extends \PHPUnit_Framework_TestCase
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
            array(CounterTest::getDefaultCounter(), 4),
            array(CounterTest::getLabeledCounter(), 6),
            array(Counter::createFromValues('foo', null), 1),
        );
    }

    /**
     * @param Counter $metric
     * @param         $expected
     *
     * @dataProvider getIncrementData
     */
    public function testIncrement(Counter $metric, $expected)
    {
        $metric->inc();
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
            array(CounterTest::getDefaultCounter(), 5, 8),
            array(CounterTest::getLabeledCounter(), 7, 12),
            array(Counter::createFromValues('foo', null), 3, 3),
        );
    }

    /**
     * @param Counter $metric
     * @param float   $value
     * @param float   $expected
     *
     * @dataProvider getAdditionData
     */
    public function testAddition(Counter $metric, $value, $expected)
    {
        $metric->add($value);
        $this->subject->persist($metric);
        $sample = $this->subject->collectSample($metric);
        $this->assertEquals($expected, $sample->getValue());
    }

    /**
     * @return array
     */
    public function getSamplesData()
    {
        return array(
            array(CounterTest::getDefaultCounter(), 'foo_bar_baz', array(), 3),
            array(CounterTest::getLabeledCounter(), 'foo_bar_baz', array('foo' => 'bar'), 5),
            array(Counter::createFromValues('foo', null), 'foo', array(), null),
        );
    }

    /**
     * @param Counter  $metric
     * @param string   $name
     * @param string[] $labels
     * @param float    $value
     *
     * @dataProvider getSamplesData
     */
    public function testSamples(Counter $metric, $name, $labels, $value)
    {
        $sample = $this->subject->collectSample($metric);
        $this->assertEquals($name, $sample->getName());
        $this->assertEquals($labels, $sample->getLabels());
        $this->assertEquals($value, $sample->getValue());
    }

    /**
     * @return Counter
     */
    private static function getDefaultCounter()
    {
        return Counter::createFromValues('baz', null, 'foo', 'bar');
    }

    /**
     * @return Counter
     */
    private static function getLabeledCounter()
    {
        return Counter::createFromValues('baz', null, 'foo', 'bar', array(
            'foo' => 'bar',
        ));
    }
}
