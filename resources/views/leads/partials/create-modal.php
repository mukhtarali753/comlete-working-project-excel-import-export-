<!-- Modal -->
<!-- <div class="modal fade" id="createLeadModal" tabindex="-1" aria-labelledby="createLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createLeadModalLabel">Add New Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('leads.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Lead Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Customer ID:</label>
                        <input type="text" class="form-control" name="customer_id" required>
                    </div>
                    <input type="hidden" name="board_id" id="modal_board_id">
                    <input type="hidden" name="stage_id" id="modal_stage_id">
                    <button type="submit" class="btn btn-success">Create</button>
                </form>
            </div>
        </div>
    </div>
</div>  -->