<?php namespace Bi\Consumer;
/**
 * Use Funplus global front-end as data consumer.
 * Refer to [http://wiki.ifunplus.cn/pages/viewpage.action?pageId=35228294]
 *
 */

use Bi\Consumer\BiAbstractConsumer;
use Exception;
use HTTPRequest;

class BiFunplusGlobalFrontEndConsumer extends BiAbstractConsumer {

    public function __construct($options = array()) {
        parent::__construct($options);
        $this->_option_keys = array('endpoint', 'project', 'group', 'archive', 'es');
    }

    /**
     * @param array $batch array of messages to consume.
     *
     * @return boolean success or fail.
     */
    public function persist($batch) {
        if (count($batch) == 0) {
            return true;
        }

        $project = $this->_options['project'];
        $group = $this->_options['group'];
        $archive = $this->_options['archive'];
        $es = $this->_options['es'];

        if (!isset($archive) || ($archive !== 0 || $archive !== 1)) {
            $archive = 1;
        }

        if (!isset($es) || ($es !== 0 || $es !== 1)) {
            $es = 0;
        }

        $url = $this->_options['endpoint'] . '?' . http_build_query(array(
            'project' => $project,
            'group' => $group,
            'archive' => $archive,
            'es' => $es
        ));

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, gzencode(join("\n", $batch)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if (isset($info['http_code']) && $info['http_code'] === 200) {
            return true;
        } else {
            return false;
        }
    }
}
