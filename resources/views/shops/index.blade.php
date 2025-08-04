@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <h3>Shop Management</h3>
                <div>
                    <a href="{{ route('shops.create') }}" class="btn btn-primary">Add Shop</a>
                    <a href="{{ route('shops.export') }}" class="btn btn-success">Export Excel</a>
                    <a href="{{ route('shops.preview') }}" class="btn btn-info">Preview Excel</a>
                </div>
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Contact Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
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
                            <td>
                                <span class="badge badge-{{ $shop->is_active ? 'success' : 'danger' }}">
                                    {{ $shop->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('shops.show', $shop->id) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('shops.edit', $shop->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                <form action="{{ route('shops.destroy', $shop->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <div>Showing {{ $shops->firstItem() }} to {{ $shops->lastItem() }} of {{ $shops->total() }} entries</div>
                <div>{{ $shops->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection