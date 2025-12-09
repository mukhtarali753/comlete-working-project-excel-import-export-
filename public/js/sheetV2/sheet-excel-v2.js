// Sheet Excel V2 JavaScript
console.log('Sheet Excel V2 loaded');

$(document).ready(function() {
    
    $('.card-header').addClass('sheet-v2-header');
    
  
    $('#fileHeader').append('<span class="v2-indicator">V2</span>');
    
   
    window.showToastV2 = function(message, duration) {
        duration = duration || 2000;
        var toast = document.createElement('div');
        toast.className = 'sheet-toast sheet-toast-v2';
        toast.textContent = message + ' (V2)';
        document.body.appendChild(toast);
        setTimeout(function(){ toast.classList.add('visible'); }, 10);
        setTimeout(function(){ toast.classList.remove('visible'); }, duration);
        setTimeout(function(){ if (toast && toast.parentNode) { toast.parentNode.removeChild(toast); } }, duration + 400);
    };
    
    console.log('Sheet Excel V2 initialized');
});































