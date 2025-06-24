@extends('layouts.theme')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center ">
            <h4>@if($id != 0)Sub @endif()Themes</h4>


            @if ($id != 0)
                <a href="{{ route('themes.index') }}" class="btn btn-secondary mx-2">Back to Themes</a>
            @endif
            {{-- @else --}}
            <a href="{{ route('themes.create', $id) }}" class="btn btn-primary mx-2">Add @if($id != 0)Sub @endif()Theme</a>
            {{-- @endif --}}




        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        

                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($themes as $theme)
                        <tr>
                            <td>{{ $theme->id }}</td>
                            <td>
                                {{-- @if ($id != 0)
                                {{ $theme->title }}
                                @else --}}
                                <a href="{{ route('themes.show', $theme->id) }}">{{ $theme->title }}</a>

                                {{-- @endif --}}

                            </td>
                            <td>{{ $theme->description }}</td>
                            <td>


                                @if(empty($theme->parent_id))
                                    <a href="{{ route('website.show', $theme->id) }}" class="btn btn-success btn-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                                        </svg>
                                    </a>
                                @endif



                                <a href="{{ route('themes.edit', $theme->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('themes.destroy', $theme->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>

                                
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Display blocks for this theme if any -->
            {{-- @if($blocks->isNotEmpty()) --}}
            {{-- @else
            <p>No blocks found for this theme.</p>
            @endif --}}
        </div>
    </div>
    @isset($blocks)

        <div class="mt-4 card">


            <div class="card-header d-flex justify-content-between align-items-center ">
                <h5>Blocks </h5>
                <a href="{{ route('theme_blocks.create', $id) }}" class="btn btn-primary mx-2">Add Theme Block</a>

            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Block ID</th>
                            <th>Title</th>
                            <th>Description</th>
                            {{-- <th>Body</th> --}}
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($blocks as $block)
                            <tr>
                                <td>{{ $block->id }}</td>
                                <td>{{ $block->title }}</td>
                                <td>{{ $block->description }}</td>
                                {{-- <td>{!! $block->body !!}</td> --}}

                                <td>

                                    
                                    
                                    
                                    {{-- <a href="{{ route('theme_blocks.single', $block->id) }}" class="btn btn-success">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                                        </svg>
                                    </a> --}}

                                    <a href="{{ route('theme_blocks.show', $block->id) }}" class="btn btn-success">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                                        </svg>
                                    </a>
                                    
                                    

                                    <a href="{{route('theme_blocks.edit', $block->id)}}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('theme_blocks.destroy', $block->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>


                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endisset


    </tbody>
    </table>
    </div>
    </div>
@endsection