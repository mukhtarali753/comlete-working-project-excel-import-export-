{{-- @extends('layouts.theme')

@section('content')
<div class="container py-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0">Edit Lead</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('leads.update', $lead->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Lead Name</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="{{ $lead->name }}" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="board_id" class="form-label">Board</label>
                        <select class="form-select" id="board_id" name="board_id" required>
                            <option value="">Select Board</option>
                            @foreach($boards as $board)
                                <option value="{{ $board->id }}" 
                                    {{ $lead->board_id == $board->id ? 'selected' : '' }}>
                                    {{ $board->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="stage_id" class="form-label">Stage</label>
                        <select class="form-select" id="stage_id" name="stage_id" required>
                            <option value="">Select Stage</option>
                            @foreach($stages as $stage)
                                <option value="{{ $stage->id }}" 
                                    data-board="{{ $stage->board_id }}"
                                    {{ $lead->stage_id == $stage->id ? 'selected' : '' }}>
                                    {{ $stage->name }} ({{ $stage->board->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">Update Lead</button>
                    <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const boardSelect = document.getElementById('board_id');
        const stageSelect = document.getElementById('stage_id');
        
        // Filter stages based on selected board
        function filterStages() {
            const boardId = boardSelect.value;
            const options = stageSelect.querySelectorAll('option');
            
            options.forEach(option => {
                if (option.value === '') return;
                
                if (option.dataset.board === boardId) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                    if (option.selected) option.selected = false;
                }
            });
            
            // If no stage is selected, select the first available
            if (!stageSelect.value && boardId) {
                const firstVisible = stageSelect.querySelector('option:not([value=""]):not([style*="display: none"])');
                if (firstVisible) firstVisible.selected = true;
            }
        }
        
        boardSelect.addEventListener('change', filterStages);
        
        // Initialize on page load
        filterStages();
    });
</script>
@endpush
@endsection --}}