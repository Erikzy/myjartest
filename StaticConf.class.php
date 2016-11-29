<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class StaticConf{
    public static $SITE_URL = "http://myjar.blackbutterfly.ee/";
    
    public static $ENCRYPTION_KEY = "Ei924f9oVuf3Hi34C27U58EW6D6D7R5f";
    
    public static $DBCONF = array(
        "host" => "localhost",
        "port" => "3306",
        "dbname" => "myjar",
        "user" => "myjar",
        "pass" => "MyJ@rT3st"  
    );
    public static $DIRS =  array(
       "lib"      => __DIR__."/lib/",
       "services" => __DIR__."/services/",
       "model" => __DIR__."/model/",
    );

}
