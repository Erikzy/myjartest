<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ForbiddenPathException extends Exception {

    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, 403, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}

class InvalidDataException extends Exception {

    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, 400, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}

class InvalidPathException extends Exception {

    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, 404, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}
