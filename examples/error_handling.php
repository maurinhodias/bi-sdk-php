<?php

require('../vendor/autoload.php');

use Bi\BiClient;

function on_error($error_message) {
    print($error_message);
}

BiClient::initialize(
    array('app_id' => 'com.funplus.daota', 'on_error' => 'on_error'),
    'Bi\Consumer\BiS3Consumer',
    array(
        's3_bucket' => 'your-s3-bucket',
        's3_key'    => 'your-s3-key',
        's3_secret' => 'your-s3-secret',
        's3_region' => 'your-s3-region'
    )
);

BiClient::instance()->trace(
    'new_user',
    1001,       // wrong type, should be string
    '12345',
    '{"app_version": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.1", "lang": "en", "level": 11, "install_ts": "123456"}'
);
