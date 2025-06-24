 <div class="row mb-2 align-items-center stage-row">
    <div class="col-1 text-center cursor-move drag-handle">
        <span class="btn btn-light">⋮⋮</span>
    </div>
    <div class="col-4">
        <input type="text" class="form-control" name="stage_name[]" value="{{ $stageName ?? '' }}" placeholder="Stage Name">
    </div>
    <div class="col-4">
        <input type="text" class="form-control" name="description[]" placeholder="Description">
    </div>
    <div class="col-2">
        <input type="text" class="form-control" name="emails[]" placeholder="Email">
    </div>
    <div class="col-1 text-center">
        <button type="button" class="btn btn-danger btn-sm remove-stage">✖️</button>
    </div>
</div>
