

<div class="row mb-2 align-items-center stage-row">
    <!-- Empty ID for new stages -->
    <input type="hidden" name="stages[{{ $index }}][id]" value="">
    
    <div class="col-1 text-center cursor-move drag-handle">
        <span class="btn btn-light">⋮⋮</span>
    </div>
    
    <div class="col-4">
        <input type="text" class="form-control" 
               name="stages[{{ $index }}][name]" 
               value="" 
               placeholder="Stage Name" required>
    </div>
    
    <div class="col-4">
        <input type="text" class="form-control" 
               name="stages[{{ $index }}][description]" 
               value="" 
               placeholder="Description" required>
    </div>
    
    <div class="col-2">
        <input type="email" class="form-control" 
               name="stages[{{ $index }}][email]" 
               value="" 
               placeholder="Email" required>
    </div>
    
    <div class="col-1 text-center">
        <button type="button" class="btn btn-danger btn-sm remove-stage">✖️</button>
    </div>
</div>




