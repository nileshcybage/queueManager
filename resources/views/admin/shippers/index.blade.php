@extends('layouts.app')

@section('content')

<div class="container">


    <div class="row justify-content-md-center">

        <div class="col col-lg-10 ">

            <div class="mb-3">
                <a class="btn btn-sm btn-primary" href="{{ route('create-shipper') }}">Add</a>
            </div>

            @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
            @endif

            <table class="table table-striped table-bordered mt-3" style="width:100%" id="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Method Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                    <tr class="item{{$item->id}}">
                        <td>{{$item->id}}</td>
                        <td>{{$item->name}}</td>
                        <td>{{$item->method_name}}</td>
                        <td><a class="btn btn-danger btn-sm" href="{{ route('delete-entry',['shippers',$item->id]) }}">Delete</a>
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