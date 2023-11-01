<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ShipmentProgress;
use App\Models\User;
use App\Models\Shipper;
use App\Models\TrackingQueue;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function dashboard()
    {



        $shippers = Shipper::count();
        $users = User::count();
        $shipmentProgress = ShipmentProgress::count();

        $data = TrackingQueue::with('user','shipper')->get();

        return view('dashboard', compact('shippers', 'users', 'shipmentProgress', 'data'));
    }

    public function delete($table, $id)
    {

        if (DB::table($table)->delete($id)) {
            return redirect()->back()
                ->with('success', 'Record deleted successfully.');
        }
    }



}
