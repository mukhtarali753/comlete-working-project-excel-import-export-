
@extends('layout.board') 

@section('content')
<div class="card">
{{-- <div class="container mt-4"> --}}
    <div class="card-header" style="padding: 10px">
    <h2>Edit Lead & Stages</h2></div>

    <form action="{{ route('lead.update', $lead->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Lead Name:</label>
            <input type="text" name="name" class="form-control" value="{{ $lead->name }}" required>
        </div>
         
        <h4>Stages</h4>
        <div class="mb-4"></div>
        @foreach($lead->stages as $index => $stage)
            <div class="border p-3 mb-2">
                <input type="hidden" name="stage_ids[]" value="{{ $stage->id }}">
                
                <div class="mb-2">
                    <label>Stage Name:</label>
                    <input type="text" name="stage_name[]" class="form-control" value="{{ $stage->name }}" required>
                </div>

                <div class="mb-2">
                    <label>Description:</label>
                    <input type="text" name="description[]" class="form-control" value="{{ $stage->description }}" required>
                </div>

                <div class="mb-2">
                    <label>Email:</label>
                    <input type="email" name="emails[]" class="form-control" value="{{ $stage->email }}" required>
                </div>
            </div>
        @endforeach
        

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
{{-- </div> --}}
</div>
@endsection
