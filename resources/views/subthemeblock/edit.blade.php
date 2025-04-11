<!-- @extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>Edit Sub-Theme Block</h4>
    </div>
    <div class="card-body">
        
        <form action="{{ route('subthemeblocks.update', $subThemeBlock->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="{{ $subThemeBlock->title }}" required>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control">{{ $subThemeBlock->description }}</textarea>
            </div>
            <button type="submit" class="btn btn-success">Update</button>
        </form>
    </div>
</div>
@endsection -->