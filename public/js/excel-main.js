

var initialSheets = [];
var fileId = null;
var isImporting = false;


function initializeExcelV2(sheets, file_id, importing) {
    initialSheets = sheets || [];
    fileId = file_id;
    isImporting = importing || false;
    
    console.log('Initializing Excel V2 with:', {
        sheets: initialSheets,
        fileId: fileId,
        isImporting: isImporting
    });
}

$(document).ready(function() {
    
    // Global function to check if Luckysheet is ready
    function isLuckysheetReady() {
        return typeof luckysheet !== 'undefined' && luckysheet && typeof luckysheet.getAllSheets === 'function';
    }

    // Update header text based on import status
    function updateHeaderText() {
        const fileName = $('#fileNameInput').val().trim();
        if (isImporting) {
            $('#fileHeader').text('Import File V2');
        } else {
            $('#fileHeader').text(fileName ? `File Name V2: ${fileName}` : 'New Spreadsheet V2');
        }
    }

    // Lightweight toast (non-blocking) for small notifications
    function showToast(message, duration) {
        duration = duration || 1800;
        var toast = document.createElement('div');
        toast.className = 'sheet-toast';
        toast.textContent = message + ' (V2)';
        document.body.appendChild(toast);
        setTimeout(function(){ toast.classList.add('visible'); }, 10);
        setTimeout(function(){ toast.classList.remove('visible'); }, duration);
        setTimeout(function(){ if (toast && toast.parentNode) { toast.parentNode.removeChild(toast); } }, duration + 400);
    }

    // Convert 0-based column index to Excel-like letters (0->A, 25->Z, 26->AA)
    function columnIndexToLabel(index) {
        var label = '';
        var n = (index || 0) + 1;
        while (n > 0) {
            var rem = (n - 1) % 26;
            label = String.fromCharCode(65 + rem) + label;
            n = Math.floor((n - 1) / 26);
        }
        return label;
    }

    // Function to create a blank sheet
    function createBlankSheet(rows, cols) {
        rows = rows || 16; // Same as original
        cols = cols || 26;
        var blankData = [];
        for (var i = 0; i < rows; i++) {
            var row = [];
            for (var j = 0; j < cols; j++) {
                row.push({ v: "" });
            }
            blankData.push(row);
        }

        var rowlen = {};
        var columnlen = {};
        for (var k = 0; k < rows; k++) {
            rowlen[k] = 30;
        }
        for (var l = 0; l < cols; l++) {
            columnlen[l] = 200;
        }

        return {
            name: "Sheet1",
            data: blankData,
            config: {
                rowlen: rowlen,
                columnlen: columnlen
            },
            order: 0,
            status: 1,
            celldata: [],
            __isNew: true
        };
    }

    // Function to initialize Luckysheet with custom settings
    function initializeLuckysheet(sheets) {
        if (!Array.isArray(sheets) || sheets.length === 0) {
            console.warn('No valid sheets provided, creating a blank sheet');
            sheets = [createBlankSheet()];
        }

        // Format sheets data to include IDs if they exist
        var formattedSheets = [];
        $.each(sheets, function(index, sheet) {
            var hasCellData = $.isArray(sheet.celldata) && sheet.celldata.length > 0;
            var sheetData = {
                name: sheet.name,
                order: sheet.order,
                status: 1,
                config: sheet.config || { rowlen: {}, columnlen: {} },
            };
            if (hasCellData) {
                sheetData.celldata = sheet.celldata;
            } else {
                sheetData.data = $.isArray(sheet.data) ? sheet.data : (typeof sheet.data === 'string' ? JSON.parse(sheet.data) : []);
            }
            
            if (sheet.id) {
                sheetData.id = sheet.id;
            }
            
            // Store row IDs if they exist (for existing sheets loaded from database)
            if (sheet.rowIds && Array.isArray(sheet.rowIds)) {
                sheetData.__rowIds = sheet.rowIds;
                console.log('Loaded sheet "' + sheet.name + '" with rowIds:', sheet.rowIds);
            } else {
                console.log('Loaded sheet "' + sheet.name + '" without rowIds');
            }
            
            formattedSheets.push(sheetData);
        });

        luckysheet.destroy();
        luckysheet.create({
            container: 'luckysheet',
            data: formattedSheets,
            showinfobar: false,
            showtoolbar: true,
            showstatisticBar: false,
            showSheetBar: true,
            allowEdit: true,
            allowUpdate: true,
            enableAddRow: true,
            enableAddCol: true,
            enableContextmenu: true,
            showGridLines: true,
            allowUpdateWhenUnFocused: false,
            enableVersion: (function(){
                try { return $('#enableVersionHistory').is(':checked'); } catch(e) { return true; }
            })()
        });

        // Add custom context menu for sheet deletion
        luckysheet.setConfig({
            hook: {
                onToggleSheetMenu: function(menu) {
                    menu.push({
                        name: "Delete Sheet",
                        onclick: function() {
                            const index = luckysheet.getSheetIndex();
                            deleteSheet(index);
                        }
                    });
                    return menu;
                }
            }
        });
    }

    // Function to handle sheet deletion
    function deleteSheet(sheetIndex) {
        var allSheets = luckysheet.getAllSheets();
        var sheetToDelete = allSheets[sheetIndex];
        
        if (!confirm('Are you sure you want to delete "' + sheetToDelete.name + '"?')) {
            return;
        }

        // If the sheet exists in the database (has an ID), send delete request
        if (sheetToDelete.id && fileId) {
            $.ajax({
                url: '/sheetV2/sheets/' + sheetToDelete.id,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Remove the sheet from Luckysheet
                    luckysheet.deleteSheet(sheetIndex);
                    alert(response.message);
                },
                error: function(xhr) {
                    var errorMsg = 'Failed to delete sheet';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg += ': ' + xhr.responseJSON.message;
                    } else if (xhr.statusText) {
                        errorMsg += ': ' + xhr.statusText;
                    }
                    alert(errorMsg);
                }
            });
        } else {
            // For new sheets not yet saved to database
            luckysheet.deleteSheet(sheetIndex);
        }
    }

    // Function to start Luckysheet initialization
    function startLuckysheetInitialization() {
        console.log('Initializing Luckysheet V2...');
        initializeLuckysheet(initialSheets);
        updateHeaderText();
        
        // Initialize Luckysheet event handlers after a delay
        setTimeout(function() {
            console.log('Attempting to initialize Luckysheet V2 events...');
            initializeLuckysheetEvents();
            initializeSheetSizeCheck();
        }, 1000);
        
        // Check initial sheet size
        setTimeout(function() {
            console.log('Checking initial sheet size V2...');
            checkSheetSize();
        }, 1500);
    }

    // Add new sheet button handler
    $('#addNewSheetBtn').on('click', function() {
        if (!isLuckysheetReady()) {
            alert('Please wait for the sheet to load completely before adding a new sheet.');
            return;
        }
        window.sheetCount = window.sheetCount || initialSheets.length;
        window.sheetCount++;

        // Get current sheets with their data preserved
        var currentSheets = luckysheet.getAllSheets();
        var existingNames = [];
        $.each(currentSheets, function(index, sheet) {
            existingNames.push(sheet.name.toLowerCase());
        });
        var newSheetName = 'Sheet' + window.sheetCount;

        while ($.inArray(newSheetName.toLowerCase(), existingNames) !== -1) {
            window.sheetCount++;
            newSheetName = 'Sheet' + window.sheetCount;
        }

        var blankRows = 16;
        var blankCols = 26;
        var newSheetData = [];
        for (var i = 0; i < blankRows; i++) {
            var row = [];
            for (var j = 0; j < blankCols; j++) {
                row.push({ v: "" });
            }
            newSheetData.push(row);
        }

        var rowlen = {};
        var columnlen = {};
        for (var k = 0; k < blankRows; k++) {
            rowlen[k] = 30;
        }
        for (var l = 0; l < blankCols; l++) {
            columnlen[l] = 200;
        }

        var newSheet = {
            name: newSheetName,
            data: newSheetData,
            config: {
                rowlen: rowlen,
                columnlen: columnlen
            },
            order: currentSheets.length,
            status: 1,
            celldata: [],
            __isNew: true
        };

        // Add the new sheet to the current sheets array
        currentSheets.push(newSheet);
        
        // Reinitialize with all sheets (preserving existing data)
        initializeLuckysheet(currentSheets);
        
        // Switch to the new sheet after a small delay to ensure initialization is complete
        setTimeout(function() {
            luckysheet.setSheetActive(currentSheets.length - 1);
        }, 100);
    });

    function buildSavePayload() {
        var allSheets = luckysheet.getAllSheets();
        var fileName = $('#fileNameInput').val().trim() || 'sheet_' + new Date().toISOString().slice(0,10);
        var sheets = [];
        
        $.each(allSheets, function(index, sheet) {
            var data2D = sheet.data;
            
            if (!Array.isArray(data2D) || data2D.length === 0) {
                // Fallback: build from celldata if present
                if (Array.isArray(sheet.celldata) && sheet.celldata.length > 0) {
                    var maxRow = 0, maxCol = 0;
                    sheet.celldata.forEach(function(cell){
                        if (typeof cell.r === 'number' && typeof cell.c === 'number') {
                            if (cell.r > maxRow) maxRow = cell.r;
                            if (cell.c > maxCol) maxCol = cell.c;
                        }
                    });
                    data2D = [];
                    for (var r = 0; r <= maxRow; r++) {
                        var row = [];
                        for (var c = 0; c <= maxCol; c++) { row.push({ v: "" }); }
                        data2D.push(row);
                    }
                    sheet.celldata.forEach(function(cell){
                        if (data2D[cell.r] && data2D[cell.r][cell.c]) {
                            data2D[cell.r][cell.c] = cell.v || { v: "" };
                        }
                    });
                } else {
                    data2D = [];
                }
            }
            
            // Build row updates array - include ALL rows with data or modifications
            var rowUpdates = [];
            
            if (Array.isArray(data2D)) {
                data2D.forEach(function(row, rowIndex) {
                    var rowId = null;
                    var hasData = false;
                    var isModified = false;
                    
                    // Check if this row was marked as modified
                    if (sheet.__modifiedRows && sheet.__modifiedRows[rowIndex]) {
                        isModified = true;
                    }
                    
                    // Check if row has any data
                    if (Array.isArray(row)) {
                        row.forEach(function(cell, colIndex) {
                            // Check if cell has actual content
                            if (cell && typeof cell === 'object' && cell.v && cell.v !== '') {
                                hasData = true;
                            }
                        });
                    }
                    
                    // For existing sheets, try to get rowId from the original data
                    // This should be stored when the sheet is loaded from the database
                    if (sheet.__rowIds && sheet.__rowIds[rowIndex]) {
                        rowId = sheet.__rowIds[rowIndex];
                    }
                    
                    // Debug: Log row ID tracking
                    if (rowIndex < 5) { // Only log first 5 rows to avoid spam
                        console.log('Row ' + rowIndex + ' - rowId:', rowId, 'hasData:', hasData, 'isModified:', isModified, 'sheet.__rowIds:', sheet.__rowIds);
                    }
                    
                    // Include ALL rows that have data OR are modified OR have a rowId
                    // This ensures we capture all changes properly
                    if (hasData || isModified || rowId !== null) {
                        rowUpdates.push({
                            rowIndex: rowIndex,
                            rowId: rowId,
                            data: row,
                            modified: isModified
                        });
                    }
                });
            }

            sheets.push({
                name: sheet.name,
                data: JSON.stringify(data2D),
                config: JSON.stringify(sheet.config || {}),
                celldata: JSON.stringify(sheet.celldata || []),
                order: sheet.order,
                id: sheet.id || null,
                rowUpdates: rowUpdates
            });
        });
        
        var versionHistoryEnabled = $('#enableVersionHistory').is(':checked');
        console.log('Version history enabled:', versionHistoryEnabled);
        console.log('Checkbox state:', $('#enableVersionHistory').prop('checked'));
        console.log('Payload sheets:', sheets);
        
        return {
            name: fileName,
            sheets: sheets,
            file_id: fileId || null,
            enable_version_history: versionHistoryEnabled
            
        };
    }

    function saveNow(isAuto) {
        isAuto = isAuto || false;
        
        // Show progress indicator for manual saves
        if (!isAuto) {
            $('#saveSheetBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            $('#saveProgress').show();
            updateSaveProgress(0, 'Preparing data...');
        }
        
        var payload = buildSavePayload();
        
        // Debug: Log the payload structure
        console.log('Save payload V2:', payload);
        console.log('Row updates for first sheet:', payload.sheets[0]?.rowUpdates);
        
        // Check if payload is too large and needs chunking
        var payloadSize = JSON.stringify(payload).length;
        var maxPayloadSize = 10000000; // Increased to 10MB limit
        
        if (payloadSize > maxPayloadSize) {
            // Save in chunks
            saveInChunks(payload, isAuto);
            return;
        }
        
        // Regular save with increased timeout
        performSave(payload, isAuto);
    }
    
    function saveInChunks(payload, isAuto) {
        var sheets = payload.sheets;
        var totalSheets = sheets.length;
        var currentSheet = 0;
        var savedSheets = [];
        
        function saveNextChunk() {
            if (currentSheet >= totalSheets) {
                // All chunks saved, finalize
                finalizeChunkedSave(savedSheets, isAuto);
                return;
            }
            
            var progress = Math.round((currentSheet / totalSheets) * 100);
            updateSaveProgress(progress, `Saving sheet ${currentSheet + 1} of ${totalSheets}...`);
            
            var chunkPayload = {
                name: payload.name,
                sheets: [sheets[currentSheet]],
                file_id: payload.file_id,
                isChunked: true,
                chunkIndex: currentSheet,
                totalChunks: totalSheets
            };
            
            performSave(chunkPayload, true, function(response) {
                if (response.sheets && response.sheets[0]) {
                    savedSheets.push(response.sheets[0]);
                }
                currentSheet++;
                saveNextChunk();
            });
        }
        
        saveNextChunk();
    }
    
    function performSave(payload, isAuto, callback) {
        // This will be set dynamically by the blade template
        var saveUrl = window.EXCEL_SAVE_URL || '/sheetV2/save-sheets';
        
        $.ajax({
            url: saveUrl,
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            timeout: 120000, // Increased to 2 minutes timeout
            success: function(response) {
                if (callback) {
                    callback(response);
                } else {
                    handleSaveSuccess(response, isAuto);
                }
            },
            error: function(xhr) {
                handleSaveError(xhr, isAuto);
            }
        });
    }
    
    function handleSaveSuccess(response, isAuto) {
        if (!isAuto) {
            $('#saveSheetBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save Data');
            $('#saveProgress').hide();
            alert('Data saved successfully! (V2)');
        }
        
        isImporting = false;
        updateHeaderText();
        
        if (!fileId && response.file_id) {
            fileId = response.file_id;
            window.history.replaceState({}, '', '/sheetV2/excel-preview/' + response.file_id);
        }
        
        if (response.sheets) {
            var updatedSheets = [];
            var allSheets = luckysheet.getAllSheets();
            $.each(allSheets, function(index, sheet) {
                if (sheet.__isNew && response.sheets[index]) {
                    sheet.id = response.sheets[index].id;
                    delete sheet.__isNew;
                }
                delete sheet.__modified;
                delete sheet.__modifiedRows;
                
                // Preserve row IDs if they exist
                if (sheet.__rowIds) {
                    // Keep existing row IDs for tracking
                }
                
                updatedSheets.push(sheet);
            });
            initializeLuckysheet(updatedSheets);
        }

        // Refresh the page shortly after a successful save (exactly like original)
        try {
            setTimeout(function(){ location.reload(); }, 500);
        } catch (e) {}
    }
    
    function handleSaveError(xhr, isAuto) {
        if (!isAuto) {
            $('#saveSheetBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save Data');
            $('#saveProgress').hide();
        }
        
        var msg = 'Save failed (V2)';
        if (xhr.responseJSON && xhr.responseJSON.message) {
            msg += ': ' + xhr.responseJSON.message;
        } else if (xhr.statusText) {
            msg += ': ' + xhr.statusText;
        } else if (xhr.status === 0) {
            msg += ': Request timeout or connection lost';
        }
        alert(msg);
    }
    
    function finalizeChunkedSave(savedSheets, isAuto) {
        if (!isAuto) {
            $('#saveSheetBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save Data');
            $('#saveProgress').hide();
            alert('Large file saved successfully in chunks! (V2)');
        }
        
        // Update sheet IDs and clear modification flags
        var allSheets = luckysheet.getAllSheets();
        $.each(allSheets, function(index, sheet) {
            if (sheet.__isNew) {
                var savedSheet = savedSheets.find(s => s.name === sheet.name);
                if (savedSheet) {
                    sheet.id = savedSheet.id;
                    delete sheet.__isNew;
                }
            }
            delete sheet.__modified;
            delete sheet.__modifiedRows;
            
            // Preserve row IDs if they exist
            if (sheet.__rowIds) {
                // Keep existing row IDs for tracking
            }
        });
        
        isImporting = false;
        updateHeaderText();

        // Ensure a full reload after chunked save to reflect latest data (exactly like original)
        try {
            setTimeout(function(){ location.reload(); }, 500);
        } catch (e) {}
    }
    
    function updateSaveProgress(percentage, text) {
        $('#saveProgressBar').css('width', percentage + '%');
        $('#saveProgressText').text(text);
    }

    var autoSaveTimer = null;
    function scheduleAutoSave() {
        if (autoSaveTimer) clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() { saveNow(true); }, 1500);
    }

    // Save data button handler
    $('#saveSheetBtn').on('click', function() { 
        if (!isLuckysheetReady()) {
            alert('Please wait for the sheet to load completely before saving.');
            return;
        }
        saveNow(false); 
    });

    // Function to initialize Luckysheet event handlers
    function initializeLuckysheetEvents() {
        if (isLuckysheetReady() && typeof luckysheet.on === 'function') {
            var markModified = function(rowIndex) {
                var allSheets = luckysheet.getAllSheets();
                var activeSheetIndex = luckysheet.getActiveSheetIndex();
                var activeSheet = allSheets[activeSheetIndex];
                
                // Mark the sheet as modified
                activeSheet.__modified = true;
                
                // Mark the specific row as modified
                if (!activeSheet.__modifiedRows) {
                    activeSheet.__modifiedRows = {};
                }
                if (rowIndex !== undefined) {
                    activeSheet.__modifiedRows[rowIndex] = true;
                }
                
                scheduleAutoSave();
            };

            luckysheet.on('cellEdited', function(payload) {
                try {
                    if (payload && payload.r !== undefined) {
                        markModified(payload.r);
                        // Log full data to console (not via alert) for debugging
                        try {
                            var allSheetsData = luckysheet.getAllSheets();
                            console.log('Sheet data after edit (V2):', allSheetsData);
                        } catch (e) {}

                        // Show concise toast for meaningful value changes only
                        var row = payload.r;
                        var col = payload.c;
                        var value = (payload && payload.v !== undefined) ? payload.v : (payload && payload.value !== undefined ? payload.value : '');
                        if (value !== undefined && value !== null && value !== '') {
                            var cellRef = columnIndexToLabel(col) + (row + 1);
                            showToast('Cell ' + cellRef + ' updated to ' + value);
                        }
                    } else {
                        markModified();
                    }
                } catch(e) {}
            });
            luckysheet.on('updated', function(operate) {
                try {
                    markModified();
                } catch (e) {}
            });
            luckysheet.on('cellMousedown', function() {});
            
            // Hook common toolbar actions that affect formatting via keydown already
            console.log('Luckysheet V2 events initialized successfully');
        } else {
            console.log('Luckysheet V2 not ready yet, retrying in 500ms...');
            setTimeout(initializeLuckysheetEvents, 500);
        }
    }

    // Keyboard shortcuts: Ctrl/Cmd+S to save, and autosave on common edit keys
    $(document).on('keydown', function(e) {
        var key = (e.key || '').toLowerCase();
        var ctrl = e.ctrlKey || e.metaKey;
        if (ctrl && key === 's') {
            e.preventDefault();
            saveNow(false);
            return;
        }
        // Bold/Italic/Underline, Undo/Redo
        if (ctrl && $.inArray(key, ['b','i','u','z','y']) !== -1) {
            scheduleAutoSave();
        }
        // Delete/Backspace edits
        if (key === 'delete' || key === 'backspace') {
            scheduleAutoSave();
        }
        // Enter/Tab often finalize edits
        if (key === 'enter' || key === 'tab') {
            scheduleAutoSave();
        }
    });

    // Export button handler
    $('#exportBtn').on('click', function() {
        if (!isLuckysheetReady()) {
            alert('Please wait for the sheet to load completely before exporting.');
            return;
        }
        
        var allSheets = luckysheet.getAllSheets();
        var wb = XLSX.utils.book_new();
        
        // Process each sheet
        $.each(allSheets, function(index, sheet) {
            // Get the sheet data and convert to 2D array
            var sheetData = [];
            $.each(sheet.data, function(rowIndex, row) {
                var newRow = [];
                if (row) {
                    $.each(row, function(cellIndex, cell) {
                        newRow.push(cell && cell.v ? cell.v : "");
                    });
                }
                sheetData.push(newRow);
            });
            
            // Create worksheet and add to workbook
            var ws = XLSX.utils.aoa_to_sheet(sheetData);
            XLSX.utils.book_append_sheet(wb, ws, sheet.name || 'Sheet' + (sheet.order + 1));
        });
        
        // Generate file name and download
        var fileName = $('#fileNameInput').val().trim() || 'export_v2';
        XLSX.writeFile(wb, fileName + '.xlsx');
    });

    // Version History button handler
    $('#versionHistoryBtn').on('click', function() {
        if (!fileId) {
            alert('Please save your file first to view version history.');
            return;
        }
        
        if (!isLuckysheetReady()) {
            alert('Please wait for the sheet to load completely before viewing version history.');
            return;
        }
        
        showVersionHistory();
    });

    // Auto-disable version history for large sheets
    function checkSheetSize() {
        if (!isLuckysheetReady()) {
            console.log('Luckysheet V2 not ready for sheet size check');
            return;
        }
        
        var allSheets = luckysheet.getAllSheets();
        var totalRows = 0;
        
        allSheets.forEach(function(sheet) {
            if (sheet.data && Array.isArray(sheet.data)) {
                totalRows += sheet.data.length;
            }
        });
        
        if (totalRows > 1000) {
            $('#enableVersionHistory').prop('checked', false).prop('disabled', true);
            $('#versionHistoryInfo').text('(Disabled - Sheet too large: ' + totalRows + ' rows)').addClass('text-warning');
        } else {
            $('#enableVersionHistory').prop('disabled', false);
            $('#versionHistoryInfo').text('(Auto-disabled for large sheets)').removeClass('text-warning');
        }
    }

    // Check sheet size when data changes
    function initializeSheetSizeCheck() {
        if (isLuckysheetReady() && typeof luckysheet.on === 'function') {
            luckysheet.on('updated', function() {
                checkSheetSize();
            });
            console.log('Sheet size check V2 initialized successfully');
        } else {
            console.log('Luckysheet V2 not ready for sheet size check, retrying in 500ms...');
            setTimeout(initializeSheetSizeCheck, 500);
        }
    }

    // Function to show version history
    function showVersionHistory() {
        $('#versionHistoryContent').html('<p class="text-muted">Loading version history...</p>');
        
        // Show modal using Bootstrap 5 syntax
        var modalElement = document.getElementById('versionHistoryModal');
        if (!modalElement) {
            alert('Modal element not found');
            return;
        }
        
        try {
            var modal = new bootstrap.Modal(modalElement);
            modal.show();
            
            // Store modal reference for later use
            window.currentModal = modal;
        } catch (e) {
            console.error('Error showing modal:', e);
            // Fallback to jQuery modal
            $('#versionHistoryModal').modal('show');
        }
        
        // Get the active sheet reliably
        var activeSheetIndexValue = null; // Luckysheet's internal index value
        try {
            if (typeof luckysheet.getActiveSheetIndex === 'function') {
                activeSheetIndexValue = luckysheet.getActiveSheetIndex();
            } else if (typeof luckysheet.getActiveSheet === 'function') {
                var tmpActive = luckysheet.getActiveSheet();
                if (tmpActive && tmpActive.index !== undefined) {
                    activeSheetIndexValue = tmpActive.index;
                }
            }
        } catch (e) {
            console.log('Error getting active sheet index:', e);
        }

        var allSheets = luckysheet.getAllSheets() || [];
        var activeSheet = null;
        // Prefer the tab with status === 1 (Luckysheet marks active tab this way)
        activeSheet = allSheets.find(function(s){ return s && s.status === 1; }) || null;
        if (!activeSheet && activeSheetIndexValue !== null) {
            // Fallback to matching internal index value (coerce types for safety)
            activeSheet = allSheets.find(function(s){
                if (!s || typeof s.index === 'undefined') return false;
                var si = Number(s.index);
                var ai = Number(activeSheetIndexValue);
                return !isNaN(si) && !isNaN(ai) && si === ai;
            }) || null;
        }
        if (!activeSheet) {
            // Fallback: use currently focused position or first sheet
            activeSheet = allSheets[0] || null;
        }
        console.log('Resolved active sheet V2:', activeSheet);
        
        if (!activeSheet || !activeSheet.id) {
            console.log('No active sheet or sheet ID found');
            $('#versionHistoryContent').html('<p class="text-danger">No sheet selected or sheet not saved yet. Please save your sheet first.</p>');
            return;
        }
        
        // Store active sheet id/name globally for revert actions and labeling
        window.__activeSheetId = activeSheet.id;
        window.__activeSheetName = activeSheet.name;

        // Fetch version history for the active sheet
        $.ajax({
            url: '/sheetV2/sheet/' + activeSheet.id + '/versions',
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Version history response V2:', response);
                if (response.version_history && response.version_history.length > 0) {
                    console.log('First row history:', response.version_history[0]);
                    if (response.version_history[0].versions && response.version_history[0].versions.length > 0) {
                        console.log('First version data:', response.version_history[0].versions[0]);
                        console.log('Data type:', typeof response.version_history[0].versions[0].sheet_data);
                        console.log('Data content:', response.version_history[0].versions[0].sheet_data);
                    }
                }
                displayVersionHistory(response);
            },
            error: function(xhr) {
                $('#versionHistoryContent').html('<p class="text-danger">Failed to load version history: ' + (xhr.responseJSON?.message || 'Unknown error') + '</p>');
            }
        });
    }

    // Add keyboard event handler for modal
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#versionHistoryModal').hasClass('show')) {
            closeVersionHistoryModal();
        }
    });

    // Add click outside modal to close functionality
    $('#versionHistoryModal').on('click', function(e) {
        if (e.target === this) {
            closeVersionHistoryModal();
        }
    });

    // Function to display version history
    function displayVersionHistory(data) {
        console.log('Displaying version history V2:', data);
        var html = '<div class="version-history">';
        var sheetLabel = (typeof window.__activeSheetName !== 'undefined' && window.__activeSheetName) ? window.__activeSheetName : (data.sheet_name || 'Unknown');
        html += '<h6>Sheet V2: ' + sheetLabel + '</h6>';
        
        // Sheet-level history table (version, is_current, timestamp)
        if (data.sheet_history && data.sheet_history.length) {
            html += '<div class="table-responsive mb-3">';
            html += '<table class="table table-sm table-bordered">';
            html += '<thead><tr><th>Version</th><th>Status</th><th>Timestamp</th><th style="width:120px;">Action</th></tr></thead><tbody>';
            var sortedHistory = (data.sheet_history || []).slice().sort(function(a, b) {
                var va = (a.version ?? 0);
                var vb = (b.version ?? 0);
                return vb - va; // version desc (latest first)
            });
            
            // Find the latest version (first in sorted array)
            var latestVersion = sortedHistory.length > 0 ? sortedHistory[0].version : null;
            
            sortedHistory.forEach(function(item, idx){
                var isCurrentNum = ((String(item.is_current) === '1') || (item.is_current === 1) || (item.is_current === true)) ? 1 : 0;
                var isCurrent = isCurrentNum === 1;
                var isLatest = item.version === latestVersion;
                
                html += '<tr>';
                html += '<td>' + (item.version ?? 'N/A') + '</td>';
                html += '<td>' + isCurrentNum + '</td>';
                html += '<td>' + formatDateTime(item.created_at) + '</td>';
                html += '<td>';
                
                // Show Revert button only if:
                // 1. Not current version (is_current = 0)
                // 2. Version number is valid
                if (item.version != null && window.__activeSheetId && !isCurrent) {
                    html += '<button class="btn btn-sm btn-outline-danger revert-btn" title="Revert to version ' + item.version + '" onclick="revertVersionClick(this,' + (item.version) + ')">Revert</button>';
                } else {
                    var reason = '';
                    if (isCurrent) {
                        reason = 'Current version';
                    } else {
                        reason = 'Invalid version';
                    }
                    html += '<span class="text-muted small">' + reason + '</span>';
                }
                html += '</td>';
                html += '</tr>';
            });
            html += '</tbody></table>';
            html += '</div>';
        }
        
        if (data.version_history && data.version_history.length > 0) {
            console.log('Found ' + data.version_history.length + ' rows with version history');
            // Detailed Version History removed per request
        } else {
            html += '<p class="text-muted">No version history available for this sheet.</p>';
        }
        
        html += '</div>';
        $('#versionHistoryContent').html(html);
    }

    // Update header when file name changes
    $('#fileNameInput').on('input', function() {
        isImporting = false;
        updateHeaderText();
    });

    // Expose the start function globally
    window.startLuckysheetInitialization = startLuckysheetInitialization;
    
    // Helper functions for version history
    window.formatDateTime = function(dateString) {
        if (!dateString) return 'N/A';
        try {
            var date = new Date(dateString);
            if (isNaN(date.getTime())) return dateString;
            return date.toLocaleString(undefined, {
                year: 'numeric', month: 'short', day: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            });
        } catch (e) {
            return dateString;
        }
    };

    window.revertVersionClick = function(btnEl, versionNumber) {
        try {
            if (!window.__activeSheetId) { 
                alert('No active sheet.'); 
                return; 
            }

            // Perform revert
            revertSheetVersion(window.__activeSheetId, versionNumber, {
                onSuccess: function(response) {
                    console.log('Revert successful:', response);
                    
                    // Mark applied button and re-enable others
                    var buttons = document.querySelectorAll('.version-history button.revert-btn');
                    buttons.forEach(function(b){
                        b.disabled = false;
                        b.textContent = 'Revert';
                        b.classList.remove('btn-success');
                        b.classList.add('btn-outline-danger');
                    });
                    
                    btnEl.disabled = true;
                    btnEl.textContent = 'Revert';
                    btnEl.classList.remove('btn-outline-danger');
                    btnEl.classList.add('btn-success');
                    
                    // Show success message
                    setTimeout(function(){
                        alert('Sheet reverted to version ' + versionNumber + ' successfully.');
                        location.reload();
                    }, 500);
                },
                onError: function(errorMsg, xhr) {
                    console.error('Revert failed:', errorMsg);
                    alert(errorMsg);
                }
            });
        } catch (err) {
            console.error('revertVersionClick error:', err);
            alert('An error occurred during revert.');
        }
    };

    // Function to revert sheet version (matches original implementation)
    window.revertSheetVersion = function(sheetId, versionNumber, handlers) {
        if (!sheetId || versionNumber === null || versionNumber === undefined) {
            alert('Invalid version selection.');
            return;
        }

        if (!confirm('Revert this sheet to version ' + versionNumber + '? This will overwrite current rows.')) {
            return;
        }

        $.ajax({
            url: '/sheetV2/sheet/' + sheetId + '/restore/' + versionNumber,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (handlers && typeof handlers.onSuccess === 'function') {
                    try { handlers.onSuccess(response); } catch (e) {
                        console.error('Handler error:', e);
                    }
                } else {
                    alert('Sheet reverted to version ' + versionNumber + ' successfully.');
                    // Refresh version history and sheet content
                    if (typeof showVersionHistory === 'function') {
                        showVersionHistory();
                    }
                    // Reload all sheets for the current file to reflect restored content
                    if (typeof fileId !== 'undefined' && fileId) {
                        $.getJSON('/sheetV2/sheets/' + fileId, function(payload) {
                            if (payload && payload.sheets) {
                                initializeLuckysheet(payload.sheets);
                            }
                        }).fail(function() {
                            console.error('Failed to reload sheets after revert');
                            location.reload();
                        });
                    }
                }
            },
            error: function(xhr) {
                var errorMsg = 'Failed to revert sheet';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg += ': ' + xhr.responseJSON.message;
                }
                if (handlers && typeof handlers.onError === 'function') {
                    try { handlers.onError(errorMsg, xhr); } catch (e) {}
                } else {
                    alert(errorMsg);
                }
            }
        });
    };

    window.closeVersionHistoryModal = function() {
        try {
            if (window.currentModal && typeof window.currentModal.hide === 'function') {
                window.currentModal.hide();
            } else {
                $('#versionHistoryModal').modal('hide');
            }
        } catch (e) {
            console.error('Error closing modal:', e);
        }
    };
    
});
