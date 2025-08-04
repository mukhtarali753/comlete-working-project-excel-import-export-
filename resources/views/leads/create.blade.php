@extends('layouts.theme')
@section('title', 'Lead Board')

@section('content')
<div class="container py-4">
    <div class="card">
        <div class="card-header bg-light text-black fw-bold d-flex justify-content-between align-items-center flex-wrap">
            <h2 class="mb-0 me-3">Lead Board</h2>
            <div class="d-flex align-items-center gap-3">
                <select class="form-select form-select-sm" id="board_id" style="min-width: 200px; height: 40px;">
                    <option value="">Select Board</option>
                    @foreach($boards as $index => $board)
                        <option value="{{ $board->id }}" {{ $index === 0 ? 'selected' : '' }} data-stages='@json($board->stages)'>
                            {{ $board->name }}
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-primary open-create-modal rounded-3 w-100"
                    style="padding: 1px 7px; height:37px;" id="addLeadHeaderBtn"
                    data-bs-toggle="modal" data-bs-target="#createLeadModal">
                    Add Lead
                </button>
            </div>
        </div>

        <div class="card-body">
            <div id="leadCardsContainer" class="d-flex gap-2 flex-wrap mt-4">
                @foreach ($boards as $board)
                    @foreach ($board->stages as $stage)
                        <div class="card-lead p-3 shadow-sm border rounded board-card"
                             data-board-id="{{ $board->id }}"
                             data-stage-id="{{ $stage->id }}"
                             style="min-width: 250px; display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-1">
                                <h5>Stage: {{ $stage->name }}</h5>
                                <button class="btn btn-sm btn-primary open-create-modal"
                                    data-board-id="{{ $board->id }}"
                                    data-stage-id="{{ $stage->id }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#createLeadModal">+</button>
                            </div>

                            <div class="sortable-leads" data-stage-id="{{ $stage->id }}">
                                @foreach ($stage->leads as $lead)
                                    <div class="lead-subtext text-muted p-2 bg-light border mb-1 d-flex justify-content-between align-items-center"
                                        data-lead-id="{{ $lead->id }}">
                                        <span class="btn btn-light">⋮⋮</span>
                                        <span>{{ $lead->name }}</span>
                                        
                                        <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" onsubmit="return confirm('Are you sure?')" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="board_id" value="{{ $board->id }}">
                                            
                                            <button type="submit" class="btn p-0 text-danger" style="border: none; background: transparent;">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Create Lead Modal -->
<div class="modal fade" id="createLeadModal" tabindex="-1" aria-labelledby="createLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('leads.store') }}" method="POST" id="leadForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Lead</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Lead Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>

                    <div class="board-stage-wrapper mb-3">
                        <label class="form-label">Select Board</label>
                        <select class="form-select" id="board_id_select" name="board_id">
                            <option value="">Select Board</option>
                            @foreach($boards as $board)
                                <option value="{{ $board->id }}" data-stages='@json($board->stages)'>
                                    {{ $board->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="board-stage-wrapper mb-3">
                        <label class="form-label">Select Stage</label>
                        <select class="form-select" id="stage_id_select" name="stage_id">
                            <option value="">Select Stage</option>
                        </select>
                        <input type="hidden" id="selectedBoardIdFromSession" value="{{ session('selected_board_id') }}">
                    </div>

                    <button type="submit" class="btn btn-success">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>



@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css">
<link rel="stylesheet" href="{{ asset('css/leads/lead-board.css') }}">
<script src="{{asset('js/leads/lead-board-jquuery.js')}}"></script>

<script>
$(function () {
    function filterCards(boardId) {
        $(".board-card").hide();
        $(`.board-card[data-board-id="${boardId}"]`).show();
    }

    const boardSelect = $('#board_id');
    if (boardSelect.val()) {
        filterCards(boardSelect.val());
    }

    boardSelect.on('change', function () {
        filterCards(this.value);
    });

    $(document).on('click', '.open-create-modal', function () {
        $('#createLeadModal form')[0].reset();
        $('#stage_id_select').empty().append('<option value="">Select Stage</option>');

        const boardId = $(this).data('board-id');
        const stageId = $(this).data('stage-id');
        const selectedOption = $('#board_id option[value="' + boardId + '"]');
        const stages = selectedOption.data('stages') || [];

        if (boardId && stageId) {
            $('.board-stage-wrapper').hide();
            $('#board_id_select').val(boardId);
            $('#stage_id_select').empty().append('<option value="">Select Stage</option>');
            stages.forEach(stage => {
                $('#stage_id_select').append(`<option value="${stage.id}">${stage.name}</option>`);
            });
            $('#stage_id_select').val(stageId);
        } else {
            $('.board-stage-wrapper').show();

            const currentBoardId = $('#board_id').val();
            const currentBoardOption = $('#board_id').find(':selected');
            const currentStages = currentBoardOption.data('stages') || [];

            if (!currentBoardId || currentStages.length === 0) return;

            $('#board_id_select').val(currentBoardId);
            $('#stage_id_select').empty().append('<option value="">Select Stage</option>');
            currentStages.forEach(stage => {
                $('#stage_id_select').append(`<option value="${stage.id}">${stage.name}</option>`);
            });
            $('#stage_id_select').val(currentStages[0].id);
        }
    });

    $(".sortable-leads").sortable({
        connectWith: ".sortable-leads",
        placeholder: "lead-placeholder",
        items: ".lead-subtext",
        forcePlaceholderSize: true,
        stop: function (event, ui) {
            const leadId = ui.item.data("lead-id");
            const newStageId = ui.item.closest(".sortable-leads").data("stage-id");

            $.ajax({
                url: "{{ route('leads.update-stage') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    lead_id: leadId,
                    stage_id: newStageId
                },
                success: function () {
                    console.log("Lead moved successfully.");
                },
                error: function () {
                    alert("Failed to update lead stage.");
                }
            });
        }
    }).disableSelection();

    document.getElementById("board_id_select").addEventListener("change", function () {
        const selectedOption = this.options[this.selectedIndex];
        const boardId = selectedOption.value;
        const stages = JSON.parse(selectedOption.getAttribute("data-stages") || "[]");

        const stageSelect = document.getElementById("stage_id_select");
        stageSelect.innerHTML = '<option value="">Select Stage</option>';

        if (stages.length > 0) {
            stages.forEach(stage => {
                const option = document.createElement("option");
                option.value = stage.id;
                option.textContent = stage.name;
                stageSelect.appendChild(option);
            });
            stageSelect.value = stages[0].id;
        }
    });

    // ✅ NEW CODE TO APPLY SESSION SELECTED BOARD
    const selectedBoardIdFromSession = $('#selectedBoardIdFromSession').val();
    if (selectedBoardIdFromSession) {
        $('#board_id').val(selectedBoardIdFromSession).trigger('change');
    }
});
</script>
@endsection
