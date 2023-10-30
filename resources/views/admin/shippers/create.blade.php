@extends('layouts.app')

@section('content')


<div class="container">

    @if ($errors->any())
    <div class="alert alert-danger">
        There were some problems with your input.<br><br>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="row justify-content-md-center">
        <form action="{{route('store-shipper')}}" method="POST">
            @csrf

            <div class="form-group row  mt-5 mx-5">
                <label for="name" class="col-sm-2 col-form-label">Name</label>
                <div class="col-sm-5">
                    <input type="text" name="name" required class="form-control" id="name" placeholder="Name">
                </div>
            </div>
            <div class="form-group row mt-2 mx-5">
                <label for="method" class="col-sm-2 col-form-label">Shipping Method</label>
                <div class="col-sm-5">
                    <input type="text" name="method_name" required class="form-control" id="method" placeholder="Shippping method">
                </div>
            </div>

            <div class="form-group row mt-2 mx-5">
                <div class="col-sm-10">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection