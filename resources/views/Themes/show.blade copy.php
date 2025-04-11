 @extends('layouts.theme')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Themes Block {{ $theme->title }}</h4>
        <div class="d-flex">
            <a href="{{ route('themes.index') }}" class="btn btn-secondary mx-2">Back to Themes</a>
            <!-- <a href="{{ route('themeblocks.create', $theme->id) }}" class="btn btn-primary mx-2">Create Theme blocks</a> -->
        </div>
    </div>
    <div class="card-body">
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>title</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($theme->blocks as $block)
                <tr>
                    <td>{{ $block->id }}</td>
                    
                    <td>{{ $block->title }}</td>
                    

                    
                    <td>{{ $block->description }}</td>
                    <td>
                        <a href="{{ route('theme.blocks.edit', $block->id) }}" class="btn btn-warning btn-sm">edit</a>
                        <form action="{{ route('theme.blocks.destroy', $block->id) }}" method="POST" style="display:inline;">
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

<div class="mt-5"></div>





<div class="card-body ">
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>create sub Themes Block {{ $theme->title }}</h4>
        <div class="d-flex">
           
            <a href="{{ route('sub_theme.create', $theme->id) }}" class="btn btn-primary mx-2">Sub Theme </a>
        </div>
    </div>
    <table class="table mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Actions</th>
                    
                </tr>
            </thead>
            <tbody>
                @if($subthemes)
                    @foreach($subthemes as $subtheme)
                    <tr>
                        <td>{{ $subtheme->id }}</td>
                        <!-- <td>{{ $subtheme->title }}</td> -->
                        <td><a href="{{ route('subthemes.show', $subtheme->id) }}">{{ $subtheme->title }} </a></td>
                        <td>{{ $subtheme->description }}</td>
                        <td>
                        <a href="{{ route('theme.blocks.edit', $block->id) }}" class="btn btn-warning btn-sm">edit</a>
                        <form action="{{ route('theme.blocks.destroy', $block->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                        </td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection 