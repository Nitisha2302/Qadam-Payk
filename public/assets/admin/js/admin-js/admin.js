
let selectedUserId = null;

// Show the custom delete popup
$(document).on('click', '.delete-user-btn', function () {
    selectedUserId = $(this).data('user-id');
    console.log(selectedUserId)
    $('.delete-user-confirmation-popup').fadeIn(); // Show the popup
    $('.delete-user-confirmation-popup-delete-btn').data('user-id', selectedUserId); // Set user ID on confirm button
});

// Hide popup on cancel or close icon
$(document).on('click', '.cancel-popup-btnbox', function () {
    $('.delete-user-confirmation-popup').fadeOut();
});

// Delete user on confirm
$(document).on('click', '.delete-user-confirmation-popup-delete-btn', function () {
    let userId = $(this).data('user-id');

    $.ajax({
        url: deleteUserUrl,
        type: 'DELETE',
        data: {
            user_id: userId,
            _token: csrfToken
        },
        success: function (response) {
            let $msgBox = $('#notificationMessage');
            $('.delete-user-confirmation-popup').fadeOut(); // Hide popup

            if (response.success) {
                // Optionally remove user row or reload
                location.reload(); 
            } else {
                $msgBox
                    .removeClass('d-none alert-success')
                    .addClass('alert-danger')
                    .text(response.message || 'Could not delete user.');
            }

            // Auto-hide message
            setTimeout(() => {
                $msgBox.addClass('d-none').text('');
            }, 4000);
        },
        error: function () {
            $('#notificationMessage')
                .removeClass('d-none alert-success')
                .addClass('alert-danger')
                .text('Fout bij het verwijderen van de gebruiker.');

            setTimeout(() => {
                $('#notificationMessage').addClass('d-none').text('');
            }, 4000);
        }
    });
});


// delete city on confirm start

// When trash icon clicked, set city id to modal confirm button
$(document).on('click', '.delete-city-btn', function () {
    let cityId = $(this).data('city-id');
    $('.delete-city-confirmation-popup-delete-btn').data('city-id', cityId);
});

$(document).on('click', '.delete-city-confirmation-popup-delete-btn', function () {
    let cityId = $(this).data('city-id');

    $.ajax({
        url: deleteCityUrl,
        type: 'DELETE',
        data: {
            city_id: cityId,
            
            _token: csrfToken
        },
        success: function (response) {
            let $msgBox = $('#notificationMessage');
            $('.delete-City-confirmation-popup').fadeOut(); // Hide popup

            if (response.success) {
                // Optionally remove user row or reload
                location.reload(); 
            } else {
                $msgBox
                    .removeClass('d-none alert-success')
                    .addClass('alert-danger')
                    .text(response.message || 'Could not delete city.');
            }

            // Auto-hide message
            setTimeout(() => {
                $msgBox.addClass('d-none').text('');
            }, 4000);
        },
        error: function () {
            $('#notificationMessage')
                .removeClass('d-none alert-success')
                .addClass('alert-danger')
                .text('Error deleting city.');

            setTimeout(() => {
                $('#notificationMessage').addClass('d-none').text('');
            }, 4000);
        }
    });
});


// delete city on confirm end


// delete car on confirm start

// When trash icon clicked, set car id to modal confirm button
$(document).on('click', '.delete-car-btn', function () {
    let carId = $(this).data('car-id');
    $('.delete-car-confirmation-popup-delete-btn').data('car-id',carId);
});

$(document).on('click', '.delete-car-confirmation-popup-delete-btn', function () {
    let carId = $(this).data('car-id');

    $.ajax({
        url: deleteCarUrl,
        type: 'DELETE',
        data: {
            car_id: carId,
            
            _token: csrfToken
        },
        success: function (response) {
            let $msgBox = $('#notificationMessage');
            $('.delete-car-confirmation-popup').fadeOut(); // Hide popup

            if (response.success) {
                // Optionally remove user row or reload
                location.reload(); 
            } else {
                $msgBox
                    .removeClass('d-none alert-success')
                    .addClass('alert-danger')
                    .text(response.message || 'Could not delete car.');
            }

            // Auto-hide message
            setTimeout(() => {
                $msgBox.addClass('d-none').text('');
            }, 4000);
        },
        error: function () {
            $('#notificationMessage')
                .removeClass('d-none alert-success')
                .addClass('alert-danger')
                .text('Error deleting car.');

            setTimeout(() => {
                $('#notificationMessage').addClass('d-none').text('');
            }, 4000);
        }
    });
});

// delete car on confirm end


// delete service on confirm start

// When trash icon clicked, set car id to modal confirm button
$(document).on('click', '.delete-service-btn', function () {
    let serviceId = $(this).data('service-id');
    $('.delete-service-confirmation-popup-delete-btn').data('service-id',serviceId);
});

$(document).on('click', '.delete-service-confirmation-popup-delete-btn', function () {
    let serviceId = $(this).data('service-id');

    $.ajax({
        url: deleteServiceUrl,
        type: 'DELETE',
        data: {
            service_id: serviceId,
            
            _token: csrfToken
        },
        success: function (response) {
            let $msgBox = $('#notificationMessage');
            $('.delete-service-confirmation-popup').fadeOut(); // Hide popup

            if (response.success) {
                // Optionally remove user row or reload
                location.reload(); 
            } else {
                $msgBox
                    .removeClass('d-none alert-success')
                    .addClass('alert-danger')
                    .text(response.message || 'Could not delete service.');
            }

            // Auto-hide message
            setTimeout(() => {
                $msgBox.addClass('d-none').text('');
            }, 4000);
        },
        error: function () {
            $('#notificationMessage')
                .removeClass('d-none alert-success')
                .addClass('alert-danger')
                .text('Error deleting service.');

            setTimeout(() => {
                $('#notificationMessage').addClass('d-none').text('');
            }, 4000);
        }
    });
});

// delete service on confirm end

// delete query on confirm start

// When trash icon clicked, set query id to modal confirm button

$(document).on('click', '.delete-query-btn', function () {
    let queryId = $(this).data('query-id');
    $('.delete-query-confirmation-popup-delete-btn').data('query-id', queryId);
});

// On confirm delete
$(document).on('click', '.delete-query-confirmation-popup-delete-btn', function () {
    let queryId = $(this).data('query-id');

    $.ajax({
        url: deleteQueryUrl,
        type: 'DELETE',
        data: {
            query_id: queryId,
            _token: csrfToken
        },
        success: function (response) {
            let $msgBox = $('#notificationMessage');
            $('#staticBackdrop').modal('hide'); // Hide popup

            if (response.success) {
                location.reload(); 
            } else {
                $msgBox
                    .removeClass('d-none alert-success')
                    .addClass('alert-danger')
                    .text(response.message || 'Could not delete query.');
            }

            setTimeout(() => {
                $msgBox.addClass('d-none').text('');
            }, 4000);
        },
        error: function () {
            $('#notificationMessage')
                .removeClass('d-none alert-success')
                .addClass('alert-danger')
                .text('Error deleting query.');

            setTimeout(() => {
                $('#notificationMessage').addClass('d-none').text('');
            }, 4000);
        }
    });
});



// delete query on confirm end


// verify or Reject user profile js start

// Verify user
$(document).on('click', '.verify-user-btn', function () {
    let userId = $(this).data('user-id');

    $.ajax({
        url: verifyUserUrl,
        type: 'POST',
        data: {
            user_id: userId,
            _token: csrfToken
        },
        success: function (response) {
            let $msgBox = $('#notificationMessage');
            if (response.success) {
                location.reload(); // refresh table
            } else {
                $msgBox
                    .removeClass('d-none alert-success')
                    .addClass('alert-danger')
                    .text(response.message || 'Could not verify user.');
            }
            setTimeout(() => $msgBox.addClass('d-none').text(''), 4000);
        },
        error: function () {
            $('#notificationMessage')
                .removeClass('d-none alert-success')
                .addClass('alert-danger')
                .text('Error verifying user.');
            setTimeout(() => $('#notificationMessage').addClass('d-none').text(''), 4000);
        }
    });
});

// Reject user
$(document).on('click', '.reject-user-btn', function () {
    let userId = $(this).data('user-id');

    $.ajax({
        url: rejectUserUrl,
        type: 'POST',
        data: {
            user_id: userId,
            _token: csrfToken
        },
        success: function (response) {
            let $msgBox = $('#notificationMessage');
            if (response.success) {
                location.reload();
            } else {
                $msgBox
                    .removeClass('d-none alert-success')
                    .addClass('alert-danger')
                    .text(response.message || 'Could not reject user.');
            }
            setTimeout(() => $msgBox.addClass('d-none').text(''), 4000);
        },
        error: function () {
            $('#notificationMessage')
                .removeClass('d-none alert-success')
                .addClass('alert-danger')
                .text('Error rejecting user.');
            setTimeout(() => $('#notificationMessage').addClass('d-none').text(''), 4000);
        }
    });
});


// verify or Reject user profile js end


$(document).ready(function(){
    
    // js for the view driver details 

    $(document).on('click', '.view-user-details', function() {
        var payload = $(this).attr('data-user');
        try {
        var user = (typeof payload === 'string') ? JSON.parse(payload) : payload;
        } catch(e) {
        user = $(this).data('user') || {};
        }

        // update modal title and fields
        $('#userModalLabel').text('Driver Details - ' + (user.name || '-'));
        $('#modal-name').text(user.name ?? '-');
        $('#modal-phone_number').text(user.phone_number ?? '-');
    });

});

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








