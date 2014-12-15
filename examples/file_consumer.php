<?php

require('../vendor/autoload.php');

use Bi\BiClient;

BiClient::initialize(
    array('app_id' => 'com.funplus.daota'),
    'Bi\Consumer\BiFileConsumer',
    array(
        'output_dir' => dirname(__FILE__) . '/output'
    )
);

for ($i = 0; $i < 1024; $i++) {
    BiClient::instance()->trace(
        'new_user',
        '1001',
        '12345',
        '{"app_version": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.1", "lang": "en", "level": 11, "install_ts": "123456"}'
    );
}

for ($i = 0; $i < 1024; $i++) {
    BiClient::instance()->trace(
        'session_start',
        '1001',
        '12345',
        '{"app_version": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.1", "lang": "en", "level": 11, "install_ts": "123456"}'
    );
}

// At the end, trigger message flushing manually.
BiClient::instance()->flush();
