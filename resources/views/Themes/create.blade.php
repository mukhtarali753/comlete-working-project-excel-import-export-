@extends('layouts.theme')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>Create New Theme</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('themes.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control"></textarea>
            </div>
            
            <input type="hidden" name="parent_id" value="{{ $parent_id }}">
            {{-- <input type="hidden" name="theme_id" value="{{ $theme->id }}"> --}}

            <button type="submit" class="btn btn-success">Create Theme </button>
        </form>
    </div>
</div>

@endsection