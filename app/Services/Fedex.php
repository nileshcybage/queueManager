<?php

namespace App\Services;


class Fedex
{
    private $wsdlPath;

    public function __construct()
    {
        $this->wsdlPath =  public_path('storage/wsdl/' . Config('shippers.fedex.wsdl_v18'));
    }

    public function getTracking($trackingNumber){
        //dd($this->wsdlPath);

        try {


            $arrRequest = [];
            $arrRequest['WebAuthenticationDetail']['UserCredential']['Key'] = config('shippers.fedex.key');
            $arrRequest['WebAuthenticationDetail']['UserCredential']['Password'] = config('shippers.fedex.password');
            $arrRequest['ClientDetail']['AccountNumber'] = config('shippers.fedex.shipaccount');
            $arrRequest['ClientDetail']['MeterNumber'] = config('shippers.fedex.meter');
            $arrRequest['TransactionDetail']['CustomerTransactionId'] = '*** Track Request v4 using PHP ***';
            $arrRequest['Version']['ServiceId'] = 'trck';
            $arrRequest['Version']['Major']     ='18';
            $arrRequest['Version']['Intermediate']     ='0';
            $arrRequest['Version']['Minor']     ='0';
            $arrRequest['PackageIdentifier']['Value']     =$trackingNumber;
            $arrRequest['PackageIdentifier']['Type']     = config('shippers.fedex.type');;

           // dd($arrRequest);



            $client = new \SoapClient($this->wsdlPath, array('trace' => 1,'cache_wsdl' => WSDL_CACHE_NONE));
            $response = $client->track($arrRequest);
            dd($response);
        } catch (\SoapFault $e) {
           dd($e->getMessage());
        }
    }



}
