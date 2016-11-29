<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Validator {

    private static $locale = 'GB';
    
    public static function validateEmail($email) {
        preg_match("/^(?=.{1,254}$)[\p{L}0-9.!#$%&'*+\/=?^_`{|}~-]{1,64}@{1}[a-zA-Z0-9]{1}[a-zA-Z0-9-]{1,62}(?:\.(?:[a-zA-Z0-9-]{2,63}))+/u", $email, $matches);
        if (sizeof($matches) < 1) {
           throw new InvalidDataException("Invalid email");
        } 
    }

    public static function validatePhoneNumber($number, $locale) {
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
            $numberProto = $phoneUtil->parse($number, $locale);
        } catch (\libphonenumber\NumberParseException $e) {
            throw new InvalidDataException($e->getMessage());
        }
        return $phoneUtil->isValidNumber($numberProto);
    }

    public static function validatePostArray($postArray){
        $dataArray = json_decode($postArray,true);
        if($dataArray == null){
            throw new InvalidDataException("Malformed or empty JSON");
        }
        
        if (sizeof($dataArray) < 10) {
            $valid = false;
            throw new InvalidDataException("Insufficient customer data ".sizeof($dataArray));
        }
        if(!isset($dataArray['email'])){
            $valid = false;
            throw new InvalidDataException("Missing email parameter");
        }else{
            self::validateEmail($dataArray['email']);
        }
        
        if(!isset($dataArray['phone'])){
            $valid = false;
            throw new InvalidDataException("Missing phone parameter");
        }else{
            self::validatePhoneNumber($dataArray['phone'], self::$locale);
        }
        return $dataArray;
    }
    
    public static function validateFilterArray($filterArray){
        $keywords = array("sort","page");
        
    }
    
}
