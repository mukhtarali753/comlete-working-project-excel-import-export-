@extends('layouts.theme')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Excel Preview & Import</h2>
                    <a href="{{ route('excel.import.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        ← Back to Import
                    </a>
                </div>

                <!-- File Info -->
                <div class="bg-blue-50 p-4 rounded-lg mb-6">
                    <h3 class="text-lg font-medium text-blue-800 mb-2">📄 File: {{ $fileName }}</h3>
                    <p class="text-sm text-blue-600">Found {{ count($sheetNames) }} sheet(s) in the file</p>
                </div>

                <!-- Sheet Selection -->
                <form action="{{ route('excel.import.process') }}" method="POST" class="mb-8">
                    @csrf
                    <input type="hidden" name="file_path" value="{{ $path }}">
                    <input type="hidden" name="file_name" value="{{ $fileName }}">
                    
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-800 mb-4">📋 Select Sheets to Import</h3>
                        
                        <div class="space-y-3">
                            @foreach($sheetNames as $index => $sheetName)
                                <div class="flex items-center">
                                    <input type="checkbox" id="sheet_{{ $index }}" name="selected_sheets[]" 
                                           value="{{ $sheetName }}" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                           {{ $index === 0 ? 'checked' : '' }}>
                                    <label for="sheet_{{ $index }}" class="ml-3 text-sm font-medium text-gray-700">
                                        {{ $sheetName }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition duration-200">
                                ✅ Import Selected Sheets
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Data Preview -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">👁️ Data Preview (First Sheet)</h3>
                    
                    @if(count($previewData) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                                <thead class="bg-gray-50">
                                    @foreach($previewData as $rowIndex => $row)
                                        @if($rowIndex === 0)
                                            <tr>
                                                @foreach($row as $cellIndex => $cell)
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                                        {{ $cell ?: 'Column ' . ($cellIndex + 1) }}
                                                    </th>
                                                @endforeach
                                            </tr>
                                        @endif
                                    @endforeach
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($previewData as $rowIndex => $row)
                                        @if($rowIndex > 0 && $rowIndex <= 10) {{-- Show first 10 rows --}}
                                            <tr class="hover:bg-gray-50">
                                                @foreach($row as $cell)
                                                    <td class="px-4 py-3 text-sm text-gray-900 border-b">
                                                        {{ $cell ?: '' }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if(count($previewData) > 11)
                            <p class="text-sm text-gray-500 mt-2">
                                Showing first 10 rows of {{ count($previewData) }} total rows
                            </p>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <div class="text-4xl text-gray-300 mb-4">📊</div>
                            <p class="text-gray-500">No data found in the first sheet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

