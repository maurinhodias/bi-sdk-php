<?php

require_once(dirname(__FILE__) . '/../vendor/autoload.php');

use Bi\BiClient;
use Bi\Core\BiEvent;

class BiEventTest extends PHPUnit_Framework_TestCase {

    protected function setUp() {
        parent::setUp();
        BiClient::initialize(array('app_id' => 'com.funplus.daota'), 'Bi\Consumer\BiTestingConsumer', array());
    }

    protected function tearDown() {
        parent::tearDown();
    }

    public function testEventValidate() {
        $event1 = new BiEvent(
            'newuser',
            '1001',
            '12345',
            '{"app_version": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.1", "lang": "en", "level": 11, "install_ts": "123456"}'
        );
        $valid1 = $event1->validate();
        $this->assertEquals(false, $valid1[0]);
        $this->assertEquals('BiEvent not exists.', $valid1[1]);

        $event2 = new BiEvent(
            'new_user',
            1001,
            '12345',
            '{"app_version": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.1", "lang": "en", "level": 11, "install_ts": "123456"}'
        );
        $valid2 = $event2->validate();
        $this->assertEquals(false, $valid2[0]);
        $this->assertEquals('[Attribute] Type mismatch: user_id.', $valid2[1]);

        $event3 = new BiEvent(
            'new_user',
            '1001',
            '12345',
            '{"app_ver": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.1", "lang": "en", "level": 11, "install_ts": "123456"}'
        );
        $valid3 = $event3->validate();
        $this->assertEquals(false, $valid3[0]);
        $this->assertEquals('[Property] Missing value: app_version.', $valid3[1]);

        $event4 = new BiEvent(
            'new_user',
            '1001',
            '12345',
            '{"app_version": 1.2, "os": "iOS", "os_version": "8.1", "ip": "192.168.0.1", "lang": "en", "level": 11, "install_ts": "123456"}'
        );
        $valid4 = $event4->validate();
        $this->assertEquals(false, $valid4[0]);
        $this->assertEquals('[Property] Type mismatch: app_version.', $valid4[1]);

        $event5 = new BiEvent(
            'new_user',
            '1001',
            '12345',
            '{"app_version": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.1", "lang": "en", "level": 11, "install_ts": "123456"}'
        );
        $valid5 = $event5->validate();
        $this->assertEquals(true, $valid5[0]);

        $event6 = new BiEvent(
            'new_user',
            '1001',
            '12345',
            '{"app_version": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.1", "lang": "en", "level": 11, "install_ts": "123456"}',
            'collections'
        );
        $valid6 = $event6->validate();
        $this->assertEquals(false, $valid6[0]);
        $this->assertEquals('[Collection] Wrong JSON format.', $valid6[1]);

        $event7 = new BiEvent(
            'new_user',
            '1001',
            '12345',
            '{"app_version": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.1", "lang": "en", "level": 11, "install_ts": "123456"}',
            '{"player_resources": [{"resource_id": "998"}]}'
        );
        $valid7 = $event7->validate();
        $this->assertEquals(false, $valid7[0]);
        $this->assertEquals('[Collection] Missing value for Resource: resource_name.', $valid7[1]);

        $event8 = new BiEvent(
            'new_user',
            '1001',
            '12345',
            '{"app_version": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.1", "lang": "en", "level": 11, "install_ts": "123456"}',
            '{"player_resources": [{"resource_id": 998}]}'
        );
        $valid8 = $event8->validate();
        $this->assertEquals(false, $valid8[0]);
        $this->assertEquals('[Collection] Type mismatch for Resource: resource_id.', $valid8[1]);

        $event9 = new BiEvent(
            'new_user',
            '1001',
            '12345',
            '{"app_version": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.1", "lang": "en", "level": 11, "install_ts": "123456"}',
            '{"player_resources": [{"resource_id": "998", "resource_name": "coins", "resource_type": "currency", "resource_amount": 100}]}'
        );
        $valid9 = $event9->validate();
        $this->assertEquals(true, $valid9[0]);
    }

    public function testEventSerialize() {
        $event = new BiEvent(
            'new_user',
            '1001',
            '12345',
            '{"app_version": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.1", "lang": "en", "level": 11, "install_ts": "123456"}',
            '{"player_resources": [{"resource_id": "998", "resource_name": "coins", "resource_type": "currency", "resource_amount": 100}]}',
            123456
        );
        $this->assertEquals('{"app_id": "com.funplus.daota", "bi_version": "1.2", "event": "new_user", "user_id": "1001", "session_id": "12345", "ts": 123456, "properties": {"app_version": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.1", "lang": "en", "level": 11, "install_ts": "123456"}, "collections": {"player_resources": [{"resource_id": "998", "resource_name": "coins", "resource_type": "currency", "resource_amount": 100}]}}', $event->serialize());
    }
}
