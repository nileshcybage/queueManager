@extends('layouts.app')
<style>
    .grey-bg {
        background-color: #F5F7FA
    }
</style>
@section('content')

<div class="container">

    <section id="stats-subtitle">


        <div class="row">
            <div class="col-xl-4 col-md-12">
                <div class="card overflow-hidden">
                    <div class="card-content">
                        <div class="card-body cleartfix">
                            <div class="media align-items-stretch">
                                <div class="align-self-center">
                                    <i class="icon-pencil primary font-large-2 mr-2"></i>
                                </div>
                                <div class="media-body">
                                    <h4>Total Users</h4>
                                    <span></span>
                                </div>
                                <div class="align-self-center">
                                    <h1>{{$users}}</h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body cleartfix">
                            <div class="media align-items-stretch">
                                <div class="align-self-center">
                                    <i class="icon-speech warning font-large-2 mr-2"></i>
                                </div>
                                <div class="media-body">
                                    <h4>Total Shippers</h4>
                                </div>
                                <div class="align-self-center">
                                    <h1>{{$shippers}}</h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body cleartfix">
                            <div class="media align-items-stretch">
                                <div class="align-self-center">
                                    <i class="icon-speech warning font-large-2 mr-2"></i>
                                </div>
                                <div class="media-body">
                                    <h4>Shipment Progress</h4>
                                </div>
                                <div class="align-self-center">
                                    <h1>{{$shipmentProgress}}</h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="row justify-content-md-center mt-5">

            <div class="col col-lg-12">
                <div class="card overflow-hidden">
                    <div class="card-content">
                        <div class="card-body cleartfix">

                            <table class="table table-bordered" id="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>User</th>
                                        <th>Shipper Name</th>
                                        <th>Tracking Number</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $item)
                                    <tr class="item{{$item->id}}">
                                        <td>{{$item->id}}</td>
                                        <td>{{ ucwords($item->user->name) }}</td>
                                        <td>{{ ucwords($item->shipper->name) }}</td>
                                        <td>{{$item->tracking_number}}</td>
                                        <td><a class="btn btn-danger btn-sm" href="{{ route('delete-entry',['tracking_queues',$item->id]) }}">Delete</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>


</div>
</section>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#table').DataTable();
    });
</script>
@endsection
