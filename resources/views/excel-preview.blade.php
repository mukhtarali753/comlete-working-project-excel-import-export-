@extends('layouts.theme')

@section('content')
<div class="container mt-4">
    <h2>Excel File Preview</h2>
    <input type="file" id="excelFile" class="form-control w-25" />

    <div class="mt-4">
        <table class="table table-bordered" id="excelTable"></table>
    </div>
</div>
@endsection

@section('scripts')
<!-- SheetJS for frontend Excel parsing -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    document.getElementById('excelFile').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });

            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];
            const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

            let table = document.getElementById('excelTable');
            table.innerHTML = '';
            jsonData.forEach(function(row) {
                let tr = document.createElement('tr');
                row.forEach(function(cell) {
                    let td = document.createElement('td');
                    td.textContent = cell;
                    tr.appendChild(td);
                });
                table.appendChild(tr);
            });
        };
        reader.readAsArrayBuffer(file);
    });
</script>
@endsection
