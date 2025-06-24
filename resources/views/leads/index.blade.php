@extends('layouts.theme')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>All Leads</h1>
        <a href="{{ route('leads.create') }}" class="btn btn-#F8F9FA">
            <i class="fas fa-plus"></i> Add Lead
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Board</th>
                            <th>Stage</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads as $lead)
                            <tr>
                                <td>{{ $lead->name }}</td>
                                <td>{{ $lead->board->name }}</td>
                                <td>{{ $lead->stage->name }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        
                                        
                                        <a href="{{ route('leads.edit', $lead->id) }}" 
                                         

                                           class="btn btn-sm btn-sm-primary">
                                            <i class="fas fa-edit">Edit</i>
                                        </a>
                                        
                                        <button class="btn btn-sm bg-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteLeadModal"
                                                data-lead-id="{{ $lead->id }}">
                                            <i class="fas fa-trash">Delete</i>
                                        </button>
                                        
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No leads found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


{{-- @include('leads.delete-modal') --}}
@endsection 