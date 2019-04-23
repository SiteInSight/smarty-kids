<?php
/**
 * Класс для работы с Bitrix24 API
 * @author: ilya@iex.su <Ilya P>
 * @version: 180313
 */

if (!function_exists('add_log')) {
    function add_log($msg) {
        //echo $msg;
    }
}

class Bitrix24API {

    private $_webhook;

    public function __construct($webhook) {
        $this->_webhook = $webhook;
    }

    /* Realised Bitrix24 API methods */

    public function getUserList(array $params = array()): array {
        $default = array(
            'order' => 'ASC'
        );
        $params = array_merge($default, $params);

        $r = $this->do_method('user.get', $params);
        return $r['result'];
    }

    public function getDepartmentList(array $params = array()): array {
        $default = array(
            'order' => 'ASC',
        );
        $params = array_merge($default, $params);

        $r = $this->do_method('department.get', $params);
        return $r['result'];
    }

    public function getTimemanStatus(int $User_ID) {
        $r = $this->do_method('timeman.status',
            array(
                'USER_ID' => $User_ID
            )
        );
        return $r['result'];
    }


    public function addCrmLivefeedmessage($title, $msg, $entity_id, $entity_type = 1) {
        $r = $this->do_method('crm.livefeedmessage.add',
            array(
                'fields' => array(
                    'POST_TITLE' => $title,
                    'MESSAGE' => $msg,
                    'ENTITYTYPEID' => $entity_type, // 1 - лид; 2 - сделка; 3 - контакт; 4 - компания.
                    'ENTITYID' => $entity_id,
                )
            )
        );
        return $r;
    }

    public function addCrmLead($title, $name, $phone = '', $email = '', array $additional_userfields = array(), $user_id = 1) {
        $params = array(
            'fields' => array(
                'TITLE' => $title,
                'NAME' => $name,
                'STATUS_ID' => 'NEW',
                'OPENED' => 'Y',
                'ASSIGNED_BY_ID' => $user_id,
            )
        );

        if (!empty($phone)) {
            $params['fields']['PHONE'] = array(array('VALUE' => $phone, 'VALUE_TYPE' => 'WORK'));
        }

        if (!empty($email)) {
            $params['fields']['EMAIL'] = array(array('VALUE' => $email, 'VALUE_TYPE' => 'WORK'));
        }

        foreach ($additional_userfields as $k => $v) {
            $params['fields'][$k] = $v;
        }
        return $this->do_method('crm.lead.add', $params);
    }

    public function getCrmLead(int $id) {
        $r = $this->do_method('crm.lead.get', array('id' => $id));
        return $r['result'];
    }

    public function getCrmContact(int $id) {
        $r = $this->do_method('crm.contact.get', array('id' => $id));
        return $r['result'];
    }

    public function getCrmDealList(array $params = array()) {
        $default = array(
            'order' => array(
                'DATE_CREATE' => 'ASC'
            ),
            'filter' => array(
                '>=DATE_CREATE' => $this->format_date(strtotime('-10 days')),
                '<DATE_CREATE' => $this->format_date(strtotime('today')),
            ),
            'select' => ['*', 'UF_*'],
            'start' => 0,
        );
        $params = array_merge($default, $params);

        return $this->do_method('crm.deal.list', $params, 'post');
    }

    public function getCrmDeal(int $id) {
        $r = $this->do_method('crm.deal.get', array('id' => $id));
        return $r['result'];
    }

    public function getCrmDealCategoryList(array $params = array()) {
        $default = array(
            'order' => array(
                'SORT' => 'ASC'
            ),
            'filter' => array(
                'IS_LOCKED' => 'N',
            ),
            'select' => ['*'],
        );
        $params = array_merge($default, $params);

        return $this->do_method('crm.dealcategory.list', $params, 'post');
    }

    /* Bitrix24 API interaction below */

    private function format_date($unix) {
        return date('d.m.Y H:i:s', $unix);
    }

    private function do_method($method, array $fields = array(), $http = 'get', $i = 0) {
        try {
            if (strtolower($http) === 'get') {
                $r = $this->http_get($this->_webhook . $method, $fields);
            } else {
                $r = $this->http_post($this->_webhook . $method, $fields);
            }

            if ($r === false)
                throw new RuntimeException('[' . __CLASS__ . '] Error: bad HTTP request');

            $r = json_decode($r, true);

            if (isset($r['error']) && !empty($r['error'])) {
                if ($r['error_description'] === 'Too many requests' && $i < 2) {
                    add_log('[LOG] Bitrix24 ' . $method . ' sleep for 3 seconds' . PHP_EOL);
                    sleep(3);
                    return $this->do_method($method, $fields, $http, $i + 1);
                }
                throw new RuntimeException('[' . __CLASS__ . '] Error "' . $method . '": ' . $r['error_description']);
            }

            return $r;

        } catch (Exception $e) {
            add_log($e->getMessage() . PHP_EOL);
            http_response_code(500);
            return false;
        }
    }

    private function http_post($url, $fields) {
        $curl = curl_init();
        /** @noinspection CurlSslServerSpoofingInspection */
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HEADER => 0,
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => array('Accept: application/json'),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => http_build_query($fields)
        ));
        return curl_exec($curl);
    }

    private function http_get($url, array $fields = array()) {
        $curl = curl_init();
        /** @noinspection CurlSslServerSpoofingInspection */
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HEADER => 0,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => array('Accept: application/json'),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url . '?' . http_build_query($fields),
        ));

        return curl_exec($curl);
    }

}