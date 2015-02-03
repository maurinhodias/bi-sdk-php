<?php

require('../vendor/autoload.php');

use Bi\BiClient;

BiClient::initialize(
    array('app_id' => 'your-app-id'),
    'Bi\Consumer\BiFunplusGlobalFrontEndConsumer',
    array(
        'endpoint' => 'http_end_point',
        'project' => 'your_project_name',
        'group' => 'your_group_name',
        'archive' => 1,
        'es' => 0,
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
