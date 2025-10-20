@extends('layouts.theme')

@section('title', 'Excel Sheet V2')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center gap-2" >
            <h6 class="m-0 font-weight-bold text-white" id="fileHeader">
                @if(isset($file))
                    File Name V2: {{ $file->name ?? 'New Spreadsheet' }}
                @else
                    Import File V2
                @endif
            </h6>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <input type="text" id="fileNameInput" class="form-control form-control-sm w-auto"
                       value="{{ $file->name ?? '' }}" placeholder="File name">

                <div class="form-check form-check-inline align-self-center">
                    <input class="form-check-input" type="checkbox" id="enableVersionHistory" checked>
                    <label class="form-check-label" for="enableVersionHistory">
                        Version History V2 <span id="versionHistoryInfo" class="text-muted">(Auto-disabled for large sheets)</span>
                    </label>
                </div>

                <button id="addNewSheetBtn" class="btn btn-sm btn-warning">
                    <i class="fas fa-plus-square"></i> Add New Sheet
                </button>
                <button id="saveSheetBtn" class="btn btn-sm btn-success">
                    <i class="fas fa-save"></i> Save Data
                </button>
                <button id="versionHistoryBtn" class="btn btn-sm btn-info">
                    <i class="fas fa-history"></i> History
                </button>
                <button id="exportBtn" class="btn btn-sm btn-primary">
                    <i class="fas fa-file-export fa-sm"></i> Export
                </button>
            </div>
        </div>

        <!-- Progress Bar for Save Operations -->
        <div id="saveProgress" class="progress-bar-container" style="display: none;">
            <div class="progress">
                <div id="saveProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                     role="progressbar" style="width: 0%"></div>
            </div>
            <small id="saveProgressText" class="text-muted">Preparing to save...</small>
        </div>

        <div class="card-body p-0 position-relative">
            <div id="luckysheet-wrapper">
                <div id="luckysheet"></div>
            </div>
        </div>
    </div>
</div>

<!-- Version History Modal -->
<div class="modal fade" id="versionHistoryModal" tabindex="-1" aria-labelledby="versionHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="versionHistoryModalLabel">Version History V2</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="versionHistoryContent">
                    
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- CSS Assets -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/plugins/css/pluginsCss.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/plugins/plugins.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/css/luckysheet.css">
<link rel="stylesheet" href="{{ asset('css/luckysheet.css') }}">
<link rel="stylesheet" href="{{ asset('luckysheet/assets/iconfont/iconfont.css') }}">

<!-- JavaScript Assets -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/plugins/js/plugin.js"></script>
<script src="https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/luckysheet.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<!-- Main Excel JavaScript -->
<script src="{{ asset('js/excel-main.js') }}"></script>

<script>
$(document).ready(function() {
  
    var initialSheets = @json($sheets ?? []);
    var fileId = @json($file->id ?? null);
    var isImporting = @json(!isset($file) ? true : false);
    
    // Set global save URL for AJAX calls
    window.EXCEL_SAVE_URL = '{{ route("sheetV2.save") }}';
    

    initializeExcelV2(initialSheets, fileId, isImporting);
    
  
    startLuckysheetInitialization();
});
</script>
@endsection
