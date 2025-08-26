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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

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
    const initialSheets = @json($sheets ?? []);
    const fileId = @json($file->id ?? null);
    let isImporting = @json(!isset($file) ? true : false);

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
    function createBlankSheet(rows = 16, cols = 26) {
        const blankData = Array.from({ length: rows }, () =>
            Array.from({ length: cols }, () => ({ v: "" }))
        );

        return {
            name: "Sheet1",
            data: blankData,
            config: {
                rowlen: Object.fromEntries([...Array(rows).keys()].map(i => [i, 30])),
                columnlen: Object.fromEntries([...Array(cols).keys()].map(j => [j, 200]))
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
        const formattedSheets = sheets.map(sheet => {
            const hasCellData = Array.isArray(sheet.celldata) && sheet.celldata.length > 0;
            const sheetData = {
                name: sheet.name,
                order: sheet.order,
                status: 1,
                config: sheet.config || { rowlen: {}, columnlen: {} },
            };
            if (hasCellData) {
                sheetData.celldata = sheet.celldata;
            } else {
                sheetData.data = Array.isArray(sheet.data) ? sheet.data : (typeof sheet.data === 'string' ? JSON.parse(sheet.data) : []);
            }
            
            if (sheet.id) {
                sheetData.id = sheet.id;
            }
            
            return sheetData;
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
        const allSheets = luckysheet.getAllSheets();
        const sheetToDelete = allSheets[sheetIndex];
        
        if (!confirm(`Are you sure you want to delete "${sheetToDelete.name}"?`)) {
            return;
        }

        // If the sheet exists in the database (has an ID), send delete request
        if (sheetToDelete.id && fileId) {
            $.ajax({
                url: `/sheets/${sheetToDelete.id}`,
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
                    alert('Failed to delete sheet: ' + (xhr.responseJSON?.message || xhr.statusText));
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
        const currentSheets = luckysheet.getAllSheets();
        const existingNames = currentSheets.map(s => s.name.toLowerCase());
        let newSheetName = `Sheet${window.sheetCount}`;

        while (existingNames.includes(newSheetName.toLowerCase())) {
            window.sheetCount++;
            newSheetName = `Sheet${window.sheetCount}`;
        }

        const blankRows = 16;
        const blankCols = 26;
        const newSheetData = Array.from({ length: blankRows }, () =>
            Array.from({ length: blankCols }, () => ({ v: "" }))
        );

        const newSheet = {
            name: newSheetName,
            data: newSheetData,
            config: {
                rowlen: Object.fromEntries([...Array(blankRows).keys()].map(i => [i, 30])),
                columnlen: Object.fromEntries([...Array(blankCols).keys()].map(j => [j, 200]))
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
        setTimeout(() => {
            luckysheet.setSheetActive(currentSheets.length - 1);
        }, 100);
    });

    function buildSavePayload() {
        const allSheets = luckysheet.getAllSheets();
        const fileName = $('#fileNameInput').val().trim() || `sheet_${new Date().toISOString().slice(0,10)}`;
        return {
            name: fileName,
            sheets: allSheets.map(sheet => ({
                name: sheet.name,
                data: JSON.stringify(sheet.data),
                config: JSON.stringify(sheet.config || {}),
                celldata: JSON.stringify(sheet.celldata || []),
                order: sheet.order,
                id: sheet.id || null
            })),
            file_id: fileId || null
        };
    }

    function saveNow(isAuto = false) {
        const payload = buildSavePayload();
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
                    window.history.replaceState({}, '', `/excel-preview/${response.file_id}`);
                }
                if (response.sheets) {
                    const updatedSheets = luckysheet.getAllSheets().map((sheet, index) => {
                        if (sheet.__isNew && response.sheets[index]) {
                            sheet.id = response.sheets[index].id;
                            delete sheet.__isNew;
                        }
                        delete sheet.__modified;
                        return sheet;
                    });
                    initializeLuckysheet(updatedSheets);
                }
            }
        });
    }

    let autoSaveTimer = null;
    function scheduleAutoSave() {
        if (autoSaveTimer) clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => saveNow(true), 1500);
    }

    // Save data button handler
    $('#saveSheetBtn').on('click', function() { saveNow(false); });

    // Mark modified and autosave on edits
    if (luckysheet && typeof luckysheet.on === 'function') {
        const markModified = function() {
            const allSheets = luckysheet.getAllSheets();
            const activeSheetIndex = luckysheet.getActiveSheetIndex();
            allSheets[activeSheetIndex].__modified = true;
            scheduleAutoSave();
        };

        luckysheet.on('cellEdited', markModified);
        luckysheet.on('updated', markModified);
        luckysheet.on('cellMousedown', function() {});
        // Hook common toolbar actions that affect formatting via keydown already
    }

    // Keyboard shortcuts: Ctrl/Cmd+S to save, and autosave on common edit keys
    $(document).on('keydown', function(e) {
        const key = (e.key || '').toLowerCase();
        const ctrl = e.ctrlKey || e.metaKey;
        if (ctrl && key === 's') {
            e.preventDefault();
            saveNow(false);
            return;
        }
        // Bold/Italic/Underline, Undo/Redo
        if (ctrl && ['b','i','u','z','y'].includes(key)) {
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
        const allSheets = luckysheet.getAllSheets();
        const wb = XLSX.utils.book_new();
        
        // Process each sheet
        allSheets.forEach(sheet => {
            // Get the sheet data and convert to 2D array
            const sheetData = sheet.data.map(row => 
                row ? row.map(cell => cell?.v || "") : []
            );
            
            // Create worksheet and add to workbook
            const ws = XLSX.utils.aoa_to_sheet(sheetData);
            XLSX.utils.book_append_sheet(wb, ws, sheet.name || `Sheet${sheet.order + 1}`);
        });
        
        // Generate file name and download
        const fileName = $('#fileNameInput').val().trim() || 'export';
        XLSX.writeFile(wb, `${fileName}.xlsx`);
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
@endsection