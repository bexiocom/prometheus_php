<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Tests\Storage\Redis\ExceptionTest.
 */

namespace Bexio\PrometheusPHP\Tests\Storage\Redis;

use Bexio\PrometheusPHP\Exception\StorageException;
use Bexio\PrometheusPHP\Metric\Gauge;
use Bexio\PrometheusPHP\Storage\Redis;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Redis|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redis;

    protected function setUp()
    {
        $this->redis = $this->getMock(\Redis::class);
    }

    /**
     * @return array
     */
    public function getExceptionsForOperationsData()
    {
        return array(
            'increment' => array('inc', 'hIncrByFloat', 'Failed to increment metric value'),
            'decrement' => array('dec', 'hIncrByFloat', 'Failed to decrement metric value'),
            'add' => array('add', 'hIncrByFloat', 'Failed to add metric value'),
            'subtract' => array('sub', 'hIncrByFloat', 'Failed to subtract metric value'),
            'set' => array('set', 'hSet', 'Failed to set metric value'),
            'collect' => array('collectSamples', 'hGetAll', 'Failed to collect metric samples'),
        );
    }

    /**
     * @param string $call    The method called on the storage.
     * @param string $method  The method called on Redis.
     * @param string $message The expected error message.
     *
     * @dataProvider getExceptionsForOperationsData
     */
    public function testExceptionsForOperations($call, $method, $message)
    {
        $subject = new Redis($this->redis);
        $this->redis->expects($this->once())
            ->method($method)
            ->willThrowException(new \Exception());
        $metric = Gauge::createFromValues('foo', 'bar');
        $this->setExpectedException(StorageException::class, $message);
        $subject->$call($metric, 3);
    }

    public function getOpenConnectionData()
    {
        return array(
            'increment' => array('inc'),
            'decrement' => array('dec'),
            'add' => array('add'),
            'subtract' => array('sub'),
            'set' => array('set'),
            'collect' => array('collectSamples'),
        );
    }

    /**
     * @param string $call The method called on the storage.
     *
     * @dataProvider getOpenConnectionData
     *
     * @expectedException \Bexio\PrometheusPHP\Exception\StorageException
     * @expectedExceptionMessage Failed to connect to Redis
     */
    public function testOpenConnection($call)
    {
        $subject = new Redis($this->redis, 'phpunit:', function () {
            throw new \Exception();
        });
        $metric = Gauge::createFromValues('foo', 'bar');
        $subject->$call($metric, 3);
    }
}
