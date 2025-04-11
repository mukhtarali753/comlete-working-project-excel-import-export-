<!-- @extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Sub Themes Block for: {{ $subTheme->title }}</h4>
        <div class="d-flex">
            <a href="{{ route('themes.index') }}" class="btn btn-secondary mx-2">Back to Themes</a>
            <a href="{{ route('subthemeblocks.create', $subTheme->id) }}" class="btn btn-primary mx-2">Create Sub Theme blocks</a>
        </div> 
    </div>
    <div class="card-body">
        

       
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
                @foreach($subTheme->blocks as $blocks)
                <tr>
                    <td>{{$blocks->id }}</td>
                    <td>{{ $blocks->title }}</td>
                    <td>{{ $blocks->description }}</td>
                    <td>
                        
                        <a href="{{ route('subthemeblocks.edit', $blocks->id) }}" class="btn btn-warning btn-sm">edit</a>
                        <form action="{{ route('subthemeblocks.destroy', $blocks->id) }}" method="POST" style="display:inline;">
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
@endsection -->