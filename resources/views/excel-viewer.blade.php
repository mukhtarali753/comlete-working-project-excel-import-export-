<!DOCTYPE html>
<html>
<head>
    <title>Excel Viewer & Editor</title>
    <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-container {
            max-height: 500px;
            overflow: auto;
            margin: 20px 0;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            position: sticky;
            top: 0;
        }
        td:focus {
            outline: 2px solid #007bff;
            background-color: #f8f9fa;
        }
        .action-buttons {
            margin: 20px 0;
        }
    </style>
</head>
<body class="container mt-4">
    <h2 class="mb-4">Excel Viewer & Editor</h2>
    
    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <label for="excelFile" class="form-label">Upload Excel File</label>
                <input class="form-control" type="file" id="excelFile" accept=".xlsx, .xls">
            </div>
            
            <div id="message" class="alert alert-danger d-none"></div>
            
            <div class="table-container">
                <div id="table-container"></div>
            </div>
            
            <div class="action-buttons">
                <button id="download" class="btn btn-primary">Download Edited File</button>
                <button id="saveServer" class="btn btn-success ms-2">Save to Server</button>
                <a id="downloadLink" class="btn btn-link d-none" download>Download Saved File</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('excelFile').addEventListener('change', function (e) {
            const file = e.target.files[0];
            const messageBox = document.getElementById('message');
            const tableContainer = document.getElementById('table-container');
            messageBox.textContent = '';
            messageBox.classList.add('d-none');
            tableContainer.innerHTML = '';

            if (!file) {
                showMessage('No file selected.');
                return;
            }

            const reader = new FileReader();

            reader.onload = function (e) {
                try {
                    const data = e.target.result;
                    const workbook = XLSX.read(data, { type: 'binary' });

                    if (!workbook.SheetNames || workbook.SheetNames.length === 0) {
                        throw new Error("No sheets found in the Excel file.");
                    }

                    const sheetName = workbook.SheetNames[0];
                    const sheet = workbook.Sheets[sheetName];
                    const json = XLSX.utils.sheet_to_json(sheet, { header: 1 });

                    if (!json || json.length === 0) {
                        throw new Error("The selected sheet is empty.");
                    }

                    renderTable(json);

                } catch (error) {
                    showMessage('Error reading the Excel file: ' + error.message);
                    console.error("Excel Read Error:", error);
                }
            };

            reader.onerror = function () {
                showMessage('Failed to read the file.');
            };

            reader.readAsBinaryString(file);
        });

        function renderTable(data) {
            let tableHTML = '<table class="table table-bordered"><thead><tr>';
            
            // Create headers from first row
            if (data.length > 0) {
                data[0].forEach((cell, index) => {
                    tableHTML += `<th>${String.fromCharCode(65 + index)}</th>`;
                });
                tableHTML += '</tr></thead><tbody>';
            }

            // Create rows
            data.forEach((row = [], rowIndex) => {
                tableHTML += '<tr>';
                row.forEach((cell = '', cellIndex) => {
                    const cellValue = cell !== null && cell !== undefined ? cell : '';
                    tableHTML += `<td data-row="${rowIndex}" data-col="${cellIndex}" contenteditable="true">${cellValue}</td>`;
                });
                tableHTML += '</tr>';
            });
            
            tableHTML += '</tbody></table>';
            document.getElementById('table-container').innerHTML = tableHTML;
        }

        document.getElementById('download').addEventListener('click', function () {
            const table = document.querySelector('#table-container table');
            if (!table) {
                showMessage('No table to download. Please upload an Excel file first.');
                return;
            }

            const rows = getTableData();
            const worksheet = XLSX.utils.aoa_to_sheet(rows);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, 'EditedSheet');
            XLSX.writeFile(workbook, 'edited_file.xlsx');
        });

        document.getElementById('saveServer').addEventListener('click', function () {
            const table = document.querySelector('#table-container table');
            if (!table) {
                showMessage('No table to save. Please upload an Excel file first.');
                return;
            }

            const rows = getTableData();
            
            fetch('/save-excel', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ excel_data: JSON.stringify(rows) })
            })
            .then(response => response.json())
            .then(data => {
                if (data.download_url) {
                    const downloadLink = document.getElementById('downloadLink');
                    downloadLink.href = data.download_url;
                    downloadLink.classList.remove('d-none');
                    showMessage('File saved successfully!', 'success');
                }
            })
            .catch(error => {
                showMessage('Error saving file: ' + error.message);
            });
        });

        function getTableData() {
            const rows = [];
            const table = document.querySelector('#table-container table');
            const tableRows = table.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                const rowData = [];
                row.querySelectorAll('td').forEach(cell => {
                    rowData.push(cell.textContent);
                });
                rows.push(rowData);
            });
            
            return rows;
        }

        function showMessage(message, type = 'danger') {
            const messageBox = document.getElementById('message');
            messageBox.textContent = message;
            messageBox.classList.remove('d-none', 'alert-success', 'alert-danger');
            messageBox.classList.add(`alert-${type}`);
        }
    </script>
</body>
</html>