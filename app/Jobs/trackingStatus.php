<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

use App\Services\Fedex;

class trackingStatus implements ShouldQueue,ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $wsdlPath;
    private $trackingNumber;
    private $serviceType;

    public function __construct($trackingNumber,$serviceType = 'REST')
    {
        $this->wsdlPath =  public_path('storage/wsdl/' . Config('shippers.fedex.wsdl_v18'));
        $this->trackingNumber = $trackingNumber;
        $this->serviceType = $serviceType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        return ($this->serviceType == 'SOAP') ?  $this->SoapService() : $this->restService();
    }


    private function SoapService(){

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
            $arrRequest['PackageIdentifier']['Value']     =$this->trackingNumber;
            $arrRequest['PackageIdentifier']['Type']     = config('shippers.fedex.type');
            $client = new \SoapClient($this->wsdlPath, array('trace' => 1,'cache_wsdl' => WSDL_CACHE_NONE));
            $response = $client->track($arrRequest);
            dd($response);
            dd('ghh');
        } catch (\SoapFault $e) {
           dd($e->getMessage());
        }

    }

    private function restService(){
        dd('33');exit;
    }
}
