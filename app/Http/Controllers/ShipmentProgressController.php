<?php

namespace App\Http\Controllers;

use App\Models\ShipmentProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShipmentProgressController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       $data = ShipmentProgress::where('user_id', Auth::user()->id)->with("shipper","user")->get();
       return view("admin/shipment-progress/index", compact("data"));
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
     * @param  \App\Models\ShipmentProgress  $shipmentProgress
     * @return \Illuminate\Http\Response
     */
    public function show(ShipmentProgress $shipmentProgress)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ShipmentProgress  $shipmentProgress
     * @return \Illuminate\Http\Response
     */
    public function edit(ShipmentProgress $shipmentProgress)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ShipmentProgress  $shipmentProgress
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ShipmentProgress $shipmentProgress)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ShipmentProgress  $shipmentProgress
     * @return \Illuminate\Http\Response
     */
    public function destroy(ShipmentProgress $shipmentProgress)
    {
        //
    }

    public function getTracking($shipper,$trackingNumber){
        //
        $ServiceModel = '\\App\\Services\\'. $shipper;
        $shipperServiceContainer = new $ServiceModel;
        return $shipperServiceContainer->getTracking($trackingNumber);

    }
}
