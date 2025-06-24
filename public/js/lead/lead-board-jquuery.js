

// // $(document).on('click', '.edit-lead-record', function () {
// //     let id = $(this).data('id');
// //     let name = $(this).data('name');
// //     let board_id = $(this).data('board_id');
// //     let stage_id = $(this).data('stage_id');

// //     $('#editLeadForm').attr('action', '/leads/' + id);
// //     $('#editLeadName').val(name);
// //     $('#editBoardId').val(board_id);
// //     $('#editStageId').val(stage_id);
// // });




// //     $(function () {
// //         $('.edit-lead-record').click(function(){
// //             // $('#createLeadModal').modal('show');
// //             // 4
            
// //         });
// //         // Show selected board stages
// //         function filterCards(boardId) {
// //             $(".board-card").hide();
// //             $(`.board-card[data-board-id="${boardId}"]`).show();
// //         }

// //         const boardSelect = $('#board_id');
// //         if (boardSelect.val()) {
// //             filterCards(boardSelect.val());
// //         }

// //         boardSelect.on('change', function () {
// //             filterCards(this.value);
// //         });

// //         // Modal hidden inputs
// //         $(document).on('click', '.open-create-modal', function () {
// //             $('#modal_board_id').val($(this).data('board-id'));
// //             $('#modal_stage_id').val($(this).data('stage-id'));
// //         });

// //         // Make leads draggable
// //         $(".sortable-leads").sortable({
// //             connectWith: ".sortable-leads",
// //             placeholder: "lead-placeholder",
// //             items: ".lead-subtext",
// //             forcePlaceholderSize: true,
// //             stop: function (event, ui) {
// //                 const leadId = ui.item.data("lead-id");
// //                 const newStageId = ui.item.closest(".sortable-leads").data("stage-id");

// //                 // AJAX call to update stage
// //                 $.ajax({
// //                     url: "{{ route('leads.update-stage') }}",
// //                     method: "POST",
// //                     data: {
// //                         _token: "{{ csrf_token() }}",
// //                         lead_id: leadId,
// //                         stage_id: newStageId
// //                     },
// //                     success: function () {
// //                         console.log("Lead moved successfully.");
// //                     },
// //                     // error: function () {
// //                     //     alert("Failed to update lead stage.");
// //                     // }
// //                 });
// //             }
// //         }).disableSelection();
// //     });

// //     //  $(document).on('click', '#addStageBtn', function () {
// //     //     $('#stageContainer').append(`@include('board.stage-row')`);
// //     // });
  
// $(function () {
//     function filterCards(boardId) {
//         $(".board-card").hide();
//         $(`.board-card[data-board-id="${boardId}"]`).show();
//     }

//     const boardSelect = $('#board_id');
//     if (boardSelect.val()) {
//         filterCards(boardSelect.val());
//     }

//     boardSelect.on('change', function () {
//         filterCards(this.value);
//     });

//     $(document).on('click', '.open-create-modal', function () {
//         $('#modal_board_id').val($(this).data('board-id'));
//         $('#modal_stage_id').val($(this).data('stage-id'));
//     });

   

//     $(".sortable-leads").sortable({
//         connectWith: ".sortable-leads",
//         placeholder: "lead-placeholder",
//         items: ".lead-subtext",
//         forcePlaceholderSize: true,
//         stop: function (event, ui) {
//             const leadId = ui.item.data("lead-id");
//             const newStageId = ui.item.closest(".sortable-leads").data("stage-id");

//             $.ajax({
//                 url: "{{ route('leads.update-stage') }}",
//                 method: "POST",
//                 data: {
//                     _token: "{{ csrf_token() }}",
//                     lead_id: leadId,
//                     stage_id: newStageId
//                 },
//                 success: function () {
//                     console.log("Lead moved successfully.");
//                 },
//                 error: function () {
//                     alert("Failed to update lead stage.");
//                 }
//             });
//         }
//     }).disableSelection();
// });

// document.addEventListener("DOMContentLoaded", function () {
//     const boardSelect = document.getElementById("board_id_select");
//     const stageSelect = document.getElementById("stage_id_select");
//     const modalBoardId = document.getElementById("modal_board_id");
//     const showBoardId = document.getElementById("show_board_id");
//     const showStageId = document.getElementById("show_stage_id");

//     boardSelect.addEventListener("change", function () {
//         const selectedOption = this.options[this.selectedIndex];
//         const boardId = selectedOption.value;
//         const stages = JSON.parse(selectedOption.getAttribute("data-stages") || "[]");

//         modalBoardId.value = boardId;
//         showBoardId.textContent = boardId;

//         stageSelect.innerHTML = '<option value="">Select Stage</option>';

//         if (stages.length > 0) {
//             stages.forEach(function (stage) {
//                 const option = document.createElement("option");
//                 option.value = stage.id;
//                 option.textContent = stage.name;
//                 stageSelect.appendChild(option);
//             });
//             stageSelect.value = stages[0].id;
//             showStageId.textContent = stages[0].id;
//         } else {
//             showStageId.textContent = "N/A";
//         }
//     });

//     stageSelect.addEventListener("change", function () {
//         showStageId.textContent = this.value || "N/A";
//     });
// });

   

