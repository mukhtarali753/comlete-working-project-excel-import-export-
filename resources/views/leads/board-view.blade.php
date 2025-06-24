{{-- @extends('layouts.theme')
@section('title','lead')
    
@endsection
@section('content')
<div class="container py-4">
    <div class="lead-management-board">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Lead Management Board</h1>
            <div class="d-flex gap-3">
                <select class="form-select" style="width: 200px;" 
                        onchange="window.location.href = '{{ route('leads.index') }}/' + this.value">
                    <option value="">Select Lead Board</option>
                    @foreach($boards as $board)
                        <option value="{{ $board->id }}" 
                            {{ isset($currentBoard) && $currentBoard->id == $board->id ? 'selected' : '' }}>
                            {{ $board->name }}
                        </option>
                    @endforeach
                </select>
                <a href="{{ route('leads.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Lead
                </a>
            </div>
        </div>

        <div class="stages-container d-flex overflow-auto py-2 gap-3">
            @foreach($stages as $stage)
                <div class="stage-card flex-shrink-0" data-stage-id="{{ $stage->id }}" 
                     style="width: 320px;">
                    <div class="card h-100">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ $stage->name }}</h5>
                            <span class="badge bg-primary rounded-pill">
                                {{ $stage->leads->count() }}
                            </span>
                        </div>
                        <div class="card-body p-2 overflow-auto" style="max-height: 70vh;">
                            <div class="leads-list" id="stage-{{ $stage->id }}">
                                @foreach($stage->leads as $lead)
                                    <div class="lead-card mb-2 p-3 border rounded bg-white" 
                                         draggable="true" data-lead-id="{{ $lead->id }}">
                                        <div class="d-flex align-items-start gap-2">
                                            <input type="checkbox" class="mt-1" 
                                                   id="lead-{{ $lead->id }}-{{ $stage->id }}">
                                            <div class="flex-grow-1">
                                                <label for="lead-{{ $lead->id }}-{{ $stage->id }}" 
                                                       class="mb-1 fw-bold">
                                                    {{ $lead->name }}
                                                </label>
                                                <div class="text-muted small">
                                                    Created: {{ $lead->created_at->format('M d, Y') }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end gap-1 mt-2">
                                            <a href="{{ route('leads.edit', $lead->id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteLeadModal"
                                                    data-lead-id="{{ $lead->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection --}}