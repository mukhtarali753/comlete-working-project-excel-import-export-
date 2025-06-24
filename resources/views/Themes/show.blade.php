@extends('layouts.theme')

@section('hideNavigation', true)

@section('content')
    <div class="custom-design">
        <div class="blocks">
            @foreach ($theme->blocks as $block)
                <div class="block">
                    {!! $block->body !!}
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-4"></div>
   
@endsection




