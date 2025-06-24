<div class="modal fade" id="addStageModal" tabindex="-1" aria-labelledby="addStageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Board</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="leaveForm" action="{{ route('board.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Board Name</label>
                        <input type="text" class="form-control" name="name" required>
                          @error('name')
                          <span class="text-danger">{{ $message }}</span>
                          @enderror
                    </div>

                    <div class="row fw-bold text-center mb-2">
                        <div class="col-1"></div>
                        <div class="col-4">Stage Name</div>
                        <div class="col-4">Description</div>
                        <div class="col-2">Emails</div>
                        <div class="col-1"></div>
                    </div>

                    <div id="stageContainer">
                        @include('board.stage-row')
                    </div>

                    <div class="mt-3">
                        <button type="button" id="addStageBtn">+ Stage</button>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var addStageModal = new bootstrap.Modal(document.getElementById('addStageModal'));
            addStageModal.show();
        });
    </script>
@endif
