<?php

namespace App\Services;

class Fedex
{
    private $wsdlPath;

    public function __construct()
    {
        $this->wsdlPath =  storage_path('public/wsdl/' . Config('shippers.fedex.wsdl_v18'));
    }

    public function getTracking($trackingNumber){
        dd($this->wsdlPath);
    }



}
