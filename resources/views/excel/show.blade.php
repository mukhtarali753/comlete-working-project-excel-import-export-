@extends('layouts.theme')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">{{ $file->name }}</h2>
                    <div class="flex space-x-2">
                        <a href="{{ route('excel.import.download', [$file, 'xlsx']) }}" 
                           class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition duration-200">
                            📥 Download Excel
                        </a>
                        <a href="{{ route('excel.import.index') }}" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                            ← Back to Import
                        </a>
                    </div>
                </div>

                <!-- File Info -->
                <div class="bg-blue-50 p-4 rounded-lg mb-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-blue-600">Imported: {{ $file->created_at->format('M d, Y H:i') }}</p>
                            <p class="text-sm text-blue-600">{{ count($sheets) }} sheet(s) imported</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-blue-600">Total rows: {{ array_sum(array_column($sheets, 'row_count')) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Sheet Tabs -->
                @if(count($sheets) > 0)
                    <div class="mb-6">
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                @foreach($sheets as $index => $sheet)
                                    <button onclick="showSheet({{ $index }})" 
                                            class="sheet-tab whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm {{ $index === 0 ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                                            data-sheet="{{ $index }}">
                                        {{ $sheet['name'] }}
                                        <span class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2.5 rounded-full text-xs">
                                            {{ $sheet['row_count'] }}
                                        </span>
                                    </button>
                                @endforeach
                            </nav>
                        </div>
                    </div>

                    <!-- Sheet Content -->
                    @foreach($sheets as $index => $sheet)
                        <div class="sheet-content {{ $index === 0 ? '' : 'hidden' }}" data-sheet="{{ $index }}">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-800">{{ $sheet['name'] }}</h3>
                                <span class="text-sm text-gray-500">{{ $sheet['row_count'] }} rows</span>
                            </div>
                            
                            @if(count($sheet['data']) > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                @foreach($sheet['data'][0] as $cellIndex => $cell)
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                                        {{ $cell ?: 'Column ' . ($cellIndex + 1) }}
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($sheet['data'] as $rowIndex => $row)
                                                @if($rowIndex > 0) {{-- Skip header row --}}
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
                            @else
                                <div class="text-center py-8">
                                    <div class="text-4xl text-gray-300 mb-4">📊</div>
                                    <p class="text-gray-500">No data found in this sheet</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-12">
                        <div class="text-4xl text-gray-300 mb-4">📄</div>
                        <p class="text-gray-500">No sheets found in this file</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function showSheet(sheetIndex) {
    // Hide all sheet contents
    document.querySelectorAll('.sheet-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Show selected sheet content
    document.querySelector(`[data-sheet="${sheetIndex}"]`).classList.remove('hidden');
    
    // Update tab styles
    document.querySelectorAll('.sheet-tab').forEach(tab => {
        tab.classList.remove('border-blue-500', 'text-blue-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Highlight active tab
    document.querySelector(`[data-sheet="${sheetIndex}"]`).classList.add('border-blue-500', 'text-blue-600');
    document.querySelector(`[data-sheet="${sheetIndex}"]`).classList.remove('border-transparent', 'text-gray-500');
}
</script>
@endsection

