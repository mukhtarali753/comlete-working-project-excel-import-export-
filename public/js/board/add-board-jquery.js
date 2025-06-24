
$(document).ready(function () {
    $('#addStageModal').on('show.bs.modal', function () {
      
        $('#leaveForm')[0].reset();

        
        let stageRows = $('#stageContainer .stage-row');
        if (stageRows.length > 1) {
            stageRows.slice(1).remove();
        }

        stageRows.first().find('input').val('');
    });
});


