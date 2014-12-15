<?php

require('../vendor/autoload.php');

use Bi\BiClient;

BiClient::initialize(
    array('app_id' => 'com.funplus.daota', 'max_queue_size' => 1024),
    'Bi\Consumer\BiS3Consumer',
    array(
        's3_bucket' => 'your-s3-bucket',
        's3_key'    => 'your-s3-key',
        's3_secret' => 'your-s3-secret',
        's3_region' => 'your-s3-region'
    )
);

for ($i = 0; $i < 1024; $i++) {
    // Will auto trigger message flushing when $i reaches 1023
    BiClient::instance()->trace(
        'new_user',
        '1001',
        '12345',
        '{"app_version": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.1", "lang": "en", "level": 11, "install_ts": "123456"}'
    );
}

print('Size of remaining queue: '.BiClient::instance()->get_producer()->queue_size());
