{{-- 


<!-- Modal -->
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

@endsection

@section('scripts')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Popper & Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<!-- jQuery UI (optional - only if you need sorting) -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css">

<script>
    $(document).ready(function () {
        // Make stage rows sortable
        $("#stageContainer").sortable({
            handle: ".drag-handle",
            items: ".stage-row",
            placeholder: "stage-placeholder",
            forcePlaceholderSize: true
        });

        // Remove stage row
        $(document).on('click', '.remove-stage', function () {
            $(this).closest('.stage-row').remove();
        });

        // Add stage row dynamically
        $('#addStageBtn').on('click', function () {
            $.ajax({
                url: '{{ route("stage.row") }}',
                method: 'GET',
                success: function (html) {
                    $('#stageContainer').append(html);
                },
                error: function () {
                    alert('Failed to load stage row');
                }
            });
        });

        // Reset modal on open
        $('#addStageModal').on('show.bs.modal', function () {
            $('#leaveForm')[0].reset();

            let stageRows = $('#stageContainer .stage-row');
            if (stageRows.length > 1) {
                stageRows.slice(1).remove();
            }

            stageRows.first().find('input').val('');
        });
    });
</script>
@endsection --}}
