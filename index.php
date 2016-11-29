<?php

require_once 'vendor/autoload.php';
require_once "StaticConf.class.php";

$DIRS = StaticConf::$DIRS;

foreach (glob($DIRS["lib"] . "/*.class.php") as $class) {
    require_once $class;
}


try {
    $custDb = new CustomerDatabase();
    $request = new Request($_SERVER);
    if($request->resourceType == "customer"){
        if($request->method == "POST"){
           $newCust = $custDb->addCustomer($request->dataParams);
           return new Response(201, $newCust, StaticConf::$SITE_URL.'customer/'.$newCust->getId());
        }else{
            if($request->resourceId == null){
               $list = $custDb->getCustomerList($request->dataParams);
               return new Response(200, $list);
            }else{
               $cust = $custDb->getSingleCustomer($request->resourceId);
               return new Response(200,$cust);
            }
        }
        
    }else{
        throw new InvalidPathException(" Resource type [".$request->resourceType."] not found.");
    }
} catch (Exception $e) {
    return new Response($e->getCode(), array("error" => $e->getMessage()));
}
?>
