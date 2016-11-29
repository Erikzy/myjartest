<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Customer implements JsonSerializable {

    private $id;
    private $email;
    private $hashedNumber;
    private $data = array();

    public function jsonSerialize() {
        $array = array();
        $array['id'] = $this->id;
        $array['email'] = $this->email;
        $array['phone'] = str_repeat("*", 6) . substr($this->getDecryptedPhonenumber(), -4, 4);
        foreach ($this->data as $k => $v) {
            $array[$k] = $v;
        }

        return $array;
    }

    public function __construct($id, $email) {
        $this->id = $id;
        $this->email = $email;
    }

    public function getId() {
        return $this->id;
    }

    public function addDataAttribute($name, $value) {
        $this->data[$name] = $value;
    }

    public function setHashedNumber($number) {
        $this->hashedNumber = $number;
    }

    public function getDecryptedPhonenumber() {
        $password = substr(md5($this->id), 0, 10) . substr(sha1($this->id), 0, 10) . StaticConf::$ENCRYPTION_KEY;
        return $this->decrypt($password, $this->hashedNumber);
    }

    public function encrypt($key, $number) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($number, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted . '/*/*/' . $iv);
    }

    public function decrypt($key, $data) {
        list($encrypted_data, $iv) = explode('/*/*/', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
    }

}
