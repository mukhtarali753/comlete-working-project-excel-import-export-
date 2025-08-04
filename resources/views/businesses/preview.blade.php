@extends('layouts.theme')

@section('title','Business Preview Table')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold text-primary">Business Data Management</h5>
            <div>
                <button id="exportBtn" class="btn btn-success btn-sm">
                    <i class="fas fa-file-export"></i> Export
                </button>
                <button class="btn btn-primary btn-sm ml-2" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fas fa-file-import"></i> Import
                </button>
                <a href="{{ route('businesses.preview.excel') }}" class="btn btn-warning btn-sm ml-2">
                  <i class="fas fa-table"></i> Excel Preview Page
                 </a>
                <button id="addRowBtn" class="btn btn-info btn-sm ml-2">
                    <i class="fas fa-plus"></i> Add Row
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="previewTable" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Industry</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($businesses as $business)
                        <tr data-id="{{ $business->id }}">
                            <td>{{ $business->id }}</td>
                            <td contenteditable="true" class="editable" data-field="name">{{ $business->name }}</td>
                            <td contenteditable="true" class="editable" data-field="industry">{{ $business->industry }}</td>
                            <td contenteditable="true" class="editable" data-field="contact_email">{{ $business->contact_email }}</td>
                            <td contenteditable="true" class="editable" data-field="phone">{{ $business->phone }}</td>
                            <td class="status-{{ $business->is_active ? 'active' : 'inactive' }}">
                                {{ $business->is_active ? 'Active' : 'Inactive' }}
                            </td>
                            <td>
                                <button class="btn btn-sm btn-danger delete-row" data-id="{{ $business->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Real Excel Preview Section -->
            <div id="realExcelPreviewContainer" class="mt-5" style="display:none;">
                <h5 class="text-primary">📊 Excel-Like Sheet Preview</h5>
                <div id="luckysheet-real-preview" style="width:100%; height:500px; border: 1px solid #ccc;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Businesses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importForm" action="{{ route('businesses.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="csvFile" class="form-label">Select Excel/CSV File</label>
                        <input class="form-control" type="file" id="csvFile" name="file" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">Download the <a href="{{ route('businesses.export') }}">template</a> for reference</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Import Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Row Modal -->
<div class="modal fade" id="addRowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Business</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addRowForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newName" class="form-label">Business Name</label>
                        <input type="text" class="form-control" id="newName" required>
                    </div>
                    <div class="mb-3">
                        <label for="newIndustry" class="form-label">Industry</label>
                        <input type="text" class="form-control" id="newIndustry" required>
                    </div>
                    <div class="mb-3">
                        <label for="newEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="newEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="newPhone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="newPhone" required>
                    </div>
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">Status</label>
                        <select class="form-control" id="newStatus">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Business</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/plugins/css/pluginsCss.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/css/luckysheet.css" />
<style>
    .status-active { color: #28a745; font-weight: bold; }
    .status-inactive { color: #dc3545; font-weight: bold; }
    #previewTable th { white-space: nowrap; background-color: #4e73df; color: white; }
    .editable {
        cursor: text;
        background-color: #f9f9f9;
    }
    .editable:focus {
        outline: 2px solid #007bff;
        background-color: #fff;
    }
    #luckysheet-real-preview #luckysheet-cols-h-c,
    #luckysheet-real-preview #luckysheet-rows-h {
        display: none !important;
    }
    .delete-row {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/plugins/js/plugin.js"></script>
<script src="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/luckysheet.umd.js"></script>

<script>
    $(document).ready(function() {
        // Export button
        $('#exportBtn').click(function() {
            window.location.href = "{{ route('businesses.export') }}";
        });

        // Import form
        $('#importForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#importModal').modal('hide');
                    toastr.success(response.success);
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Import failed. Please check your file format.');
                }
            });
        });

        // Show add row modal
        $('#addRowBtn').click(function() {
            $('#addRowModal').modal('show');
        });

        // Add new row
        $('#addRowForm').submit(function(e) {
            // e.preventDefault();
            
            $.ajax({
                url: "{{ route('businesses.store') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    name: $('#newName').val(),
                    industry: $('#newIndustry').val(),
                    contact_email: $('#newEmail').val(),
                    phone: $('#newPhone').val(),
                    is_active: $('#newStatus').val()
                },
                success: function(response) {
                    // Add the new row to the table
                    const newRow = `
                        <tr data-id="${response.business.id}">
                            <td>${response.business.id}</td>
                            <td contenteditable="true" class="editable" data-field="name">${response.business.name}</td>
                            <td contenteditable="true" class="editable" data-field="industry">${response.business.industry}</td>
                            <td contenteditable="true" class="editable" data-field="contact_email">${response.business.contact_email}</td>
                            <td contenteditable="true" class="editable" data-field="phone">${response.business.phone}</td>
                            <td class="status-${response.business.is_active ? 'active' : 'inactive'}">
                                ${response.business.is_active ? 'Active' : 'Inactive'}
                            </td>
                            <td>
                                <button class="btn btn-sm btn-danger delete-row" data-id="${response.business.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    $('#previewTable tbody').append(newRow);
                    
                    // Reset form and close modal
                    $('#addRowForm')[0].reset();
                    $('#addRowModal').modal('hide');
                    
                    toastr.success('Business added successfully!');
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Failed to add business. Please try again.');
                }
            });
        });

        // Delete row
        $(document).on('click', '.delete-row', function() {
            const row = $(this).closest('tr');
            const id = $(this).data('id');
            
            if (confirm('Are you sure you want to delete this business?')) {
                $.ajax({
                    url: `/businesses/${id}`,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        row.remove();
                        toastr.success('Business deleted successfully!');
                    },
                    error: function(xhr) {
                        toastr.error('Failed to delete business. Please try again.');
                    }
                });
            }
        });

        // Excel preview
        $('#realExcelPreviewBtn').click(function () {
            let table = document.getElementById('previewTable');
            let data = [];
            for (let i = 0; i < table.rows.length; i++) {
                let row = [];
                for (let j = 0; j < table.rows[i].cells.length; j++) {
                    row.push({ v: table.rows[i].cells[j].innerText.trim() });
                }
                data.push(row);
            }

            $('#realExcelPreviewContainer').show();
            document.getElementById('luckysheet-real-preview').innerHTML = '';

            luckysheet.create({
                container: 'luckysheet-real-preview',
                showinfobar: false,
                showtoolbar: false,
                showSheetBar: false,
                showstatisticBar: false,
                sheetBottomConfig: false,
                showRowHeader: false,
                showColumnHeader: false,
                allowEdit: false,
                data: [{
                    name: "Preview",
                    status: "1",
                    order: "0",
                    row: data.length + 10,
                    column: Math.max(...data.map(r => r.length)) + 5,
                    data: data
                }]
            });
        });

        // Inline editing
        $('.editable').on('blur', function () {
            var row = $(this).closest('tr');
            var id = row.data('id');
            var field = $(this).data('field');
            var value = $(this).text().trim();

            $.ajax({
                url: "{{ route('businesses.update.inline') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}", 
                    id: id,
                    field: field,
                    value: value
                },
                success: function(response) {
                    if (field === 'is_active') {
                        row.find('.status-active, .status-inactive')
                            .removeClass('status-active status-inactive')
                            .addClass(response.is_active ? 'status-active' : 'status-inactive')
                            .text(response.is_active ? 'Active' : 'Inactive');
                    }
                    toastr.success(response.message || 'Updated successfully!');
                },
                error: function(xhr) {
                    toastr.error('Failed to update. Please try again.');
                }
            });
        });
    });
</script>
@endsection