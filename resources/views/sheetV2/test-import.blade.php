@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Excel Import Test - V2') }}</div>

                <div class="card-body">
                    <form id="importForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="file" class="form-label">Select Excel File</label>
                            <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="file_name" class="form-label">Custom File Name (Optional)</label>
                            <input type="text" class="form-control" id="file_name" name="file_name" placeholder="Leave empty to use original filename">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Import Excel File</button>
                    </form>

                    <div id="result" class="mt-4" style="display: none;">
                        <div class="alert" id="resultAlert"></div>
                    </div>

                    <div id="importedFiles" class="mt-4">
                        <h5>Recently Imported Files:</h5>
                        <div class="list-group" id="filesList">
                            <!-- Files will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('importForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.textContent = 'Importing...';
    
    fetch('{{ route("sheetV2.import") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        const resultDiv = document.getElementById('result');
        const alertDiv = document.getElementById('resultAlert');
        
        resultDiv.style.display = 'block';
        
        if (data.file_id) {
            alertDiv.className = 'alert alert-success';
            alertDiv.innerHTML = `
                <strong>Success!</strong> ${data.message}<br>
                <strong>File ID:</strong> ${data.file_id}<br>
                <strong>Imported Sheets:</strong> ${data.imported_sheets}<br>
                <strong>Imported Rows:</strong> ${data.imported_rows}<br>
                <a href="/sheetV2/excel-preview/${data.file_id}" class="btn btn-sm btn-primary mt-2">Open in Editor</a>
            `;
            
            // Reset form
            document.getElementById('importForm').reset();
            loadRecentFiles();
        } else {
            alertDiv.className = 'alert alert-danger';
            alertDiv.innerHTML = `<strong>Error:</strong> ${data.message}`;
        }
    })
    .catch(error => {
        const resultDiv = document.getElementById('result');
        const alertDiv = document.getElementById('resultAlert');
        
        resultDiv.style.display = 'block';
        alertDiv.className = 'alert alert-danger';
        alertDiv.innerHTML = `<strong>Error:</strong> ${error.message}`;
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Import Excel File';
    });
});

function loadRecentFiles() {
    fetch('{{ route("sheetV2.files.list") }}')
        .then(response => response.json())
        .then(data => {
            const filesList = document.getElementById('filesList');
            filesList.innerHTML = '';
            
            if (data.files && data.files.length > 0) {
                data.files.slice(0, 5).forEach(file => {
                    const fileItem = document.createElement('a');
                    fileItem.href = `/sheetV2/excel-preview/${file.id}`;
                    fileItem.className = 'list-group-item list-group-item-action';
                    fileItem.innerHTML = `
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${file.name}</h6>
                            <small>${file.sheets_count || 0} sheets</small>
                        </div>
                    `;
                    filesList.appendChild(fileItem);
                });
            } else {
                filesList.innerHTML = '<div class="list-group-item">No files imported yet.</div>';
            }
        })
        .catch(error => {
            console.error('Error loading files:', error);
        });
}

// Load recent files on page load
document.addEventListener('DOMContentLoaded', loadRecentFiles);
</script>
@endsection













