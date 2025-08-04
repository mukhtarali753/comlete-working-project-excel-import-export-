@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Shop Details</h3>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>ID:</strong> {{ $shop->id }}</p>
                    <p><strong>Name:</strong> {{ $shop->name }}</p>
                    <p><strong>Address:</strong> {{ $shop->address }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Contact Email:</strong> {{ $shop->contact_email }}</p>
                    <p><strong>Phone:</strong> {{ $shop->phone }}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge badge-{{ $shop->is_active ? 'success' : 'danger' }}">
                            {{ $shop->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('shops.edit', $shop->id) }}" class="btn btn-primary">Edit</a>
                <a href="{{ route('shops.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection