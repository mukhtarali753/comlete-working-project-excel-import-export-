
@foreach ($boards as $board)
    <div class="modal fade" id="editStageForm{{ $board->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                    


                <form action="{{ route('stage.update', $board->id) }}" method="POST" id="editForm{{ $board->id }}">
                    @csrf
                    
                    @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Update board</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">Board Name</label>
                            <input type="text" name="board_name" class="form-control" value="{{ $board->name }}" required>
                        </div>

                        <div class="row fw-bold text-center mb-2">
                            <div class="col-1"></div>
                            <div class="col-4">Stage Name</div>
                            <div class="col-4">Description</div>
                            <div class="col-2">Email</div>
                            <div class="col-1"></div>
                        </div>

                        <div id="stageContainer{{ $board->id }}">
                            @foreach ($board->stages as $index => $stage)
                                <div class="row mb-2 align-items-center stage-row">
                                    <input type="hidden" name="stages[{{ $index }}][id]" value="{{ $stage->id }}">
                                    <div class="col-1 text-center drag-handle">
                                        <span class="btn btn-light">⋮⋮</span>
                                    </div>
                                    <div class="col-4">
                                        <input type="text" name="stages[{{ $index }}][name]" class="form-control" value="{{ $stage->name }}" required>
                                    </div>
                                    <div class="col-4">
                                        <input type="text" name="stages[{{ $index }}][description]" class="form-control" value="{{ $stage->description }}" required>
                                    </div>
                                    <div class="col-2">
                                        <input type="email" name="stages[{{ $index }}][email]" class="form-control" value="{{ $stage->email }}" required>
                                    </div>
                                    <div class="col-1 text-center">
                                        <button type="button" class="btn btn-danger btn-sm remove-stage">✖️</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-3">
                            <button type="button" class="  add-stage-btn" data-next-index="{{ count($board->stages) }}" data-board-id="{{ $board->id }}">
                                + Stage
                            </button>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
