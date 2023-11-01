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

            <div class="mb-3">
            </div>

            <table class="table table-striped table-bordered  mt-3" style="width:100%" id="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client Id</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                    <tr class="item{{$item->id}}">
                        <td>{{$item->id}}</td>
                        <td>{{$item->client_id}}</td>
                        <td>{{ucfirst($item->name)}}</td>
                        <td>{{$item->email}}</td>
                        <td>{{ $item->is_admin == "true" ? 'Admin' : 'User' }}</td>

                        <td><a class="btn btn-danger btn-sm" href="{{ route('delete-entry',['users',$item->id]) }}">Delete</a>
                            </button>
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
