{{-- @extends('layout.board')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Edit Stage</h2>
    </div>
    <div class="card-body">
        
       

        <form action="{{ route('layout.update',$stage->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="lead_name" class="form-label">Lead Name:</label>
                <input type="text" name="lead_name" class="form-control" value="{{ $stage->lead->name  }}">
              

            </div>

            


            <div class="mb-3">
                <label>Stage Name:</label>
                
                <input type="text" name="name" class="form-control" id="name" value="{{ $stage->name }}">




            </div>

            <div class="mb-3">
                <label>Description:</label>
                <input type="text" name="description" class="form-control" value="{{ $stage->description }}" required>
            </div>

            <div class="mb-3">
                <label>Email:</label>
                <input type="email" name="email" class="form-control" value="{{ $stage->email }}" required>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</div>
@endsection --}}
