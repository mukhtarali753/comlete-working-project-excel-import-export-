{{-- <!-- Edit Modal -->
<div class="modal fade" id="editLeadModal" tabindex="-1" aria-labelledby="editLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLeadModalLabel">Edit Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editLeadForm" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="editLeadName" class="form-label">Lead Name</label>
                        <input type="text" class="form-control" id="editLeadName" name="name" required value="{{$lead->name}}" >
                    </div>

                    <div class="mb-3">
                        <label for="editCustomerId" class="form-label">Customer ID</label>
                        <input type="text" class="form-control" id="editCustomerId" name="customer_id" required>
                    </div>

                    <input type="hidden" name="board_id" id="editBoardId">
                    <input type="hidden" name="stage_id" id="editStageId">

                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div> --}}
