@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="row justify-content-md-center">
        <div class="col col-lg-10">

            @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
            @endif

            <table class="table table-striped table-bordered" style="width:100%" id="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User Name</th>
                        <th>Shipper Name</th>
                        <th>Tracking Number</th>
                        <th>Status</th>
                        <th>Schedule Delivery Date</th>
                        <th>Delivery Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                    <tr class="item{{$item->id}}">
                        <td>{{$item->id}}</td>
                        <td>{{ ucwords($item->user->name) }}</td>
                        <td>{{ ucwords($item->shipper->name) }}</td>
                        <td>{{$item->tracking_number}}</td>
                        <td>{{$item->status}}</td>
                        <td>{{$item->schedule_delivery_date}}</td>
                        <td>{{$item->delivery_date}}</td>
                        <td><a class="btn btn-danger btn-sm" href="{{ route('delete-entry',['shipment_progress',$item->id]) }}">Delete</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>



        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#table').DataTable();
    });
</script>
@endsection