 @extends('layouts.theme')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center "  >
        <h4>@if($id != 0)Sub @endif()Themes</h4>
        
        @if ($id != 0)
        <a href="{{ route('themes.index') }}" class="btn btn-secondary mx-2">Back to Themes</a>     
        @endif   
        {{-- @else  --}}
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
                        <a href="{{ route('themes.edit', $theme->id) }}" class="btn btn-warning btn-sm">edit</a>
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

    
    <div class="card-header d-flex justify-content-between align-items-center "  >
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($blocks as $block)
                <tr>
                    <td>{{ $block->id }}</td>
                    <td>{{ $block->title }}</td>
                    <td>{{ $block->description }}</td>

                    <td>
                        <a href="{{ route('theme_blocks.edit', $block->id) }}" class="btn btn-warning btn-sm">edit</a>
                        <form action="{{ route('theme_blocks.destroy', $block->id) }}" method="POST" style="display:inline;">
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








