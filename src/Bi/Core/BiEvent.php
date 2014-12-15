<?php namespace Bi\Core;

use Bi\Core\BiUtils;
use Bi\Exception\BiNotInitializeException;
use Bi\Exception\BiInvalidJsonFormatException;
use Bi\Exception\BiLoadMetadataFailedException;
use Bi\Exception\BiEventSerializeFailedException;
use Bi\Exception\BiMetadataMissFieldExcecption;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * The action that someone takes in the client application.
 */
class BiEvent {

    /**
     * @var string Event name.
     */
    private $_event = null;

    /**
     * @var array Basic required attributes.
     */
    private $_attributes = null;

    /**
     * @var array Extra attributes.
     */
    private $_properties = null;

    /**
     * @var array Collection of items or resources.
     */
    private $_collections = null;

    /**
     * @param string $event the event name.
     * @param string $user_id the player's game id.
     * @param string $session_id the ongoing session's id.
     * @param string $properties the event's properties in JSON format.
     * @param string $collections the event's collections in JSON format, default is null.
     * @param integer $ts UNIX timestamp for the event, default is null.
     *
     * @throws BiNotInitializeException
     */
    public function __construct($event, $user_id, $session_id, $properties, $collections = null, $ts = null) {
        if (!BiUtils::is_initialized()) {
            throw new BiNotInitializeException('BiClient has not been initialized yet, please initialize it before calling BiClient::instance().');
        }

        if (!isset($ts)) {
            // $ts = time();
            $date = new DateTime(null, new DateTimeZone('UTC'));
            $ts = $date->getTimestamp() + $date->getOffset();
        }

        $this->_event = $event;

        $this->_attributes = array(
            'app_id' => BiUtils::instance()->get_config('app_id'),
            'bi_version' => BiUtils::instance()->get_bi_version(),
            'event' => $event,
            'user_id' => $user_id,
            'session_id' => $session_id,
            'ts' => $ts
        );

        $this->_properties = $properties;

        $this->_collections = $collections;
    }

    /**
     * @return string the serialized JSON string of the event.
     */
    public function serialize() {
        $valid = $this->validate();
        if (!$valid[0]) {
            throw new BiEventSerializeFailedException('Can not serialize the event, please make sure you have passed correct parameters when constructing this event.');
        }

        if (!isset($this->_collections)) {
            $this->_collections = 'null';
        }

        return sprintf(
            '{"app_id": "%s", "bi_version": "%s", "event": "%s", "user_id": "%s", "session_id": "%s", "ts": %d, "properties": %s, "collections": %s}',
            $this->_attributes['app_id'],
            $this->_attributes['bi_version'],
            $this->_attributes['event'],
            $this->_attributes['user_id'],
            $this->_attributes['session_id'],
            $this->_attributes['ts'],
            $this->_properties,
            $this->_collections
        );
    }

    /**
     * @return array If event contains no error, return array(true); otherwise return array(false, 'err_message').
     */
    public function validate() {
        try {
            $attributes_status = $this->_validate_attributes();
            if (!$attributes_status[0]) {
                return $attributes_status;
            }

            $properties_status = $this->_validate_properties();
            if (!$properties_status[0]) {
                return $properties_status;
            }

            $collections_status = $this->_validate_collections();
            if (!$collections_status[0]) {
                return $collections_status;
            }

            return array(true);
        } catch (BiMetadataMissFieldExcecption $e) {
            return array(false, 'Missing fields in metadata. Make sure your metadata file contains the following fields: `event`, `attribute`, `property`, `collection, `item` and `resource`.');
        }
    }

    /**
     * @return array If attributes contain no error, return array(true); otherwise return array(false, 'err_message').
     *
     * @throws BiMetadataMissFieldExcecption
     */
    private function _validate_attributes() {
        $attrs = $this->_attributes;

        $event_meta = BiUtils::instance()->get_meta('event');
        $attr_meta = BiUtils::instance()->get_meta('attribute');

        if (!isset($event_meta) || !isset($attr_meta)) {
            throw new BiMetadataMissFieldExcecption('Missing fields in metadata: please check if `event` and `attribute` are in your metadata file.');
        }

        if (!array_key_exists($this->_event, $event_meta)) {
            return array(false, 'BiEvent not exists.');
        } else {
            foreach($attr_meta as $name => $meta) {
                // 1. check if is required.
                // 2. check if type is correct.
                if ($meta['required'] && !isset($attrs[$name])) {
                    return array(false, sprintf('[Attribute] Missing value: %s.', $name));
                }

                $func = 'is_'.$meta['type'];
                if (!call_user_func($func, $attrs[$name])) {
                    return array(false, sprintf('[Attribute] Type mismatch: %s.', $name));
                }
            }
        }

        return array(true);
    }

    /**
     * @return array if properties contain no error, return array(true); otherwise return array(false, 'err_message').
     *
     * @throws BiMetadataMissFieldExcecption
     */
    private function _validate_properties() {
        $props = array();

        try {
            $props = json_decode($this->_properties, true);
            if (!is_array($props)) {
                throw new BiInvalidJsonFormatException('Wrong JSON format.');
            }
        } catch (Exception $e) {
            return array(false, '[Property] Wrong JSON format.');
        }

        $event_meta = BiUtils::instance()->get_meta('event');
        $prop_meta = BiUtils::instance()->get_meta('property');

        if (!isset($event_meta) || !isset($prop_meta)) {
            throw new BiMetadataMissFieldExcecption('Missing fields in metadata: please check if `event` and `property` are in your metadata file.');
        }

        foreach($event_meta[$this->_event]['required_properties'] as $name) {
            if (!array_key_exists($name, $props)) {
                return array(false, sprintf('[Property] Missing value: %s.', $name));
            }

            $func = 'is_'.$prop_meta[$name]['type'];
            if (!call_user_func($func, $props[$name])) {
                return array(false, sprintf('[Property] Type mismatch: %s.', $name));
            }
        }

        return array(true);
    }

    /**
     * @return array if collections contain no error, return array(true); otherwise return array(false, 'err_message').
     *
     * @throws BiMetadataMissFieldExcecption
     */
    private function _validate_collections() {
        if (!isset($this->_collections)) {  // empty collections is allowed
            return array(true);
        }

        $collections = array();

        try {
            $collections = json_decode($this->_collections, true);
            if (!is_array($collections)) {
                throw new BiInvalidJsonFormatException('Wrong JSON format.');
            }
        } catch (Exception $e) {
            return array(false, '[Collection] Wrong JSON format.');
        }

        $event_meta = BiUtils::instance()->get_meta('event');
        $collection_meta = BiUtils::instance()->get_meta('collection');
        $item_meta = BiUtils::instance()->get_meta('item');
        $resource_meta = BiUtils::instance()->get_meta('resource');

        if (!isset($event_meta) || !isset($collection_meta) || !isset($item_meta) || !isset($resource_meta)) {
            throw new BiMetadataMissFieldExcecption('Missing fields in metadata: please check if `event`, `collection`, `item` and `resource` are in your metadata file.');
        }

        foreach($event_meta[$this->_event]['collections'] as $name) {
            if (array_key_exists($name, $collections)) {
                $arr = $collections[$name];
                if (!is_array($arr)) {
                    return array(false, '[Collection] Wrong JSON format.');
                }

                if ($collection_meta[$name]['type'] == 'Items') {
                    $target_type = 'Item';
                    $target_meta = $item_meta;
                } else if ($collection_meta[$name]['type'] == 'Resources') {
                    $target_type = 'Resource';
                    $target_meta = $resource_meta;
                }// else {
                //    return array(false, sprintf('[Collection] Unknown type for %s.', $name));
                //}

                foreach($arr as $element) {
                    foreach($target_meta as $name => $meta) {
                        if ($meta['required'] && !isset($element[$name])) {
                            return array(false, sprintf('[Collection] Missing value for %s: %s.', $target_type, $name));
                        }

                        $func = 'is_'.$meta['type'];
                        if (!call_user_func($func, $element[$name])) {
                            return array(false, sprintf('[Collection] Type mismatch for %s: %s.', $target_type, $name));
                        }
                    }
                }
            } // end if (array_key_exists
        } // end foreach($event_meta

        return array(true);
    }
}
