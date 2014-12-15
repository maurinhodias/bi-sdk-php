# BI SDK for PHP

This library allows you to quickly integrate Funplus BI SDK and easily trace user events using PHP.

Important: **This library requires PHP 5.3 or higher**.

## Getting Started

This version requires [Composer](http://getcomposer.org/) installed in your system.

### Install with Composer

Add funplus/bi-sdk-php as a dependency and run composer update:

```
"requires": {
    "funplus/bi-sdk-php": "0.1.1"
    ...
}
```

### (Optional) Manually install.

1. Copy folder bi-sdk-php/ to your project.
2. Enter folder bi-sdk-php/, and run `composer install`.

## Quick Example

1. Import denpendencies.

    ```
    <?php
    require 'bi-sdk-php/vendor/autoload.php';

    use Bi\BiClient;
    ```

2. Initialize.

    ```
    BiClient::initialize(
        array('app_id' => '<app_id>'),
        '<consumer_type>',
        array(
            's3_bucket' => '<bucket>',
            's3_key'=> '<access_key>',
            's3_secret' => '<secret_key>',
            's3_region' => '<region>'
        )
    );
    ```

3. Trace an event.

    ```
    BiClient::instance()->trace(
        '<event_name>',
        '<user_id>',
        '<session_id>',
        '<event_properties>',
        '<event_collections>'
    );
    ```

## API Reference

### 1. Initialize the client.

Method signature:

```
public static Bi\BiClient initialize(array $config, string $consumer_type, array $consumer_options);
```

Examples: 

```
BiClient::initialize(
    array(
        // app_id is required
        'app_id' => 'string',
        'max_queue_size' => 'integer',
        'on_error' => 'string'
    ),
    'Bi\Consumer\BiS3Consumer',
    array(
        's3_bucket' => 'string',
        's3_key'    => 'string',
        's3_secret' => 'string',
        's3_region' => 'string'
    )
);
```

Parameters:

`$config`. The configuration values that are used internally by BI SDK:

- `app_id(required)`. The unique game ID.

- `max_queue_size'`. The size of cache queue; the default is 512.

- `on_error`. The name of the error callback function when tracing an event. It shall accept one string parameter which indicates the error message. If you do not concern about errors, you may ignore it.

`$consumer_type`. An easy and friendly Producer-Consumer Model is applied in this SDK logic, which gives us flexibility to alter consumer strategy. Following are built-in consumers:

- `Bi\Consumer\S3Consumer`.

- `Bi\Consumer\FileConsumer`.

If performance is not among your concerns, `S3Consumer` would be the best option, because it will upload data directly to the AWS server. Alternatively, you can use `FileConsumer` for a better performance. It will temporarily store data in local files, which can be uploaded periodicly to the AWS server by using [bi-s3-uploader](https://bitbucket.org/yuankun/bi-s3-uploader).
	
`$consumer_options`. Settings for consumer with specific `$consumer_type`.

- For `Bi\Consumer\S3Consumer`, you should provide these fields: `s3_bucket`, `s3_key`, `s3_secret`, `s3_region`.
    
- For `Bi\Consumer\FileConsumer`, you should provide one field named `output_dir`.


### 2. Check if BI client is initialized.

Method signature:

```
public static Bi\BiClient is_initialized();
```

Returns:

- If the client is properly initialized, return true.
- Otherwise, return false.

### 3. Get client instance.

Method signature:

```
public static Bi\BiClient instance();
```

Returns:

- The singleton instance of `Bi\BiClient`.

### 4. Trace a user event.

Method signature:

```
public static Bi\BiClient trace(string $event, string $user_id, string $session_id, string $properties, string $collections ＝ null);
```

Examples:

```
BiClient::instance()->trace(
    'new_user',
    '16118',
    '4edee82ba2de63d85746389ea1211b68',
    '{
        "app_version": "1.2",
        "os": "iOS",
        "os_version": "8.1",
        "ip": "10.13.72.132",
        "lang": "en_US",
        "level": 11,
        "install_ts": "1411541374"
    }'
);
```

Parameters:

`$event`. Event name.

`$user_id`. User's game ID.

`$session_id`. A unique ID representing the current play session.

`$properties`. Extra properties for an event. Keep in mind that it is a serialized JSON **string**. For example:

```
'{"device": "iPhone2,1", "ip": "10.13.72.132", "os": "ios", "os_version": "6.1.3"}'

```

`$collections`. Optional collections for an event; it can be null. Keep in mind that it is a serialized JSON **string**. For example:

```
'{
    "items_received": [
        {"item_id": "8100012", "item_name": "Yogurt Maker", "item_class": "durable", "item_type": "construction", "item_amount": 3}
    ],
    "resources_received": [
        {"resource_id": "998", "resource_name": "coins", "resource_type": "currency", "resource_amount": 100}
    ]
}'
```

Returns:

- True if successful, false if not.

For full event reference, please visit [this spreadsheet](https://docs.google.com/spreadsheets/d/1ILr_Zn_Hhn_zakWJ7X8v5YbzKoniczxbSKqX58nrpXY/edit?usp=sharing).

## Manually upload messages.

If the size of the message queue reaches `max_queue_size`, the message queue will be uploaded automatically. You can also upload the message queue manually.

Method signature:

```
public static Bi\BiClient flush();
```

## Running Tests

BI-php is well tested. The existed tests in the test directory can be run by using [PHPUnit](https://github.com/sebastianbergmann/phpunit/) with the following commands:

```
composer update --dev
cd test
../vendor/bin/phpunit .
```

Or if you already have PHPUnit installed globally:

```
cd test
phpunit .
```

## More Examples

More examples can be found in directory bi-sdk-php/examples/.

## Contact

Any question? Please contact Yuankun Zhang <yuankun.zhang@funplus.com>.
