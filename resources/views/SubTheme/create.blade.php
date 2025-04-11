 @extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>Create New Sub Theme</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('subthemes.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Title</label>
                <input type="hidden" name="theme_id" value="{{ $themeId }}" >
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-success">Create new sub theme block</button>
        </form>
    </div>
</div>
@endsection



 
