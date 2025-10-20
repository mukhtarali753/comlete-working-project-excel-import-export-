// Luckysheet UMD JS
// This file should be replaced with the actual luckysheet.umd.js from Luckysheet
// Download from: https://cdn.jsdelivr.net/npm/luckysheet@2.1.13/dist/luckysheet.umd.js

console.log('Luckysheet UMD loaded - replace with actual file');

// Placeholder luckysheet object to prevent errors during development
if (typeof luckysheet === 'undefined') {
    window.luckysheet = {
        create: function(options) {
            console.log('Placeholder luckysheet.create called with:', options);
        },
        destroy: function() {
            console.log('Placeholder luckysheet.destroy called');
        },
        getAllSheets: function() {
            console.log('Placeholder luckysheet.getAllSheets called');
            return [];
        },
        getActiveSheetIndex: function() {
            console.log('Placeholder luckysheet.getActiveSheetIndex called');
            return 0;
        },
        on: function(event, callback) {
            console.log('Placeholder luckysheet.on called for:', event);
        },
        setConfig: function(config) {
            console.log('Placeholder luckysheet.setConfig called with:', config);
        },
        deleteSheet: function(index) {
            console.log('Placeholder luckysheet.deleteSheet called for index:', index);
        },
        setSheetActive: function(index) {
            console.log('Placeholder luckysheet.setSheetActive called for index:', index);
        }
    };
}













