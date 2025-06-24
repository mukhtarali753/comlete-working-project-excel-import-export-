

 @extends('layouts.theme')

@section('content')
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Block title: {{ $block->title }}</h4>
            <a href="{{ route('themes.show', $block->theme->id) }}" class="btn btn-secondary">Back to Theme</a>
        </div>

        <div class="card-body">
           <p> {{ $block->description }}</p>
           
            <div>{!! $block->body !!}</div>
        </div>
    </div>
@endsection

