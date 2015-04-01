### Version 0.1

* First public version.


### Version 0.1.1

* Remove `date_set_default_timezone()`, which can be dangerous.
* Remove all exception throw-outs from `BiClient`.
* Add interface `BiClient::is_initialized()`.

### Version 0.1.2

* Add `BiFluentdConsumer` to post data to Fluentd.
* Change type of `install_ts` to `integer`.
* Change default value for `collection` from `null` to `{}`.
