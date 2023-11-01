<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\trackingRequest;
use App\Models\Shipper;
use Illuminate\Support\Collection;
use App\Models\User;
use App\Models\TrackingQueue;
use Illuminate\Http\JsonResponse;

class TrackingRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function trackingRequest(trackingRequest $request){
            // Retrieve the validated input data...
            $requestData = $request->validated();
            $user = User::where('client_id',$requestData['client_id'])->first();
            $shipper = Shipper::where('name',strtoupper($requestData['ship_method']))->first();

            if($user) {
                if(TrackingQueue::create(['user_id' => $user->id, 'tracking_number' => $requestData['tracking_number'], 'shipper_id' => $shipper->id])) {
                    return response()->json(['success' => true,'message' => 'request submit sucessfully.!']);
                } else {
                    return response()->json(['success' => false, 'message' => 'something went wrong.']);
                }
            }else{
                return response()->json(['success' => false, 'message' => 'user not found:something went wrong.']);
            }



    }
}
