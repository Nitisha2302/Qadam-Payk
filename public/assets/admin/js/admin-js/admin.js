







// js for plan delete start 

// Show the custom delete popup
$(document).on('click', '.delete-plan-btn', function () {
    selectedUserId = $(this).data('user-id');
    console.log(selectedUserId)
    $('.delete-plan-confirmation-popup').fadeIn(); // Show the popup
    $('.delete-plan-confirmation-popup-delete-btn').data('user-id', selectedUserId); // Set user ID on confirm button
});
$(document).on('click', '.cancel-popup-btnbox', function () {
    $('.delete-plan-confirmation-popup').fadeOut();
});

