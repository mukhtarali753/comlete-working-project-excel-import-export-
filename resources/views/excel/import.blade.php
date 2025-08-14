@extends('layouts.theme')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Excel File Import</h2>
                    <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        ← Back to Dashboard
                    </a>
                </div>

                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- File Upload Section -->
                <div class="bg-gray-50 p-6 rounded-lg border-2 border-dashed border-gray-300 mb-8">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">📁 Upload Excel File</h3>
                    <form action="{{ route('excel.import.preview') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Select Excel File</label>
                            <input type="file" id="file" name="file" accept=".xlsx,.xls,.csv" 
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                            <p class="text-xs text-gray-500 mt-1">Supported formats: .xlsx, .xls, .csv (Max: 2MB)</p>
                        </div>
                        <div class="flex space-x-3">
                            <button type="button" id="importSheetBtn" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition duration-200">
                                📥 Import Sheet
                            </button>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition duration-200">
                                📊 Preview & Import
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Imported Files Section -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Imported Files</h3>
                    
                    @if($files->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($files as $file)
                                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200">
                                    <div class="p-4">
                                        <div class="flex justify-between items-start mb-3">
                                            <a href="{{ route('excel.import.show', $file) }}" class="hover:text-blue-600 transition duration-200">
                                                <h4 class="font-medium text-gray-800 truncate cursor-pointer" title="{{ $file->name }}">
                                                    {{ $file->name }}
                                                </h4>
                                            </a>
                                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                                {{ $file->sheets_count }} sheets
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500 mb-3">
                                            Imported: {{ $file->created_at->format('M d, Y H:i') }}
                                        </p>
                                        <div class="flex space-x-2">
                                            <a href="{{ route('excel.import.show', $file) }}" 
                                               class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition duration-200">
                                                👁️ View
                                            </a>
                                            <a href="{{ route('excel.import.download', [$file, 'xlsx']) }}" 
                                               class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition duration-200">
                                                📥 Download
                                            </a>
                                            <a href="{{ route('sheets.export', [$file, 'xlsx']) }}" 
                                               class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-sm transition duration-200">
                                                📤 Export
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-4xl text-gray-300 mb-4">📄</div>
                            <p class="text-gray-500">No files imported yet</p>
                            <p class="text-sm text-gray-400 mt-1">Upload your first Excel file above</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Hidden file input for Import Sheet -->
<input type="file" id="importSheetFileInput" accept=".xlsx,.xls,.csv" style="display: none;">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Import Sheet button handler
    $('#importSheetBtn').on('click', function() {
        $('#importSheetFileInput').click();
    });

    // Handle file selection for Import Sheet
    $('#importSheetFileInput').on('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Create FormData and submit to import route
        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');

        // Submit the form to import the file directly
        $.ajax({
            url: '{{ route("sheets.import") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                alert('Sheet imported successfully!');
                // Reload the page to show the new file
                window.location.reload();
            },
            error: function(xhr) {
                alert('Error importing sheet: ' + (xhr.responseJSON?.message || xhr.statusText));
            }
        });
        
        // Clear the file input
        e.target.value = '';
    });
});
</script>
@endsection

