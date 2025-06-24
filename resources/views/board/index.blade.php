@extends('layouts.theme')
@section('title', 'Add board')
@section('content')
<div class="container mt-3">
     <div class="card mb-4">
        <div class="card-header bg-light text-black d-flex justify-content-between align-items-center">
            <h5 class="fw-bold">Board</h5>

            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStageModal">Add Board</button>
        </div>

    <div class="card-body bg-white">
        <table class="table  text-center">
            <thead>
                <tr>
                    {{-- <th>board Id</th> --}}
                    <th>Board Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($boards as $board)
                <tr>
                    {{-- <td>{{ $board->id }}</td> --}}
                    <td>
                        {{ $board->name }}
                    </td>
                    <td colspan="3">
                                <div class="d-flex justify-content-center gap-1">
                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editStageForm{{ $board->id }}">Edit</button>
                                    <form class="mb-0" action="{{ route('board.destroy', $board->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this board?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
     </div>
</div>


@include('board.modals.add-stage')
@include('board.modals.edit-stage')
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css">
<script>
    $(document).on('click', '#addStageBtn', function () {
        $('#stageContainer').append(`@include('board.stage-row')`);
    });
</script>  
 <script src="{{ asset('js/board/add-board-jquery.js') }}"></script>
<script src="{{ asset('js/board/edit-board-jquery.js') }}"></script>

@endsection
