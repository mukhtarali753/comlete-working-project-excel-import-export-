@extends('layouts.theme')

@section('title', 'Excel Sheet V2')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="m-0 font-weight-bold text-primary" id="fileHeader">
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


<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Bootstrap JS (required for dropdowns) --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

{{-- Luckysheet and dependencies --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/sheetV2/sheet-excel-v2.css') }}">

{{-- Include V2 JavaScript --}}
<script src="{{ asset('js/sheetV2/sheet-excel-v2.js') }}"></script>

<script>
// This would be the same as the original excel.blade.php but with V2 routes
var initialSheets = @json($sheets ?? []);
var fileId = @json($file->id ?? null);
var isImporting = @json(!isset($file) ? true : false);


</script>

@endsection































