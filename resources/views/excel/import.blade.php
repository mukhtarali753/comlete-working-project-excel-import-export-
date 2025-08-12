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
                        <div>
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
                                            <h4 class="font-medium text-gray-800 truncate" title="{{ $file->name }}">
                                                {{ $file->name }}
                                            </h4>
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
@endsection

