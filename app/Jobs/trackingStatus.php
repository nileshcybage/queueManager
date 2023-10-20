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
use App\Http\Controllers\JobController;

class trackingStatus implements ShouldQueue
{
   // use Dispatchable, InteractsWithQueue, SerializesModels;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $wsdlPath;
    private $trackingNumber;
    private $serviceType;
    private $controllerObject;
    private $response;


    public function __construct($trackingNumber,$serviceType = 'REST')
    {
        $this->wsdlPath =  public_path('storage/wsdl/' . Config('shippers.fedex.wsdl_v18'));
        $this->trackingNumber = $trackingNumber;
        $this->serviceType = $serviceType;
        $this->controllerObject = new JobController();
        $this->response = '';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

       ($this->serviceType == 'SOAP') ?  $this->SoapService() : $this->restService();
        //dd($this->response);
        $this->controllerObject->saveTrackingStatus($this->trackingNumber,'in');
    }


    public function SoapService(){

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
            $this->response  = $client->track($arrRequest);



        } catch (\SoapFault $e) {
            print $e->getMessage() ."\n";
        }

    }

    public function restService(){
        dd('33');exit;
    }


}
