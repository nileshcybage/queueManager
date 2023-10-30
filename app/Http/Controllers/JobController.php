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
        //pull out data
        TrackingQueue::all();
        $trackingData =  TrackingQueue::all();
        if($trackingData->isEmpty()){
            print "\n no data available in queue \n";
            return 0;
        }

        foreach($trackingData as $value){
            print "\n Tracking ". $value->tracking_number. " pulled in to shipment progress table";
            //pull tracking data into queueManager stream table i.e. shipment progress
            //get the shipper credentials for request shipper response
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
                TrackingQueue::where('id', $value->id)->delete();
            }
            catch(Exception $e){
                print $e->getMessage() ."\n";
            }
        }
    }

    protected function sendRequestJobToShipper($tracking_number,$serviceType){
        return dispatch(new trackingStatus($tracking_number,$serviceType));
       // return trackingStatus::dispatchSync($tracking_number,$serviceType);
    }

    public function publishTrackingToJobTable(){
        try{
            $shipmentProgressObj = ShipmentProgress::where('status','<>', 'Delivered')->get();
        }
        catch(Exception $e){
            print $e->getMessage() ."\n";
        }

        if($shipmentProgressObj->isEmpty()){
            print "\n no records available to send to job table";
            return 0;
        }


        foreach($shipmentProgressObj as $value){
            try {
                print "\n Tracking ".$value->tracking_number. " sending for queue processing...";
                return $this->sendRequestJobToShipper($value->tracking_number, 'SOAP');
            }
            catch(Exception $e){
                print $e->getMessage() ."\n";
                return 0;
            }
        }
    }

    public function saveTrackingStatus($trackingNumber,$trackingStatus){
        try{
            $shipmentProgressObj = ShipmentProgress::where('tracking_number',$trackingNumber)->first();
        }
        catch(Exception $e){
            print "JobControllerError :" .$e->getMessage() ."\n";
            return 0;
        }

        if(empty($shipmentProgressObj)){
            print "\n no records available to update status";
            return 0;
        }

        try {
            print "\n tracking : ".$shipmentProgressObj->tracking_number. " update status : " . $trackingStatus . "old status : ". $shipmentProgressObj->tracking_number;
            ShipmentProgress::where('tracking_number', $shipmentProgressObj->tracking_number)->update([
                'status' => $trackingStatus,
                'schedule_delivery_date' => '',
                'delivery_date' => '',
                'create_datetime' => ''
            ]);

        }
        catch(Exception $e){
            print "UpdateCallError : " . $e->getMessage() ."\n";
            return 0;
        }

    }
}
