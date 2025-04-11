@extends('layouts.theme')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>Create Theme Block</h4>
    </div>
    <div class="card-body">
        
        <form action="{{ route('theme_blocks.store', $themeId) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-success">Create Theme Block</button>
        </form>
    </div>
</div>


@endsection