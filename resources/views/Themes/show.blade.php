 @extends('layouts.theme')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Themes Block {{ $theme->title }}</h4>
        <div class="d-flex">
            <a href="{{ route('themes.index') }}" class="btn btn-secondary mx-2">Back to Themes</a>
            <a href="{{ route('themes.create', $theme->id) }}" class="btn btn-primary mx-2">Sub Theme </a>
        </div>
    </div>
    <div class="card-body ">
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
                        <td><a href="{{ route('themes.show', $subtheme->id) }}">{{ $subtheme->title }} </a></td>
                        <td>{{ $subtheme->description }}</td>
                        <td>
                        </td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
</div>
</div>

<div class="mt-5"></div>






@endsection 