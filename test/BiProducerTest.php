<?php

require_once(dirname(__FILE__) . '/../vendor/autoload.php');

use Bi\BiClient;
use Bi\Producer\BiProducer;

class BiProducerTest extends PHPUnit_Framework_TestCase {

    protected function setUp() {
        parent::setUp();
        BiClient::initialize(array('app_id' => 'com.funplus.daota'), 'Bi\Consumer\BiTestingConsumer', array());
    }

    protected function tearDown() {
        parent::tearDown();
    }

    public function testTraceEventAndReset() {
        $producer = new BiProducer();

        for ($i = 0; $i < 10; $i++) {
            $this->assertTrue($producer->trace(
                'new_user',
                '1001',
                '12345',
                '{"app_version": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.1", "lang": "en", "level": 11, "install_ts": "123456"}'
            ));
            $this->assertEquals($i + 1, $producer->queue_size());
        }

        $producer->reset();
        $this->assertEquals(0, $producer->queue_size());
    }

    public function testFlush() {
        $producer = new BiProducer();

        for ($i = 0; $i < 10; $i++) {
            $this->assertTrue($producer->trace(
                'new_user',
                '1001',
                '12345',
                '{"app_version": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.1", "lang": "en", "level": 11, "install_ts": "123456"}'
            ));
        }

        $this->assertEquals(10, $producer->queue_size());
        $producer->flush();
        $this->assertEquals(0, $producer->queue_size());
    }

    public function testAutoFlush() {
        $producer = new BiProducer();

        for ($i = 0; $i < 511; $i++) {
            $this->assertTrue($producer->trace(
                'new_user',
                '1001',
                '12345',
                '{"app_version": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.1", "lang": "en", "level": 11, "install_ts": "123456"}'
            ));
        }

        $this->assertEquals(511, $producer->queue_size());
        $this->assertTrue($producer->trace(
            'new_user',
            '1001',
            '12345',
            '{"app_version": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.1", "lang": "en", "level": 11, "install_ts": "123456"}'
        ));
        $this->assertEquals(0, $producer->queue_size());
    }
}
