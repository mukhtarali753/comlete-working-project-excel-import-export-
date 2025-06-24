@extends('layouts.theme')

@section('content')
<div class="container mt-3">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header bg-light text-black d-flex justify-content-between align-items-center">
            <h5>Lead Board</h5>
              <button type="button" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addStageModal">
               Add Board
            </button>
            

{{-- <button type="button" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addStageModal">
    Add Board
</button> --}}

            
        </div>

        <div class="card-body bg-white">
            <table class="table text-center">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th colspan="3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($leads as $lead)
                        <tr>
                            <td>{{ $lead->id }}</td>
                            <td>{{ $lead->name }}</td>
                            <td colspan="3">
                                <div class="d-flex justify-content-center gap-1">
                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#editStageForm{{ $lead->id }}">Edit</button>
                                    <form class="mb-0" action="{{ route('lead.destroy', $lead->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure you want to delete this lead?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editStageForm{{ $lead->id }}" tabindex="-1" aria-labelledby="editStageModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Update Stage</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('stage.update', $lead->id) }}" method="POST" id="editForm{{ $lead->id }}">
                                            @csrf
                                            @method('PUT')

                                            <div class="mb-3">
                                                <label class="form-label">Lead Name</label>
                                                <input type="text" name="lead_name" class="form-control" value="{{ $lead->name }}" required>
                                            </div>

                                            <div class="row fw-bold text-center mb-2">
                                                <div class="col-1"></div> <!-- drag handle -->
                                                <div class="col-4">Stage Name</div>
                                                <div class="col-4">Description</div>
                                                <div class="col-2">Email</div>
                                                <div class="col-1"></div> <!-- remove btn -->
                                            </div>

                                            <div id="stageContainer{{ $lead->id }}">
                                                @foreach ($lead->stages as $index => $stage)
                                                    <div class="row mb-2 align-items-center stage-row">
                                                        <input type="hidden" name="stages[{{ $index }}][id]" value="{{ $stage->id }}">
                                                        <div class="col-1 text-center cursor-move drag-handle">
                                                            <span class="btn btn-light">⋮⋮</span>
                                                        </div>
                                                        <div class="col-4">
                                                            <input type="text" class="form-control" name="stages[{{ $index }}][name]" value="{{ $stage->name }}" required>
                                                        </div>
                                                        <div class="col-4">
                                                            <input type="text" class="form-control" name="stages[{{ $index }}][description]" value="{{ $stage->description }}" required>
                                                        </div>
                                                        <div class="col-2">
                                                            <input type="email" class="form-control" name="stages[{{ $index }}][email]" value="{{ $stage->email }}" required>
                                                        </div>
                                                        <div class="col-1 text-center">
                                                            <button type="button" class="btn btn-danger btn-sm remove-stage">✖️</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            

                                            

                                            <div class="mt-3">
                                                <button type="button" 
                                                    class="btn btn-secondary add-stage-btn" 
                                                     data-next-index="{{ count($lead->stages) }}" 
                                                    data-lead-id="{{ $lead->id }}" 
                                                   
                                                    style="background-color: white; color: black; border-color: #6c757d;">
                                                    + Stage
                                                </button>
                                            </div>

                                            <div class="mt-4 text-end">
                                                <button type="submit" class="btn btn-primary">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>













{{--add-board modal--}}
<div class="modal fade" id="addStageModal" tabindex="-1" aria-labelledby="addStageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStageModalLabel">Add Stage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- Validation Errors -->
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($errors->any())
                <script>
                    window.onload = function () {
                        var myModal = new bootstrap.Modal(document.getElementById('addStageModal'));
                        myModal.show();
                    };
                </script>
                @endif

                <!-- Stage Form -->
                <form id="leaveForm" action="{{ route('lead.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Enter your name" required>
                    </div>

                    <div class="row fw-bold text-center mb-2">
                        <div class="col-1"></div>
                        <div class="col-4">Stage Name</div>
                        <div class="col-4">Description</div>
                        <div class="col-2">Emails</div>
                        <div class="col-1"></div>
                    </div>

                    <!-- Stage Rows Container -->
                    <div id="stageContainer">
                        @include('layout.stage-row', ['stageName' => ''])
                    </div>

                    <div class="mt-3">
                        <button type="button" id="addStageBtn" class="btn btn-secondary" style="background-color: white; color: black; border-color: #6c757d;">
                            + Stage
                        </button>
                    </div>

                    <hr>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



{{--end-add-board modal--}}

@endsection










@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css">
<script>
$(document).ready(function () {
    // Make stage rows sortable by drag handle
    $('[id^="stageContainer"]').sortable({
        handle: ".drag-handle",
        items: ".stage-row",
        placeholder: "ui-state-highlight",
        forcePlaceholderSize: true,
        axis: 'y'
    });

    // Handle Add Stage button click dynamically
//     $(document).on('click', '.add-stage-btn', function () {
//     const btn = $(this);
//     const leadId = btn.data('lead-id');
//     let index = btn.data('next-index');
//     const container = $('#stageContainer' + leadId);
    

//     $.ajax({
       
        
//         url: "{{ route('update.stage.row') }}",
//         method: "get",
//         data: { index: index },
//         success: function (html) {
//             container.append(html);
//             btn.data('next-index', index + 1);
 
           
//         },
//         error: function () {
//             alert('Failed to load new stage row');
            
//         }
//     });
// });

// correct
$(document).on('click', '.add-stage-btn', function() {
    const btn = $(this);
    const leadId = btn.data('lead-id');
    const index = btn.data('next-index');
    const form = $('#editForm' + leadId);
    // const container = $('#stageContainer' + leadId);
    
    $.ajax({
        url: '{{ route("update.stage.row") }}',
        method: 'GET',
        data: { index: index },
        success: function(response) {
           
            form.append(response);
            // container.append(response); 

           
            
            btn.data('next-index', index + 1);
            
           
            console.log('Added row with index:', index);
            console.log('Form data:', form.serialize());
        },
        error: function(xhr) {
            console.error("Error:", xhr.responseText);
            alert('Failed to add new stage');
        }
    });
});
    


// $(document).on('click', '.add-stage-btn', function() {
//     const btn = $(this);
//     const leadId = btn.data('lead-id');
//     const index = btn.data('next-index');
//     const form = $('#editForm' + leadId);
//     const container = $('#stageContainer' + leadId);
    
//     $.ajax({
//         url: '{{ route("update.stage.row") }}',
//         method: 'GET',
//         data: { index: index },
//         success: function(response) {
//             // Append to both form AND container to ensure proper binding
//             const $newRow = $(response);
//             form.append($newRow.clone());
//             // container.append($newRow);
            
//             btn.data('next-index', index + 1);
//             container.sortable('refresh');
            
//             // Debug form data
//             console.log('Form contents:', form.find('input').serializeArray());
//         },
//         error: function(xhr) {
//             console.error("Error:", xhr.responseText);
//             alert('Failed to add new stage');
//         }
//     });
// });


////////////////
    $(document).on('click', '.remove-stage', function () {
        $(this).closest('.stage-row').remove();
    });
});

$(document).ready(function () {
    
    $('[id^="editStageForm"]').on('show.bs.modal', function () {
        const modal = $(this);
        const container = modal.find('[id^="stageContainer"]'); 
        const stageRows = container.find('.stage-row');

        stageRows.each(function () {
            const hiddenIdInput = $(this).find('input[type="hidden"][name*="[id]"]');
            if (!hiddenIdInput.length || !hiddenIdInput.val()) {
                $(this).remove();
            }
        });

        
        const addBtn = modal.find('.add-stage-btn');
        addBtn.data('next-index', container.find('.stage-row').length);
    });
});





////////////////////////////////////////////////////////
//add board js

    // Initialize sortable
    $(document).ready(function () {
        $("#stageContainer").sortable({
            handle: ".drag-handle",
            items: ".stage-row",
            placeholder: "stage-placeholder",
            forcePlaceholderSize: true
        });
    });

    // Remove a stage
    $(document).on('click', '.remove-stage', function () {
        $(this).closest('.stage-row').remove();
    });

    // Add new stage using AJAX
    $('#addStageBtn').on('click', function () {
        $.ajax({
            url: '{{ route("stage.row") }}', // Defined in web.php
            method: 'GET',
            success: function (html) {
                $('#stageContainer').append(html);
            },
            error: function () {
                alert('Failed to load stage row');
            }
        });
    });
     $(document).ready(function () {
    $('#addStageModal').on('show.bs.modal', function () {
      
        // $('#leaveForm')[0].reset();

        
        // let stageRows = $('#stageContainer .stage-row');
        // if (stageRows.length > 1) {
        //     stageRows.slice(1).remove();
        // }

        // stageRows.first().find('input').val('');
    });
});


// end add board js
</script>
@endsection  

