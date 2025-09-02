@extends('layouts.theme')

@section('title', 'Excel Preview')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="m-0 font-weight-bold text-primary" id="fileHeader">
                @if(isset($file))
                    File Name: {{ $file->name ?? 'New Spreadsheet' }}
                @else
                    Import File
                @endif
            </h6>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <input type="text" id="fileNameInput" class="form-control form-control-sm w-auto"
                       value="{{ $file->name ?? '' }}" placeholder="File name">

                <button id="addNewSheetBtn" class="btn btn-sm btn-warning">
                    <i class="fas fa-plus-square"></i> Add New Sheet
                </button>
                <button id="saveSheetBtn" class="btn btn-sm btn-success">
                    <i class="fas fa-save"></i> Save Data
                </button>
                <button id="exportBtn" class="btn btn-sm btn-primary">
                    <i class="fas fa-file-export fa-sm"></i> Export
                </button>
                <button id="historyBtn" class="btn btn-sm btn-secondary">
                    <i class="fas fa-history"></i> History
                </button>
            </div>
        </div>

        <div class="card-body p-0 position-relative">
            <div id="luckysheet-wrapper">
                <div id="luckysheet"></div>
            </div>
        </div>
    </div>
</div>

{{-- CSRF token --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Bootstrap JS (required for dropdowns) --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

{{-- Luckysheet and dependencies --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">


<link rel="stylesheet" href="/luckysheet/plugins/css/pluginsCss.css" />
<link rel="stylesheet" href="/luckysheet/assets/iconfont/iconfont.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/css/luckysheet.css" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/plugins/js/plugin.js"></script>
<script src="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/luckysheet.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<script>
$(document).ready(function() {
    var initialSheets = @json($sheets ?? []);
    var fileId = @json($file->id ?? null);
    var isImporting = @json(!isset($file) ? true : false);

    // Update header text based on import status
    function updateHeaderText() {
        const fileName = $('#fileNameInput').val().trim();
        if (isImporting) {
            $('#fileHeader').text('Import File');
        } else {
            $('#fileHeader').text(fileName ? `File Name: ${fileName}` : 'New Spreadsheet');
        }
    }

    // Function to create a blank sheet
    function createBlankSheet(rows, cols) {
        rows = rows || 16;
        cols = cols || 26;
        var blankData = [];
        for (var i = 0; i < rows; i++) {
            var row = [];
            for (var j = 0; j < cols; j++) {
                row.push({ v: "" });
            }
            blankData.push(row);
        }

        var rowlen = {};
        var columnlen = {};
        for (var k = 0; k < rows; k++) {
            rowlen[k] = 30;
        }
        for (var l = 0; l < cols; l++) {
            columnlen[l] = 200;
        }

        return {
            name: "Sheet1",
            data: blankData,
            config: {
                rowlen: rowlen,
                columnlen: columnlen
            },
            order: 0,
            status: 1,
            celldata: [],
            __isNew: true
        };
    }

    // Function to initialize Luckysheet with custom settings
    function initializeLuckysheet(sheets) {
        if (!Array.isArray(sheets) || sheets.length === 0) {
            console.warn('No valid sheets provided, creating a blank sheet');
            sheets = [createBlankSheet()];
        }

        // Format sheets data to include IDs if they exist
        var formattedSheets = [];
        $.each(sheets, function(index, sheet) {
            var hasCellData = $.isArray(sheet.celldata) && sheet.celldata.length > 0;
            var sheetData = {
                name: sheet.name,
                order: sheet.order,
                status: 1,
                config: sheet.config || { rowlen: {}, columnlen: {} },
            };
            if (hasCellData) {
                sheetData.celldata = sheet.celldata;
            } else {
                sheetData.data = $.isArray(sheet.data) ? sheet.data : (typeof sheet.data === 'string' ? JSON.parse(sheet.data) : []);
            }
            
            if (sheet.id) {
                sheetData.id = sheet.id;
            }
            
            formattedSheets.push(sheetData);
        });

        luckysheet.destroy();
        luckysheet.create({
            container: 'luckysheet',
            data: formattedSheets,
            showinfobar: false,
            showtoolbar: true,
            showstatisticBar: false,
            showSheetBar: true,
            allowEdit: true,
            enableAddRow: true,
            enableAddCol: true,
            enableContextmenu: true,
            showGridLines: true,
            allowUpdateWhenUnFocused: false
        });

        // Add custom context menu for sheet deletion
        luckysheet.setConfig({
            hook: {
                onToggleSheetMenu: function(menu) {
                    menu.push({
                        name: "Delete Sheet",
                        onclick: function() {
                            const index = luckysheet.getSheetIndex();
                            deleteSheet(index);
                        }
                    });
                    return menu;
                }
            }
        });
    }

    // Function to handle sheet deletion
    function deleteSheet(sheetIndex) {
        var allSheets = luckysheet.getAllSheets();
        var sheetToDelete = allSheets[sheetIndex];
        
        if (!confirm('Are you sure you want to delete "' + sheetToDelete.name + '"?')) {
            return;
        }

        // If the sheet exists in the database (has an ID), send delete request
        if (sheetToDelete.id && fileId) {
            $.ajax({
                url: '/sheets/' + sheetToDelete.id,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Remove the sheet from Luckysheet
                    luckysheet.deleteSheet(sheetIndex);
                    alert(response.message);
                },
                error: function(xhr) {
                    var errorMsg = 'Failed to delete sheet';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg += ': ' + xhr.responseJSON.message;
                    } else if (xhr.statusText) {
                        errorMsg += ': ' + xhr.statusText;
                    }
                    alert(errorMsg);
                }
            });
        } else {
            // For new sheets not yet saved to database
            luckysheet.deleteSheet(sheetIndex);
        }
    }

    // Initialize Luckysheet
    initializeLuckysheet(initialSheets);
    updateHeaderText();

    // Add new sheet button handler
    $('#addNewSheetBtn').on('click', function() {
        window.sheetCount = window.sheetCount || initialSheets.length;
        window.sheetCount++;

        // Get current sheets with their data preserved
        var currentSheets = luckysheet.getAllSheets();
        var existingNames = [];
        $.each(currentSheets, function(index, sheet) {
            existingNames.push(sheet.name.toLowerCase());
        });
        var newSheetName = 'Sheet' + window.sheetCount;

        while ($.inArray(newSheetName.toLowerCase(), existingNames) !== -1) {
            window.sheetCount++;
            newSheetName = 'Sheet' + window.sheetCount;
        }

        var blankRows = 16;
        var blankCols = 26;
        var newSheetData = [];
        for (var i = 0; i < blankRows; i++) {
            var row = [];
            for (var j = 0; j < blankCols; j++) {
                row.push({ v: "" });
            }
            newSheetData.push(row);
        }

        var rowlen = {};
        var columnlen = {};
        for (var k = 0; k < blankRows; k++) {
            rowlen[k] = 30;
        }
        for (var l = 0; l < blankCols; l++) {
            columnlen[l] = 200;
        }

        var newSheet = {
            name: newSheetName,
            data: newSheetData,
            config: {
                rowlen: rowlen,
                columnlen: columnlen
            },
            order: currentSheets.length,
            status: 1,
            celldata: [],
            __isNew: true
        };

        // Add the new sheet to the current sheets array
        currentSheets.push(newSheet);
        
        // Reinitialize with all sheets (preserving existing data)
        initializeLuckysheet(currentSheets);
        
        // Switch to the new sheet after a small delay to ensure initialization is complete
        setTimeout(function() {
            luckysheet.setSheetActive(currentSheets.length - 1);
        }, 100);
    });

    function buildSavePayload() {
        var allSheets = luckysheet.getAllSheets();
        var fileName = $('#fileNameInput').val().trim() || 'sheet_' + new Date().toISOString().slice(0,10);
        var sheets = [];
        
        $.each(allSheets, function(index, sheet) {
            var data2D = sheet.data;
            if (!Array.isArray(data2D) || data2D.length === 0) {
                // Fallback: build from celldata if present
                if (Array.isArray(sheet.celldata) && sheet.celldata.length > 0) {
                    var maxRow = 0, maxCol = 0;
                    sheet.celldata.forEach(function(cell){
                        if (typeof cell.r === 'number' && typeof cell.c === 'number') {
                            if (cell.r > maxRow) maxRow = cell.r;
                            if (cell.c > maxCol) maxCol = cell.c;
                        }
                    });
                    data2D = [];
                    for (var r = 0; r <= maxRow; r++) {
                        var row = [];
                        for (var c = 0; c <= maxCol; c++) { row.push({ v: "" }); }
                        data2D.push(row);
                    }
                    sheet.celldata.forEach(function(cell){
                        if (data2D[cell.r] && data2D[cell.r][cell.c]) {
                            data2D[cell.r][cell.c] = cell.v || { v: "" };
                        }
                    });
                } else {
                    data2D = [];
                }
            }

            sheets.push({
                name: sheet.name,
                data: JSON.stringify(data2D),
                config: JSON.stringify(sheet.config || {}),
                celldata: JSON.stringify(sheet.celldata || []),
                order: sheet.order,
                id: sheet.id || null
            });
        });
        
        return {
            name: fileName,
            sheets: sheets,
            file_id: fileId || null
        };
    }

    function saveNow(isAuto) {
        isAuto = isAuto || false;
        var payload = buildSavePayload();
        $.ajax({
            url: '{{ route("sheets.save") }}',
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                if (!isAuto) alert('Data saved successfully!');
                isImporting = false;
                updateHeaderText();
                if (!fileId && response.file_id) {
                    fileId = response.file_id;
                    window.history.replaceState({}, '', '/excel-preview/' + response.file_id);
                }
                if (response.sheets) {
                    var updatedSheets = [];
                    var allSheets = luckysheet.getAllSheets();
                    $.each(allSheets, function(index, sheet) {
                        if (sheet.__isNew && response.sheets[index]) {
                            sheet.id = response.sheets[index].id;
                            delete sheet.__isNew;
                        }
                        delete sheet.__modified;
                        updatedSheets.push(sheet);
                    });
                    initializeLuckysheet(updatedSheets);
                }
            },
            error: function(xhr) {
                var msg = 'Save failed';
                if (xhr.responseJSON && xhr.responseJSON.message) msg += ': ' + xhr.responseJSON.message;
                else if (xhr.statusText) msg += ': ' + xhr.statusText;
                alert(msg);
            }
        });
    }

    var autoSaveTimer = null;
    function scheduleAutoSave() {
        if (autoSaveTimer) clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() { saveNow(true); }, 1500);
    }

    // Save data button handler
    $('#saveSheetBtn').on('click', function() { saveNow(false); });

    // Mark modified and autosave on edits
    if (luckysheet && typeof luckysheet.on === 'function') {
        var markModified = function() {
            var allSheets = luckysheet.getAllSheets();
            var activeSheetIndex = luckysheet.getActiveSheetIndex();
            allSheets[activeSheetIndex].__modified = true;
            scheduleAutoSave();
        };

        luckysheet.on('cellEdited', function(payload) {
            try {
                markModified();
            } catch(e) {}
        });
        luckysheet.on('updated', function(operate) {
            try {
                markModified();
            } catch (e) {}
        });
        luckysheet.on('cellMousedown', function() {});
        // Hook common toolbar actions that affect formatting via keydown already
    }

    // Keyboard shortcuts: Ctrl/Cmd+S to save, and autosave on common edit keys
    $(document).on('keydown', function(e) {
        var key = (e.key || '').toLowerCase();
        var ctrl = e.ctrlKey || e.metaKey;
        if (ctrl && key === 's') {
            e.preventDefault();
            saveNow(false);
            return;
        }
        // Bold/Italic/Underline, Undo/Redo
        if (ctrl && $.inArray(key, ['b','i','u','z','y']) !== -1) {
            scheduleAutoSave();
        }
        // Delete/Backspace edits
        if (key === 'delete' || key === 'backspace') {
            scheduleAutoSave();
        }
        // Enter/Tab often finalize edits
        if (key === 'enter' || key === 'tab') {
            scheduleAutoSave();
        }
    });

    // Export button handler
    $('#exportBtn').on('click', function() {
        var allSheets = luckysheet.getAllSheets();
        var wb = XLSX.utils.book_new();
        
        // Process each sheet
        $.each(allSheets, function(index, sheet) {
            // Get the sheet data and convert to 2D array
            var sheetData = [];
            $.each(sheet.data, function(rowIndex, row) {
                var newRow = [];
                if (row) {
                    $.each(row, function(cellIndex, cell) {
                        newRow.push(cell && cell.v ? cell.v : "");
                    });
                }
                sheetData.push(newRow);
            });
            
            // Create worksheet and add to workbook
            var ws = XLSX.utils.aoa_to_sheet(sheetData);
            XLSX.utils.book_append_sheet(wb, ws, sheet.name || 'Sheet' + (sheet.order + 1));
        });
        
        // Generate file name and download
        var fileName = $('#fileNameInput').val().trim() || 'export';
        XLSX.writeFile(wb, fileName + '.xlsx');
    });

    // ===== Version History Modal and Logic =====
    function getActiveSheetMeta() {
        var allSheets = luckysheet.getAllSheets();
        var idx = luckysheet.getActiveSheetIndex();
        var sheet = allSheets[idx];
        return { id: sheet.id || null, name: sheet.name || '' };
    }

    function renderHistoryRows(histories) {
        var rows = (histories || []).map(function(h) {
            var badge = h.is_current ? '<span class="badge bg-success">Current</span>' : '';
            var user = (h.user && h.user.name) ? h.user.name : (h.user_id || 'Unknown');
            return '<tr>'+
                '<td>v'+ h.version_number +'</td>'+
                '<td>'+ user +'</td>'+
                '<td>'+ (new Date(h.created_at)).toLocaleString() +'</td>'+
                '<td>'+ badge +'</td>'+
                '<td class="text-end">'+
                    '<button class="btn btn-sm btn-outline-primary preview-history" data-id="'+h.id+'">Preview</button> '+
                    (h.is_current ? '' : '<button class="btn btn-sm btn-outline-danger restore-history" data-id="'+h.id+'">Restore</button>')+
                '</td>'+
            '</tr>';
        }).join('');
        $('#historyTableBody').html(rows || '<tr><td colspan="5" class="text-center">No history yet</td></tr>');
    }

    function loadHistoryList() {
        var sheetMeta = getActiveSheetMeta();
        if (!fileId) {
            $('#historyTableBody').html('<tr><td colspan="5" class="text-center">No file. Save first.</td></tr>');
            return;
        }
        // If sheet id is missing, attempt to resolve by name from backend
        if (!sheetMeta.id) {
            $.getJSON('/files/' + fileId + '/sheets')
                .done(function(res) {
                    try {
                        var list = res || [];
                        var found = list.find(function(s){ return (s.name || '').toLowerCase() === (sheetMeta.name || '').toLowerCase(); });
                        if (found && found.id) {
                            // set id on the active sheet for future calls
                            var allSheets = luckysheet.getAllSheets();
                            var idx = luckysheet.getActiveSheetIndex();
                            allSheets[idx].id = found.id;
                            // proceed to load history
                            $.getJSON('/history', { file_id: fileId, sheet_id: found.id })
                                .done(function(res2) { renderHistoryRows(res2.histories || []); })
                                .fail(function(xhr2) {
                                    console.error('History load failed', xhr2);
                                    $('#historyTableBody').html('<tr><td colspan="5" class="text-center">Failed to load history</td></tr>');
                                });
                        } else {
                            $('#historyTableBody').html('<tr><td colspan="5" class="text-center">Sheet not saved yet. Click Save, then retry.</td></tr>');
                        }
                    } catch(e) {
                        $('#historyTableBody').html('<tr><td colspan="5" class="text-center">Error resolving sheet</td></tr>');
                    }
                })
                .fail(function(xhr){
                    console.error('Resolve sheet id failed', xhr);
                    $('#historyTableBody').html('<tr><td colspan="5" class="text-center">Failed to resolve sheet</td></tr>');
                });
            return;
        }
        $.getJSON('/history', { file_id: fileId, sheet_id: sheetMeta.id })
            .done(function(res) { renderHistoryRows(res.histories || []); })
            .fail(function(xhr) {
                console.error('History load failed', xhr);
                $('#historyTableBody').html('<tr><td colspan="5" class="text-center">Failed to load history</td></tr>');
            });
    }

    $('#historyBtn').on('click', function() {
        var modalEl = document.getElementById('historyModal');
        var modal = new bootstrap.Modal(modalEl);
        // Show placeholder while loading
        $('#historyTableBody').html('<tr><td colspan="5" class="text-center py-3">Loading...</td></tr>');
        try {
            loadHistoryList();
        } catch (e) {
            console.error('History click error', e);
            $('#historyTableBody').html('<tr><td colspan="5" class="text-center">Failed to load history</td></tr>');
        }
        modal.show();
    });

    $(document).on('click', '.preview-history', function() {
        var id = $(this).data('id');
        $.getJSON('/history/' + id)
            .done(function(res) {
                var payload = res.history.data || {};
                var sheets = [{
                    name: payload.name || 'Sheet',
                    // Build from celldata if present, else use data
                    data: Array.isArray(payload.data) ? payload.data : [],
                    celldata: Array.isArray(payload.celldata) ? payload.celldata : [],
                    config: payload.config || {},
                    order: payload.order || 0,
                    status: 1
                }];
                luckysheet.destroy();
                luckysheet.create({ container: 'luckysheet', data: sheets, showinfobar: false, showtoolbar: true, showstatisticBar: false, showSheetBar: false, allowEdit: false });
            })
            .fail(function() { alert('Failed to load version'); });
    });

    $(document).on('click', '.restore-history', function() {
        var id = $(this).data('id');
        if (!confirm('Restore this version? This will create a new version as current.')) return;
        $.ajax({
            url: '/history/restore',
            type: 'POST',
            data: JSON.stringify({ history_id: id }),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        }).done(function() {
            loadHistoryList();
            alert('Version restored. Reloading current sheet view...');
            initializeLuckysheet(luckysheet.getAllSheets());
        }).fail(function(xhr){
            alert('Failed to restore: ' + (xhr.responseJSON?.message || xhr.statusText));
        });
    });



    // Update header when file name changes
    $('#fileNameInput').on('input', function() {
        isImporting = false;
        updateHeaderText();
    });
    
});
</script>

<style>
.card-body {
    height: 75vh;
    padding: 0 !important;
}
#luckysheet-wrapper {
    height:100%;
    width: 100%;
    background-color: #fff;
}
#luckysheet {
    height: 100% !important;
    width: 100% !important;
}

/* Ensure toolbar is visible */
.luckysheet-toolbar {
    display: block !important;
    visibility: visible !important;
    height: auto !important;
    min-height: 40px !important;
}

.luckysheet-toolbar-container {
    display: block !important;
    visibility: visible !important;
}

#fileHeader{
    color: black;
}
.dropdown-item i {
    width: 20px;
    text-align: center;
    margin-right: 5px;
}


/* Ensure toolbar and icons are visible */
.luckysheet-toolbar, 
.luckysheet-toolbar-container,
.luckysheet-toolbar i {
    display: block !important;
    visibility: visible !important;
    color: #000 !important; /* black icons */
    z-index: 10;
}




@media (max-width: 768px) {
    .card-body {
        height: 65vh;
    }
}


</style>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">Version History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Version</th>
                                <th>User</th>
                                <th>Created</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
    </div>
@endsection