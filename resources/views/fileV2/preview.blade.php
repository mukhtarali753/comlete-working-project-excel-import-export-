@extends('layouts.theme')

@section('title', 'Files V2 Preview Table')

@section('content')
    <div class="container-fluid mt-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h5 class="m-0 font-weight-bold text-primary">Files V2</h5>

                {{-- ORIGINAL header buttons --}}
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <button id="listUsersBtn" class="btn btn-secondary btn-sm">
                        <i class="fas fa-users"></i> List of Users
                    </button>
                    <button id="importSheetBtn" class="btn btn-success btn-sm">
                        <i class="fas fa-file-import"></i> Import Sheet
                    </button>
                    <button id="openCreateFileModal" class="btn btn-info btn-sm">
                        <i class="fas fa-plus"></i> Create File
                    </button>
                </div>
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
                                @php
                                    $isOwner = $file->user_id == Auth::id();
                                    $share = $file->shares->where('user_id', Auth::id())->first();
                                    $permission = $share ? $share->type : null;
                                  @endphp
                                <tr data-id="{{ $file->id }}" style="cursor: pointer;">
                                    <td>{{ $file->id }}</td>
                                    <td>{{ $file->name }}</td>
                                    <td>{{ $file->user_id }}</td>
                                    <td>
                                        @if($isOwner)
                                            <button class="btn btn-sm btn-danger delete-row" data-id="{{ $file->id }}"><i
                                                    class="fas fa-trash"></i></button>
                                            <button class="btn btn-sm btn-primary edit-row" data-id="{{ $file->id }}"><i
                                                    class="fas fa-edit"></i></button>
                                            <a href="{{ route('sheetV2.export', [$file, 'xlsx']) }}"
                                                class="btn btn-sm btn-warning"><i class="fas fa-file-export"></i></a>
                                            <button class="btn btn-sm btn-success share-row" data-id="{{ $file->id }}"><i
                                                    class="fas fa-share"></i></button>
                                        @elseif($permission === 'editor')
                                            <button class="btn btn-sm btn-primary edit-row" data-id="{{ $file->id }}"><i
                                                    class="fas fa-edit"></i></button>
                                        @elseif($permission === 'viewer')
                                            <button class="btn btn-sm btn-info view-row" data-id="{{ $file->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endif
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
    <div class="modal fade" id="createFileModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="createFileForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Excel File V2</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">File Name</label><input type="text" name="name"
                                class="form-control" required></div>
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
    <div class="modal fade" id="editFileModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editFileForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit File V2</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">File Name</label><input type="text" name="name"
                                id="editFileName" class="form-control" required><input type="hidden" id="editFileId"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update File</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- List of Users Modal -->
    <div class="modal fade" id="listUsersModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">List of Users</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>File Name</th>
                                    <th>Owner/Creator</th>
                                    <th>Shared Users</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($files as $file)
                                    <tr>
                                        <td><strong>{{ $file->name }}</strong></td>
                                        <td>
                                            @if($file->user)
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-user-circle text-primary me-2"></i>
                                                    <div>
                                                        <div><strong>{{ $file->user->name }}</strong></div>
                                                        <small class="text-muted">{{ $file->user->email }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">Unknown</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($file->shares && $file->shares->count() > 0)
                                                <div class="d-flex flex-column gap-2">
                                                    @foreach($file->shares as $share)
                                                        @if($share->user)
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-user-share text-{{ $share->type === 'editor' ? 'success' : 'info' }} me-2"></i>
                                                                <div class="flex-grow-1">
                                                                    <div>
                                                                        <strong>{{ $share->user->name }}</strong>
                                                                        <span class="badge bg-{{ $share->type === 'editor' ? 'success' : 'info' }} ms-2">{{ ucfirst($share->type) }}</span>
                                                                    </div>
                                                                    <small class="text-muted">{{ $share->user->email }}</small>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">No shared users</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No files found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- share modal --}}
    <div class="modal fade" id="shareFileModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Share File – Set Access per User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Choose user button --}}
                    <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="toggleUserListBtn">Choose
                        Users</button>

                    {{-- user list (hidden by default) --}}
                    <div id="userSelectList" class="border rounded p-2"
                        style="max-height:250px; overflow-y:auto; display:none;">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th class="text-center">Select</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $u)
                                    <tr>
                                        <td>{{ $u->name }} <small class="text-muted">({{ $u->email }})</small></td>
                                        <td class="text-center">
                                            <input class="form-check-input user-pick" type="checkbox" value="{{ $u->id }}"
                                                data-name="{{ $u->name }}" data-email="{{ $u->email }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- current shares + live-added users --}}
                    <div class="mt-3">
                        <h6 class="small mb-2">Current Shares</h6>
                        <div id="currentShares" class="border rounded p-2" style="max-height:200px; overflow-y:auto;">
                            <div class="text-muted small">No shares yet</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="shareSaveBtn">Save & Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/fileV2/file-manager-v2.css') }}">
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(function () {
            const shareModal = new bootstrap.Modal('#shareFileModal');
            const listUsersModal = new bootstrap.Modal('#listUsersModal');
            let currentFileId = null;

            /* ---------- open list users modal  ---------- */
            $('#listUsersBtn').click(function () {
                listUsersModal.show();
            });

            /* ---------- open share modal  ---------- */
            $(document).on('click', '.share-row', function () {
                currentFileId = $(this).data('id');
                $('#userSelectList input[type=checkbox]').prop('checked', false);   // reset
                $('#currentShares').empty().append('<div class="text-muted small">No shares yet</div>');
                $('#userSelectList').hide();                                       // start collapsed
                loadCurrentShares(currentFileId);                                  // load existing
                shareModal.show();
            });

            /* ---------- toggle user list  ---------- */
            $('#toggleUserListBtn').click(function () {
                $('#userSelectList').slideToggle();
            });

            /* ---------- when user ticked : add under Current Shares with dropdown + ✖ -- */
            $(document).on('change', '.user-pick', function () {
                const id = $(this).val();
                const name = $(this).data('name');
                const email = $(this).data('email');
                const checked = $(this).is(':checked');

                if (checked) {
                    // disable checkbox so user can't pick again
                    $(this).prop('disabled', true);
                    // add row with live dropdown + ✖ remove icon
                    const row = `
            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded user-share-row" data-user-id="${id}">
              <div><strong>${name}</strong> <small class="text-muted">(${email})</small></div>
              <div class="d-flex align-items-center gap-2">
                <select class="form-select form-select-sm share-type" style="width:auto;">
                  <option value="viewer">Viewer</option>
                  <option value="editor">Editor</option>
                </select>
                <button class="btn btn-sm btn-outline-danger remove-line" title="Remove">&times;</button>
              </div>
            </div>`;
                    $('#currentShares .text-muted').remove();   // remove "no shares" text
                    $('#currentShares').append(row);
                } else {
                    // re-enable checkbox (in case you want un-pick later)
                    $(this).prop('disabled', false);
                    // remove row
                    $('.user-share-row[data-user-id="' + id + '"]').remove();
                    if ($('#currentShares').children().length === 0) {
                        $('#currentShares').append('<div class="text-muted small">No shares yet</div>');
                    }
                }
            });

            /* ---------- remove line (✖)  ---------- */
            $(document).on('click', '.remove-line', function () {
                const row = $(this).closest('.user-share-row');
                const userId = row.data('user-id');
                const fileId = currentFileId;

                // find the share id for this user+file
                $.get(`/fileV2/${fileId}/shares`, res => {
                    const share = res.shares.find(s => s.user.id == userId);
                    if (!share) return; // not shared yet

                    // call your EXISTING removeShare route
                    $.ajax({
                        url: `/fileV2/shares/${share.id}`,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' }
                    })
                        .done(() => {
                            
                            row.remove();
                           
                            $('.user-pick[value="' + userId + '"]').prop('disabled', false);
                            if ($('#currentShares').children().length === 0) {
                                $('#currentShares').append('<div class="text-muted small">No shares yet</div>');
                            }
                            toastr.success('Share removed');
                        })
                        .fail(() => toastr.error('Remove failed'));
                });
            });

            /* ---------- save sharing  ---------- */
            $('#shareSaveBtn').click(function () {
                const rows = $('#currentShares .user-share-row');
                const payload = [];

                rows.each(function () {
                    const userId = $(this).data('user-id');
                    const type = $(this).find('.share-type').val();
                    payload.push({ user_id: userId, type: type });
                });

                if (!payload.length) { toastr.warning('No user selected'); return; }

                const reqs = payload.map(obj =>
                    $.post(`/fileV2/${currentFileId}/share`, {
                        _token: '{{ csrf_token() }}',
                        user_id: obj.user_id,
                        type: obj.type
                    })
                );

                Promise.all(reqs)
                    .then(() => {
                        toastr.success('Sharing saved');
                        shareModal.hide();
                        setTimeout(() => location.reload(), 600);
                    })
                    .catch(xhr => toastr.error(xhr.responseJSON?.message || 'Share failed'));
            });

            /* ---------- load existing shares  ---------- */
            function loadCurrentShares(fileId) {
                $.get(`/fileV2/${fileId}/shares`, res => {
                    const box = $('#currentShares'); box.empty();
                    if (!res.shares.length) { box.append('<div class="text-muted small">No shares yet</div>'); return; }
                    res.shares.forEach(s => {
                        // disable checkbox for already-shared users
                        $('.user-pick[value="' + s.user.id + '"]').prop('disabled', true);
                        const row = `
              <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded user-share-row" data-user-id="${s.user.id}">
                <div><strong>${s.user.name}</strong> <small class="text-muted">(${s.user.email})</small></div>
                <div class="d-flex align-items-center gap-2">
                  <select class="form-select form-select-sm share-type" style="width:auto;">
                    <option value="viewer" ${s.type === 'viewer' ? 'selected' : ''}>Viewer</option>
                    <option value="editor" ${s.type === 'editor' ? 'selected' : ''}>Editor</option>
                  </select>
                  <button class="btn btn-sm btn-outline-danger remove-line" title="Remove">&times;</button>
                </div>
              </div>`;
                        box.append(row);
                    });
                }, 'json').fail(() => toastr.error('Failed to load shares'));
            }

            /* ---------- your ORIGINAL JS (unchanged)  ---------- */
            const createModal = new bootstrap.Modal('#createFileModal');
            const editModal = new bootstrap.Modal('#editFileModal');

            $('#openCreateFileModal').click(() => { $('#createFileForm')[0].reset(); createModal.show(); });
            $('#createFileForm').submit(function (e) {
                e.preventDefault();
                const name = $(this).find('input[name=name]').val().trim();
                if (!name) { toastr.warning('File name required'); return; }
                $.post("{{ route('fileV2.store') }}", $(this).serialize(), res => {
                    createModal.hide(); toastr.success(res.message); setTimeout(() => location.reload(), 1000);
                }, 'json').fail(xhr => toastr.error(xhr.responseJSON?.message || 'Failed'));
            });

            $(document).on('click', '.edit-row', function () {
                const id = $(this).data('id');
                $.get(`/fileV2/${id}/edit`, res => {
                    $('#editFileName').val(res.name); $('#editFileId').val(res.id); editModal.show();
                }, 'json').fail(() => toastr.error('Fetch failed'));
            });
            $('#editFileForm').submit(function (e) {
                e.preventDefault();
                const id = $('#editFileId').val(), name = $('#editFileName').val().trim();
                if (!name) { toastr.warning('File name required'); return; }
                $.ajax({
                    url: `/fileV2/${id}`, method: 'PUT', data: $(this).serialize(), dataType: 'json',
                    success: res => { editModal.hide(); toastr.success(res.message); setTimeout(() => location.reload(), 1000); },
                    error: xhr => toastr.error(xhr.responseJSON?.message || 'Update failed')
                });
            });

            $(document).on('click', '.delete-row', function () {
                if (!confirm('Delete this file?')) return;
                const row = $(this).closest('tr'), id = $(this).data('id');
                $.ajax({
                    url: `/fileV2/${id}`, method: 'DELETE', data: { _token: '{{ csrf_token() }}' },
                    success: () => { row.remove(); toastr.success('Deleted'); },
                    error: () => toastr.error('Delete failed')
                });
            });

            $(document).on('click', '#previewTable tbody tr', function (e) {
                if ($(e.target).closest('.delete-row,.edit-row,.share-row,.view-row').length) return;
                window.location.href = `/sheetV2/excel-preview/${$(this).data('id')}`;
            });

            // View row button handler for viewers
            $(document).on('click', '.view-row', function (e) {
                e.stopPropagation(); // Prevent row click from triggering
                const fileId = $(this).data('id');
                window.location.href = `/sheetV2/excel-preview/${fileId}`;
            });

            $('#importSheetBtn').click(() => $('#importSheetFileInput').click());
            $('#importSheetFileInput').on('change', function (e) {
                const file = e.target.files[0]; if (!file) return;
                const fd = new FormData(); fd.append('file', file); fd.append('_token', '{{ csrf_token() }}');
                $.ajax({
                    url: '{{ route("sheetV2.import") }}', type: 'POST', data: fd, processData: false, contentType: false,
                    success: () => { toastr.success('Imported'); setTimeout(() => location.reload(), 1000); },
                    error: xhr => toastr.error(xhr.responseJSON?.message || 'Import failed')
                });
                e.target.value = '';
            });
        });
    </script>
    <input type="file" id="importSheetFileInput" accept=".xlsx,.xls,.csv" style="display:none;">
@endsection