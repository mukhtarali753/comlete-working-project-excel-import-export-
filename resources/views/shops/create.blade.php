@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>{{ isset($shop) ? 'Edit' : 'Create' }} Shop</h3>
        </div>

        <div class="card-body">
            <form action="{{ isset($shop) ? route('shops.update', $shop->id) : route('shops.store') }}" method="POST">
                @csrf
                @if(isset($shop))
                    @method('PUT')
                @endif

                <div class="form-group">
                    <label>Shop Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $shop->name ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label>Address *</label>
                    <textarea name="address" class="form-control" required>{{ old('address', $shop->address ?? '') }}</textarea>
                </div>

                <div class="form-group">
                    <label>Contact Email *</label>
                    <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $shop->contact_email ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label>Phone *</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $shop->phone ?? '') }}" required>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" 
                            {{ old('is_active', isset($shop) ? $shop->is_active : true) ? 'checked' : '' }}>
                        <label for="is_active" class="form-check-label">Active</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('shops.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection