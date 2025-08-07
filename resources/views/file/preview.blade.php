@extends('layouts.theme')

@section('title', 'Business Preview Table')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold text-primary">Files</h5>
            <button id="openCreateFileModal" class="btn btn-info btn-sm ml-2">
                <i class="fas fa-plus"></i> Create File
            </button>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="previewTable" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>File Name</th>
                            <th>User ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($files as $file)
                        <tr data-id="{{ $file->id }}" style="cursor: pointer;">
                            <td>{{ $file->id }}</td>
                            <td>{{ $file->name }}</td>
                            <td>{{ $file->user_id }}</td>
                            <td>
                                <button class="btn btn-sm btn-danger delete-row" data-id="{{ $file->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="btn btn-sm btn-primary edit-row" data-id="{{ $file->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No files found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create File Modal -->
<div class="modal fade" id="createFileModal" tabindex="-1" aria-labelledby="createFileLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="createFileForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Excel File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="fileName" class="form-label">File Name</label>
                        <input type="text" class="form-control" id="fileName" name="name" placeholder="Enter file name" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save File</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit File Modal -->
<div class="modal fade" id="editFileModal" tabindex="-1" aria-labelledby="editFileLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editFileForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editFileName" class="form-label">File Name</label>
                        <input type="text" class="form-control" id="editFileName" name="name" placeholder="Enter file name" required>
                        <input type="hidden" id="editFileId" name="id">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update File</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {
    let createModal = new bootstrap.Modal(document.getElementById('createFileModal'));
    let editModal = new bootstrap.Modal(document.getElementById('editFileModal'));

    // Open create modal
    $('#openCreateFileModal').click(function () {
        $('#fileName').val('');
        createModal.show();
    });

    // Submit create form via AJAX
    $('#createFileForm').submit(function (e) {
        e.preventDefault();
        const fileName = $('#fileName').val().trim();

        if (fileName === '') {
            toastr.warning('File name is required.');
            return;
        }

        $.ajax({
            url: "{{ route('businesses.store') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                name: fileName
            },
            success: function (response) {
                createModal.hide();
                toastr.success(response.message);
                setTimeout(() => location.reload(), 1000);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Failed to create file.');
            }
        });
    });

    // Open edit modal and populate data
    $(document).on('click', '.edit-row', function () {
        const fileId = $(this).data('id');
        
        $.ajax({
            url: `/businesses/${fileId}/edit`,
            method: 'GET',
            success: function (response) {
                $('#editFileName').val(response.name);
                $('#editFileId').val(response.id);
                editModal.show();
            },
            error: function () {
                toastr.error('Failed to fetch file data');
            }
        });
    });

    // Submit edit form via AJAX
    $('#editFileForm').submit(function (e) {
        e.preventDefault();
        const fileId = $('#editFileId').val();
        const fileName = $('#editFileName').val().trim();

        if (fileName === '') {
            toastr.warning('File name is required.');
            return;
        }

        $.ajax({
            url: `/businesses/${fileId}/update`,
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                name: fileName
            },
            success: function (response) {
                editModal.hide();
                toastr.success(response.message);
                setTimeout(() => location.reload(), 1000);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Failed to update file.');
            }
        });
    });

    // Handle row click to open sheet editor
    $(document).on('click', '#previewTable tbody tr', function(e) {
        if ($(e.target).closest('.delete-row, .edit-row').length) {
            return;
        }
        
        const fileId = $(this).data('id');
        window.location.href = `/businesses/${fileId}/edit-sheet`;
    });

    // Delete file
    $(document).on('click', '.delete-row', function () {
        const row = $(this).closest('tr');
        const id = $(this).data('id');

        if (confirm('Are you sure you want to delete this file?')) {
            $.ajax({
                url: `/businesses/${id}`,
                method: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function () {
                    row.remove();
                    toastr.success('File deleted successfully');
                },
                error: function () {
                    toastr.error('Failed to delete file');
                }
            });
        }
    });
});
</script>
@endsection
