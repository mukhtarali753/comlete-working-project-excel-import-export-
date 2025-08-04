@extends('layouts.theme')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h4>📥 Import Students Excel File</h4>
        </div>
        <div class="card-body">
            {{-- Error --}}
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- Import Validation Errors --}}
            @if(session('import_errors'))
                <div class="alert alert-warning">
                    <h5>⚠️ Import Errors:</h5>
                    <ul>
                        @foreach(session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- File Upload Form --}}
            <form action="{{ route('students.preview') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group mb-3">
                    <label for="file">Excel File</label>
                    <input type="file" class="form-control @error('file') is-invalid @enderror"
                           name="file" id="file" required>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        Supported formats: .xlsx, .xls, .csv — Max: 2MB
                    </small>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-eye"></i> Preview
                </button>
                <a href="{{ route('students.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Students
                </a>
            </form>

            {{-- Preview Table --}}
            @if(session('preview_data'))
            <div class="mt-4">
                {{-- <h5>🔍 Preview First 10 Rows</h5> --}}
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                @foreach(session('preview_data.headers') as $header)
                                    <th>{{ $header }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(session('preview_data.rows') as $row)
                                <tr>
                                    @foreach($row as $cell)
                                        <td>{{ $cell ?? '(empty)' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Confirm Import --}}
                <form action="{{ route('students.import') }}" method="POST" class="mt-3">
                    @csrf
                    <input type="hidden" name="file_path" value="{{ session('file_path') }}">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-file-import"></i> Confirm Import ({{ session('total_rows') }} rows)
                    </button>
                    <a href="{{ route('students.import.form') }}" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
