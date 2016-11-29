<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Response {

    private $code;
    private $data = array();

    public function __construct($responseCode, $data = array(), $locationLink = null) {
        $this->code = $responseCode;
        $this->data = $data;

        header("Content-type: application/json", null, $this->code);
        if (strlen($locationLink) > 0) {
            header("Location: ".$locationLink);
        }
        echo json_encode($this->data);
    }

}
