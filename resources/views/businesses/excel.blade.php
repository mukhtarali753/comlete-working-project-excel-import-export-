@extends('layouts.theme')

@section('title', 'Excel Preview')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="m-0 font-weight-bold text-primary">Business Data</h6>
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


    const businesses = @json($businesses);
    let sheetData = [];

    if (businesses.length > 0) {
        sheetData = [
            [{ v: "ID" }, { v: "Name" }, { v: "Industry" }, { v: "Email" }, { v: "Phone" }, { v: "Status" }],
            ...businesses.map(b => [
                { v: b.id },
                { v: b.name },
                { v: b.industry },
                { v: b.contact_email },
                { v: b.phone },
                { v: b.is_active ? "Active" : "Inactive" }
            ])
        ];
    } else {
        for (let i = 0; i < 16; i++) {
            let row = [];
            for (let j = 0; j < 26; j++) {
                row.push({ v: "" });
            }
            sheetData.push(row);
        }
    }

    let allSheets = [{
        name: "Sheet1",
        data: sheetData,
        config: {
            rowlen: Object.fromEntries([...Array(sheetData.length).keys()].map(i => [i, 30])),
            columnlen: Object.fromEntries([...Array(sheetData[0].length).keys()].map(j => [j, 200]))
        },
        order: 0,
        __isNew: false
    }];

    // Fetch saved sheets from DB
    $.ajax({
        url: '{{ route("sheets.get") }}',
        type: 'GET',
        cache: false,
        success: function(response) {
            if (response && response.length > 0) {
                const savedSheets = response.map(sheet => ({
                    name: sheet.name,
                    data: sheet.data,
                    config: sheet.config,
                    order: sheet.order,
                    __isNew: false
                }));
                allSheets = [...allSheets, ...savedSheets].sort((a, b) => a.order - b.order);
            }
            initializeLuckysheet(allSheets);
        },
        error: function(xhr, status, error) {
            console.error('Failed to load sheets:', error);
            initializeLuckysheet(allSheets); // fallback
        }
    });

    function initializeLuckysheet(sheets) {
        try {
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
                showGridLines: true
            });
        } catch (err) {
            alert('Failed to initialize Luckysheet: ' + err.message);
        }
    }

    $('#addNewSheetBtn').on('click', addNewSheet);
    $('#saveSheetBtn').on('click', saveToDatabase);
    $('#exportXlsxBtn').on('click', () => exportSheet('xlsx'));
    $('#exportCsvBtn').on('click', () => exportSheet('csv'));

    luckysheet.on('cellEdited', function() {
        const allSheets = luckysheet.getAllSheets();
        const activeSheetIndex = luckysheet.getActiveSheetIndex();
        allSheets[activeSheetIndex].__modified = true;
    });



    
});

function addNewSheet() {
    window.sheetCount = window.sheetCount || 1;
    window.sheetCount++;

    const allSheets = luckysheet.getAllSheets();
    const existingNames = allSheets.map(s => s.name.toLowerCase());

    let newSheetName = `Sheet${window.sheetCount}`;
    while (existingNames.includes(newSheetName.toLowerCase())) {
        window.sheetCount++;
        newSheetName = `Sheet${window.sheetCount}`;
    }

    const headerRow = allSheets[0]?.data?.[0] || [];

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
        order: allSheets.length,
        __isNew: true
    };

    allSheets.push(newSheet);

    luckysheet.create({
        container: 'luckysheet',
        data: allSheets,
        showinfobar: false,
        showtoolbar: false,
        showstatisticBar: false,
        showSheetBar: true,
        allowEdit: true,
        enableAddRow: true,
        enableAddCol: true,
        enableContextmenu: true,
        showGridLines: true
    });

    luckysheet.setSheetActive(allSheets.length - 1);
}


function saveToDatabase() {
    try {
        const allSheets = luckysheet.getAllSheets();
        const sheetsToSave = allSheets.filter(s => s.__isNew || s.__modified);
        if (sheetsToSave.length === 0) {
            alert("No new or modified sheets to save.");
            return;
        }

        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const enteredName = $('#fileNameInput').val().trim();
        const selectedOption = $('#existingFileSelect').find('option:selected');
        const existingFileId = selectedOption.val();
        const existingFileName = selectedOption.data('name');

        const fileName = enteredName || existingFileName || `sheets_${timestamp}`;

        const fileData = {
            name: fileName,
            type: 'xlsx',
            sheets: sheetsToSave.map((sheet, index) => ({
                name: sheet.name,
                data: JSON.stringify(sheet.data),
                order: sheet.order
            }))
        };

        if (existingFileId) {
            fileData.file_id = existingFileId;
        }

        $.ajax({
            url: '{{ route("sheets.save") }}',
            type: 'POST',
            data: JSON.stringify(fileData),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                alert('Sheet data saved successfully!');
                sheetsToSave.forEach(sheet => {
                    delete sheet.__isNew;
                    delete sheet.__modified;
                });
            },
            error: function(xhr, status, error) {
                alert('Failed to save data: ' + (xhr.responseJSON?.message || error));
            }
        });
    } catch (err) {
        alert('Save failed: ' + err.message);
    }
}

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
        const fileName = `business-data_${timestamp}`;

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