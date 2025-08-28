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
                <button id="historyBtn" class="btn btn-sm btn-info" @if(!isset($file) || !($file->id ?? null)) disabled @endif>
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

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">Change History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="white-space:nowrap">Cell</th>
                                <th style="white-space:nowrap">Change Type</th>
                                <th>Old Value</th>
                                <th>New Value</th>
                                <th style="white-space:nowrap">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                            <tr><td colspan="5" class="text-center text-muted">No history yet</td></tr>
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
            sheets.push({
                name: sheet.name,
                data: JSON.stringify(sheet.data),
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
                    $('#historyBtn').prop('disabled', false);
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

    // Mark modified and autosave on edits + capture history
    if (luckysheet && typeof luckysheet.on === 'function') {
        var historyBuffer = [];
        var historyTimer = null;

        function toA1(colIndex, rowIndex) {
            var col = '';
            var x = (colIndex || 0) + 1;
            while (x > 0) {
                var mod = (x - 1) % 26;
                col = String.fromCharCode(65 + mod) + col;
                x = Math.floor((x - mod) / 26) - 1;
            }
            return col + String(((rowIndex || 0) + 1));
        }

        function pushHistory(change) {
            if (!change) return;
            if (!fileId) return;
            if (!change.change_type || (typeof change.change_type === 'string' && change.change_type.trim() === '')) {
                change.change_type = 'update';
            }
            historyBuffer.push(change);
            if (historyTimer) clearTimeout(historyTimer);
            historyTimer = setTimeout(function() {
                var payload = { file_id: fileId, changes: historyBuffer.splice(0) };
                $.ajax({
                    url: '{{ route("history.store") }}',
                    type: 'POST',
                    data: JSON.stringify(payload),
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                }).done(function(res){
                    // Uncomment for debugging: console.log('History saved', res);
                }).fail(function(xhr){
                    try {
                        console.error('History save failed', xhr.status, xhr.responseText || xhr);
                    } catch(e) {}
                });
            }, 800);
        }

        var markModified = function() {
            var allSheets = luckysheet.getAllSheets();
            var activeSheetIndex = luckysheet.getActiveSheetIndex();
            allSheets[activeSheetIndex].__modified = true;
            scheduleAutoSave();
        };

        luckysheet.on('cellEdited', function(payload) {
            try {
                // payload may include r,c,oldValue/newValue depending on version
                // Log to help verify event firing
                console.debug('cellEdited event', payload);
                markModified();
                var r = (payload && (payload.r !== undefined ? payload.r : payload.row));
                var c = (payload && (payload.c !== undefined ? payload.c : payload.col));
                var cell = (r !== undefined && c !== undefined) ? toA1(c, r) : null;
                var oldVal = (payload && (payload.old !== undefined ? payload.old : payload.oldValue));
                var newVal = (payload && (payload.v !== undefined ? payload.v : payload.value));
                pushHistory({
                    cell: cell,
                    change_type: 'value change',
                    old_value: oldVal,
                    new_value: newVal
                });
            } catch(e) {
                console.warn('cellEdited handler error', e);
            }
        });
        luckysheet.on('updated', function(operate) {
            try {
                console.debug('updated event', operate);
                markModified();
                if (!operate) return;
                var op = operate.op || operate.type || '';
                var r = (operate.r !== undefined ? operate.r : (operate.row !== undefined ? operate.row : null));
                var c = (operate.c !== undefined ? operate.c : (operate.col !== undefined ? operate.col : null));
                var cell = (r !== null && c !== null) ? toA1(c, r) : (operate.cell || null);
                var oldVal = (operate.old !== undefined) ? operate.old : (operate.oldValue !== undefined ? operate.oldValue : null);
                var newVal = (operate.v !== undefined) ? operate.v : (operate.value !== undefined ? operate.value : (operate.newValue !== undefined ? operate.newValue : null));

                function labelFor(opcode) {
                    switch (opcode) {
                        case 'v':
                        case 'cellEdit':
                            return 'value change';
                        case 'bl':
                        case 'bold':
                            return 'bold';
                        case 'it':
                        case 'italic':
                            return 'italic';
                        case 'cl':
                        case 'fc':
                        case 'color':
                            return 'color';
                        case 'bgc':
                        case 'bgcolor':
                            return 'background';
                        case 'strike':
                            return 'strikethrough';
                        case 'u':
                        case 'underline':
                            return 'underline';
                        default:
                            return opcode || 'update';
                    }
                }

                var change = {
                    cell: cell,
                    change_type: labelFor(op),
                    old_value: oldVal,
                    new_value: newVal
                };
                pushHistory(change);
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



    // Update header when file name changes
    $('#fileNameInput').on('input', function() {
        isImporting = false;
        updateHeaderText();
    });

    // History button handler
    $('#historyBtn').on('click', function() {
        if (!fileId) return;
        var $tbody = $('#historyTableBody');
        $tbody.html('<tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>');
        $.getJSON('{{ route('history.get', ['fileId' => '__ID__']) }}'.replace('__ID__', fileId))
            .done(function(rows) {
                if (!rows || rows.length === 0) {
                    $tbody.html('<tr><td colspan="5" class="text-center text-muted">No history yet</td></tr>');
                    return;
                }
                var html = '';
                $.each(rows, function(index, r) {
                    function esc(v){
                        if (v === null || v === undefined) return '';
                        try { v = typeof v === 'string' ? v : JSON.stringify(v); } catch(_) {}
                        return $('<div>').text(v).html();
                    }
                    var ts = r.created_at ? new Date(r.created_at).toLocaleString() : '';
                    html += '<tr>' +
                        '<td>' + esc(r.cell || '') + '</td>' +
                        '<td>' + esc(r.change_type || '') + '</td>' +
                        '<td>' + esc(r.old_value || '') + '</td>' +
                        '<td>' + esc(r.new_value || '') + '</td>' +
                        '<td>' + esc(ts) + '</td>' +
                    '</tr>';
                });
                $tbody.html(html);
            })
            .fail(function(xhr){
                var msg = 'Failed to load history';
                if (xhr && xhr.status) msg += ' (HTTP ' + xhr.status + ')';
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg += ': ' + xhr.responseJSON.message;
                $tbody.html('<tr><td colspan="5" class="text-center text-danger">' + msg + '</td></tr>');
            });

        historyModal.show();
    });

    // History modal close button handlers - Bootstrap 5 with jQuery
    var historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
    
    // Close modal when clicking close button or secondary button
    $('#historyModal .btn-close, #historyModal .btn-secondary').on('click', function() {
        historyModal.hide();
    });

    // Close modal when clicking outside
    $('#historyModal').on('click', function(e) {
        if (e.target === this) {
            historyModal.hide();
        }
    });

    // Close modal on escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#historyModal').hasClass('show')) {
            historyModal.hide();
        }
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
@endsection