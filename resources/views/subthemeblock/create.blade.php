<!-- @extends('layouts.app')
@section('content')

<div class="card">
    <div class="card-header"><h4>sub theme block</h4></div>

    <div class="card-body">
<form action="{{ route('subthemeblocks.store') }}" method="POST">
    @csrf
    <div class="mb-3">
    <input type="hidden" name="sub_theme_id" value="{{ $subThemeId }}">

    <div>
        <label for="title">Title</label>
        <input type="text" name="title" required>
    </div>

    <div>
        <label for="description">description</label>
        <textarea name="description"></textarea>
    </div>
    </div>
    

    <button type="submit" >Create Sub theme Block</button>
</form>
    </div>
@endsection








 


 -->
