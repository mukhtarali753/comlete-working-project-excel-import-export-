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
                    {{-- <div class="dropdown-menu dropdown-menu-right" aria-labelledby="exportDropdown">
                        <button id="exportXlsxBtn" class="dropdown-item" type="button">
                            <i class="fas fa-file-excel text-success"></i> Excel (.xlsx)
                        </button>
                        <button id="exportCsvBtn" class="dropdown-item" type="button">
                            <i class="fas fa-file-csv text-info"></i> CSV (.csv)
                        </button>
                    </div> --}}
                </div>
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
    console.log('initialSheets:', initialSheets); // Debug initialSheets

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
            __isNew: false
        };
    }

    // Function to initialize Luckysheet with custom settings to avoid demo
    function initializeLuckysheet(sheets) {
        if (!Array.isArray(sheets) || sheets.length === 0) {
            console.warn('No valid sheets provided, creating a blank sheet');
            sheets = [createBlankSheet()];
        }

        luckysheet.destroy(); // Destroy existing instance to clear cache
        luckysheet.create({
            container: 'luckysheet',
            data: sheets,
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
    }

    initializeLuckysheet(initialSheets);

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
                    order: sheet.order
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
            },
            error: function(xhr) {
                alert('Failed to save: ' + (xhr.responseJSON?.message || xhr.statusText));
            }
        });
    });

    // $('#exportXlsxBtn').on('click', () => exportSheet('xlsx'));
    // $('#exportCsvBtn').on('click', () => exportSheet('csv'));

    // function exportSheet(type) {
    //     const sheet = luckysheet.getSheet();
    //     const exportData = sheet.data.map(row =>
    //         row ? row.map(cell => cell?.v || "") : []
    //     );

    //     const wb = XLSX.utils.book_new();
    //     const ws = XLSX.utils.aoa_to_sheet(exportData);
    //     XLSX.utils.book_append_sheet(wb, ws, sheet.name || "Sheet");

    //     const fileName = $('#fileNameInput').val().trim() || 'export';

    //     if (type === 'csv') {
    //         const csv = XLSX.utils.sheet_to_csv(ws);
    //         const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    //         saveAs(blob, `${fileName}.csv`);
    //     } else {
    //         XLSX.writeFile(wb, `${fileName}.xlsx`);
    //     }
    // }


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

    // Add delete functionality
    $(document).on('click', '.delete-sheet', function() {
        const sheetId = $(this).data('sheet-id');
        if (confirm('Are you sure you want to delete this sheet?')) {
            $.ajax({
                url: `/sheets/${sheetId}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    alert(response.message);
                    // Refresh sheets after deletion
                    if (fileId) {
                        $.get(`/files/${fileId}/sheets`, function(data) {
                            initializeLuckysheet(data);
                        });
                    }
                },
                error: function(xhr) {
                    alert('Failed to delete sheet: ' + xhr.responseJSON?.message);
                }
            });
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
@endsectionclear
