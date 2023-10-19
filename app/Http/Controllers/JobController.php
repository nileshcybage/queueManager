<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrackingQueue;
use App\Jobs\trackingStatus;
use App\Models\ShipmentProgress;
use Exception;

class JobController extends Controller
{
    //
    //invoke the queue run
    public function runQueue(){
        TrackingQueue::all();
        $trackingData =  TrackingQueue::all();
        foreach($trackingData as $value){
            //get the shipper credentials for request shipper response

            //send data to shipper
           $response =  dispatch(new trackingStatus($value->tracking_number,'SOAP'));
            try {
                $shipmentProgressObj = new ShipmentProgress();
                $shipmentProgressObj->user_id = $value->user_id;
                $shipmentProgressObj->shipper_id = $value->shipper_id;
                $shipmentProgressObj->tracking_number = $value->tracking_number;
                $shipmentProgressObj->status = 'Pending';
                $shipmentProgressObj->schedule_delivery_date = '0000:00:00 00:00:00';
                $shipmentProgressObj->delivery_date = '0000:00:00 00:00:00';
                $shipmentProgressObj->create_datetime = '0000:00:00 00:00:00';
                $shipmentProgressObj->save();
                TrackingQueue::where('id',$value->id)->delete();
            }
            catch(Exception $e){
                dd($e->getMessage());
            }




        }
    }
}
