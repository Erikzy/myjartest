<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Request {

    public $resourceType;
    public $resourceId = null;
    public $dataParams;
    public $method;

    public function __construct($serverRequest) {
        $this->method = $serverRequest['REQUEST_METHOD'];
        $this->dataParams = $this->extractDataParams();
        $this->parseRoute($serverRequest['REQUEST_URI']);
    }

    public function parseRoute($requestUri) {
        $uriParts = explode("?", $requestUri);
        $pathVars = explode("/", $uriParts[0]);
        if (strlen($pathVars[1]) == 0) {
            throw new InvalidPathException("Empty path");
        } else {
            $this->resourceType = $pathVars[1];
        }
        if (isset($pathVars[2]) && strlen($pathVars[2]) > 0) {
            if (is_numeric($pathVars[2])) {
                $this->resourceId = $pathVars[2];
            } else {
                throw new InvalidDataException("Resource identifier must be numeric");
            }
        }
    }

    public function extractDataParams() {
        switch ($this->method) {
            case "GET":
                $data = $_GET;
                break;

            case "PUT":
            case "POST":
                $jsondata = file_get_contents("php://input");
                $data = Validator::validatePostArray($jsondata);
                break;

            case "DELETE":
                throw new ForbiddenPathException("Delete action is not allowed");
                break;

            default:
                throw new ForbiddenPathException("Unsupported method: " . $requestMethod);
        }
        return $data;
    }

}
