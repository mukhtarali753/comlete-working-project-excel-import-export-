@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Excel Preview</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Shop Name</th>
                            <th>Address</th>
                            <th>Contact Email</th>
                            <th>Phone</th>
                            <th>Is Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shops as $shop)
                        <tr>
                            <td>{{ $shop->id }}</td>
                            <td>{{ $shop->name }}</td>
                            <td>{{ $shop->address }}</td>
                            <td>{{ $shop->contact_email }}</td>
                            <td>{{ $shop->phone }}</td>
                            <td>{{ $shop->is_active ? 'Yes' : 'No' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <a href="{{ route('shops.export') }}" class="btn btn-success">Download Excel</a>
                <a href="{{ route('shops.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection