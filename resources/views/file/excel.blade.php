@extends('layouts.theme')

@section('title', 'Excel Preview')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="m-0 font-weight-bold text-primary">File Name: {{ $file->name ?? 'New Spreadsheet' }}</h6>
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/plugins/css/pluginsCss.css" />
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
            const sheetData = {
                name: sheet.name,
                data: Array.isArray(sheet.data) ? sheet.data : JSON.parse(sheet.data),
                order: sheet.order,
                status: 1,
                config: {
                    rowlen: {},
                    columnlen: {}
                }
            };
            
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
            showtoolbar: false,
            showstatisticBar: false,
            showSheetBar: true,
            allowEdit: true,
            enableAddRow: true,
            enableAddCol: true,
            enableContextmenu: true,
            showGridLines: true,
            allowUpdateWhenUnFocused: false,
            userInfo: null,
            userMenuItem: []
        });

        // Add custom context menu for sheet deletion now a add delete are working
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

    // Add new sheet button handler
    $('#addNewSheetBtn').on('click', function() {
        window.sheetCount = window.sheetCount || initialSheets.length;
        window.sheetCount++;

        const allSheets = luckysheet.getAllSheets();
        const existingNames = allSheets.map(s => s.name.toLowerCase());
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
            order: allSheets.length,
            status: 1,
            celldata: [],
            __isNew: true
        };

        allSheets.push(newSheet);
        initializeLuckysheet(allSheets);
        luckysheet.setSheetActive(allSheets.length - 1);
    });

    // Save data button handler
    $('#saveSheetBtn').on('click', function() {
        const allSheets = luckysheet.getAllSheets();
        const fileName = $('#fileNameInput').val().trim() || `sheet_${new Date().toISOString().slice(0,10)}`;

        $.ajax({
            url: '{{ route("sheets.save") }}',
            type: 'POST',
            data: JSON.stringify({
                name: fileName,
                sheets: allSheets.map(sheet => ({
                    name: sheet.name,
                    data: JSON.stringify(sheet.data),
                    order: sheet.order,
                    id: sheet.id || null
                })),
                file_id: fileId || null
            }),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                alert('Data saved successfully!');
                if (!fileId && response.file_id) {
                    window.history.replaceState({}, '', `/excel-preview/${response.file_id}`);
                }
                
                // Update sheet IDs if they were created
                if (response.sheets) {
                    const updatedSheets = luckysheet.getAllSheets().map((sheet, index) => {
                        if (sheet.__isNew && response.sheets[index]) {
                            sheet.id = response.sheets[index].id;
                            delete sheet.__isNew;
                        }
                        return sheet;
                    });
                    initializeLuckysheet(updatedSheets);
                }
            },
            error: function(xhr) {
                alert('Failed to save: ' + (xhr.responseJSON?.message || xhr.statusText));
            }
        });
    });

    // Export button handler
    $('#exportBtn').on('click', function() {
        const sheet = luckysheet.getSheet();
        const exportData = sheet.data.map(row =>
            row ? row.map(cell => cell?.v || "") : []
        );

        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(exportData);
        XLSX.utils.book_append_sheet(wb, ws, sheet.name || "Sheet");

        const fileName = $('#fileNameInput').val().trim() || 'export';
        XLSX.writeFile(wb, `${fileName}.xlsx`);
    });
});
</script>

<style>
.card-body {
    height: 75vh;
    padding: 0 !important;
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