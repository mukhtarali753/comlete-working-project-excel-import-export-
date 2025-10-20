@extends('layouts.theme')

@section('title', 'Excel Sheet')

@section('content')

{{-- CSRF token --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid mt-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="m-0 font-weight-bold text-primary">File & Sheet Data</h6>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <!-- File name input and existing file select -->
                <input type="text" id="fileNameInput" class="form-control form-control-sm w-auto" placeholder="create file">
                {{-- <select id="existingFileSelect" class="form-control form-control-sm w-auto">
                    <option value="">Files</option>
                </select> --}}

                <!-- Sheet buttons -->
                <button id="addNewSheetBtn" class="btn btn-sm btn-warning">
                    <i class="fas fa-plus-square"></i> Add New Sheet
                </button>
                <button id="saveSheetBtn" class="btn btn-sm btn-success">
                    <i class="fas fa-save"></i> Save Data
                </button>

                <!-- Export Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="exportDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-file-export fa-sm"></i> Export
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="exportDropdown">
                        <button id="exportXlsxBtn" class="dropdown-item" type="button">
                            <i class="fas fa-file-excel text-success"></i> Excel (.xlsx)
                        </button>
                        <button id="exportCsvBtn" class="dropdown-item" type="button">
                            <i class="fas fa-file-csv text-info"></i> CSV (.csv)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Luckysheet Container -->
        <div class="card-body p-0 position-relative">
            <div id="luckysheet-wrapper">
                <div id="luckysheet"></div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts and Styles -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/plugins/css/pluginsCss.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/css/luckysheet.css" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/plugins/js/plugin.js"></script>
<script src="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/luckysheet.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<script>
$(document).ready(function() {
    if (typeof luckysheet === 'undefined') {
        alert("Luckysheet failed to load. Check your internet connection.");
        return;
    }

    // Load existing files into dropdown
    // $.get('{{ route("files.list") }}', function(response) {
    //     if (response.files) {
    //         response.files.forEach(file => {
    //             $('#existingFileSelect').append(
    //                 `<option value="${file.id}" data-name="${file.name}">${file.name} (${file.sheets_count})</option>`
    //             );
    //         });
    //     }
    // });


    // 1. Load file list into dropdown
$.get('{{ route("files.list") }}', function(response) {
    if (response.files) {
        response.files.forEach(file => {
            $('#existingFileSelect').append(
                `<option value="${file.id}">${file.name}</option>`
            );
        });
    }
});

// 2. When a file is selected, load its sheets
$('#existingFileSelect').on('change', function () {
    const fileId = $(this).val();

    if (!fileId) return;

    $.get(`/files/${fileId}/sheets`, function (response) {
        if (response.sheets && response.sheets.length > 0) {
            // Destroy old sheet
            $('#luckysheet').html('');
            luckysheet.destroy();

            // Load new sheets
            luckysheet.create({
                container: 'luckysheet',
                lang: 'en',
                showinfobar: true,
                allowEdit: true,
                data: response.sheets
            });
        } else {
            alert("No sheets found for this file.");
        }
    });
});


    // Initialize Luckysheet strictly from backend data (no local defaults)
    function normalizeSheetForCreate(sheet) {
        const hasCellData = Array.isArray(sheet.celldata) && sheet.celldata.length > 0;
        return {
            id: sheet.id || undefined,
            name: sheet.name,
            order: sheet.order ?? 0,
            status: 1,
            config: sheet.config || { rowlen: {}, columnlen: {} },
            ...(hasCellData
                ? { celldata: sheet.celldata }
                : { data: Array.isArray(sheet.data) ? sheet.data : (typeof sheet.data === 'string' ? JSON.parse(sheet.data) : []) }
            )
        };
    }

    function initializeLuckysheetFromServerSheets(serverSheets) {
        try {
            if (typeof luckysheet !== 'undefined' && luckysheet.getAllSheets) {
                luckysheet.destroy();
            }
            const formatted = (serverSheets || [])
                .sort((a,b) => (a.order ?? 0) - (b.order ?? 0))
                .map(normalizeSheetForCreate);

            luckysheet.create({
                container: 'luckysheet',
                data: formatted,
                showinfobar: false,
                showtoolbar: true,
                showstatisticBar: false,
                showSheetBar: true,
                allowEdit: true,
                enableAddRow: true,
                enableAddCol: true,
                enableContextmenu: true,
                showGridLines: true
            });
        } catch (err) {
            alert('Failed to initialize Luckysheet: ' + err.message);
        }
    }

    function getPersistedFileId() {
        // Priority: URL param > localStorage > select value
        const urlParams = new URLSearchParams(window.location.search);
        const fromUrl = urlParams.get('file_id');
        if (fromUrl) return fromUrl;
        const fromStorage = localStorage.getItem('last_file_id');
        if (fromStorage) return fromStorage;
        return $('#existingFileSelect').val() || '';
    }

    function persistFileId(fileId) {
        if (fileId) localStorage.setItem('last_file_id', fileId);
    }

    function fetchAndInitSheets() {
        const selectedFileId = getPersistedFileId();
        const url = selectedFileId ? ('{{ route("sheets.get") }}' + '?file_id=' + encodeURIComponent(selectedFileId)) : '{{ route("sheets.get") }}';
        $.ajax({
            url: url,
            type: 'GET',
            cache: false
        }).done(function(response) {
            initializeLuckysheetFromServerSheets(response || []);
        }).fail(function(xhr, status, error) {
            console.error('Failed to load sheets:', error);
            initializeLuckysheetFromServerSheets([]);
        });
    }

    fetchAndInitSheets();

    function initializeLuckysheet(sheets) {
        try {
            // Destroy existing instance if it exists
            if (typeof luckysheet !== 'undefined' && luckysheet.getAllSheets) {
                luckysheet.destroy();
            }
            
            // Ensure sheets have proper data structure
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
                showGridLines: true
            });
        } catch (err) {
            alert('Failed to initialize Luckysheet: ' + err.message);
        }
    }

    $('#addNewSheetBtn').on('click', function() {
        addNewSheet();
    });
    $('#saveSheetBtn').on('click', function() { saveToDatabase(false); });
    $('#exportXlsxBtn').on('click', function() {
        exportSheet('xlsx');
    });
    $('#exportCsvBtn').on('click', function() {
        exportSheet('csv');
    });

    if (luckysheet && typeof luckysheet.on === 'function') {
        luckysheet.on('cellEdited', function() {
            const allSheets = luckysheet.getAllSheets();
            const activeSheetIndex = luckysheet.getActiveSheetIndex();
            allSheets[activeSheetIndex].__modified = true;
            scheduleAutoSave();
        });
    }

    // Keyboard shortcuts: Ctrl/Cmd+S to save and autosave on common edit keys
    $(document).on('keydown', function(e) {
        const key = (e.key || '').toLowerCase();
        const ctrl = e.ctrlKey || e.metaKey;
        if (ctrl && key === 's') {
            e.preventDefault();
            saveToDatabase(false);
            return;
        }
        if (ctrl && ['b','i','u','z','y'].includes(key)) {
            scheduleAutoSave();
        }
        if (key === 'delete' || key === 'backspace' || key === 'enter' || key === 'tab') {
            scheduleAutoSave();
        }
    });

    // Define addNewSheet function inside document ready
    function addNewSheet() {
        window.sheetCount = window.sheetCount || 1;
        window.sheetCount++;

        // Get current sheets with their data preserved
        const currentSheets = luckysheet.getAllSheets();
        const existingNames = currentSheets.map(s => s.name.toLowerCase());

        let newSheetName = `Sheet${window.sheetCount}`;
        while (existingNames.includes(newSheetName.toLowerCase())) {
            window.sheetCount++;
            newSheetName = `Sheet${window.sheetCount}`;
        }

        const headerRow = currentSheets[0]?.data?.[0] || [];

        const newSheetData = [
            headerRow.map(cell => ({ v: cell?.v || '' }))
        ];

        for (let i = 1; i < 16; i++) {
            let row = [];
            for (let j = 0; j < headerRow.length; j++) {
                row.push({ v: "" });
            }
            newSheetData.push(row);
        }

        const newSheet = {
            name: newSheetName,
            data: newSheetData,
            config: {
                rowlen: Object.fromEntries([...Array(16).keys()].map(i => [i, 30])),
                columnlen: Object.fromEntries([...Array(headerRow.length).keys()].map(j => [j, 200]))
            },
            order: currentSheets.length,
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
    }

    // Define saveToDatabase function inside document ready
    let autoSaveTimer = null;
    function scheduleAutoSave() {
        if (autoSaveTimer) clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => saveToDatabase(true), 1500);
    }

    function saveToDatabase(isAuto = false) {
        try {
            const allSheets = luckysheet.getAllSheets();
            const hasChanges = allSheets.some(s => s.__isNew || s.__modified);
            if (!hasChanges && !isAuto) {
                alert("No changes to save.");
                return;
            }

            const enteredName = $('#fileNameInput').val().trim();
            const selectedFileId = $('#existingFileSelect').val() || null;

            const payload = {
                name: enteredName || undefined,
                file_id: selectedFileId || undefined,
                sheets: allSheets.map(s => ({
                    id: s.id || null,
                    name: s.name,
                    order: s.order ?? 0,
                    data: JSON.stringify(s.data || []),
                    config: JSON.stringify(s.config || {}),
                    celldata: JSON.stringify(s.celldata || [])
                })),
                simple_upsert: true
            };

        $.ajax({
                url: '{{ route("sheets.save") }}',
                type: 'POST',
                data: JSON.stringify(payload),
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            }).done(function(response) {
                if (!isAuto) alert('Data saved successfully!');
                luckysheet.getAllSheets().forEach(s => { delete s.__isNew; delete s.__modified; });
            if (response && response.file_id) {
                // Persist file id to survive reloads
                persistFileId(String(response.file_id));
                const url = new URL(window.location.href);
                url.searchParams.set('file_id', String(response.file_id));
                window.history.replaceState({}, '', url.toString());
            }
                if (response && Array.isArray(response.sheets)) {
                    initializeLuckysheetFromServerSheets(response.sheets);
                } else {
                    fetchAndInitSheets();
                }
            }).fail(function(xhr, status, error) {
                alert('Failed to save data: ' + (xhr.responseJSON?.message || error));
            });
        } catch (err) {
            alert('Save failed: ' + err.message);
        }
    }

    // Define exportSheet function inside document ready
    function exportSheet(type) {
        try {
            const sheet = luckysheet.getSheet();
            const maxCol = Math.max(...sheet.data.map(row => row ? row.length : 0));
            const exportData = sheet.data.map(row => {
                return [...Array(maxCol).keys()].map(i => row?.[i]?.v || "");
            });

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(exportData);
            XLSX.utils.book_append_sheet(wb, ws, sheet.name || "Sheet");

            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            const fileName = `file-data_${timestamp}`;

            if (type === 'csv') {
                const csv = XLSX.utils.sheet_to_csv(ws);
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                saveAs(blob, `${fileName}.csv`);
            } else {
                XLSX.writeFile(wb, `${fileName}.${type}`);
            }
        } catch (err) {
            alert("Export failed: " + err.message);
        }
    }
});
</script>

<style>
.card-body {
    height: 75vh;
    padding: 0 !important;
    position: relative;
}
#luckysheet-wrapper {
    height: 100%;
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
.dropdown-item i {
    width: 20px;
    text-align: center;
    margin-right: 5px;
}
@media (max-width: 768px) {
    .card-body {
        height: 65vh;
    }
}
</style>
@endsection