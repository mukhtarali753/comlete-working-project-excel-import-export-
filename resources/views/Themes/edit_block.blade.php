@extends('layouts.theme')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>Edit Sub-Theme Block</h4>
    </div>
    <div class="card-body">
        
        <form action="{{ route('theme_blocks.update', $block->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="{{ $block->title }}" required>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control">{{ $block->description }}</textarea>
            </div>
            <button type="submit" class="btn btn-success">Update</button>
        </form>
    </div>
</div>
@endsection