   $(document).on('click', '.add-stage-btn', function () {
        const boardId = $(this).data('board-id');
        const container = $('#stageContainer' + boardId);
        const index = $(this).data('next-index');

        let row = `
        <div class="row mb-2 align-items-center stage-row">
            <input type="hidden" name="stages[${index}][id]" value="">
            <div class="col-1 text-center drag-handle"><span class="btn btn-light">⋮⋮</span></div>
            <div class="col-4"><input type="text" name="stages[${index}][name]" class="form-control" required></div>
            <div class="col-4"><input type="text" name="stages[${index}][description]" class="form-control" required></div>
            <div class="col-2"><input type="email" name="stages[${index}][email]" class="form-control" required></div>
            <div class="col-1 text-center"><button type="button" class="btn btn-danger btn-sm remove-stage">✖️</button></div>
        </div>`;
        container.append(row);
        $(this).data('next-index', index + 1);
    });

    $(document).on('click', '.remove-stage', function () {
        $(this).closest('.stage-row').remove();
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