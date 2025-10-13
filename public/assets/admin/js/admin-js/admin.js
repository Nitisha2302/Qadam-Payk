
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

// delete booking on confirm start

// When trash icon clicked, set city id to modal confirm button
$(document).on('click', '.delete-booking-btn', function () {
    let bookingId = $(this).data('booking-id');
    $('.delete-booking-confirmation-popup-delete-btn').data('booking-id', bookingId);
});

$(document).on('click', '.delete-booking-confirmation-popup-delete-btn', function () {
    let bookingId = $(this).data('booking-id');

    $.ajax({
        url: deleteBookingUrl,
        type: 'DELETE',
        data: {
            booking_id: bookingId,
            
            _token: csrfToken
        },
        success: function (response) {
            let $msgBox = $('#notificationMessage');
            $('.delete-booking-confirmation-popup').fadeOut(); // Hide popup

            if (response.success) {
                // Optionally remove user row or reload
                location.reload(); 
            } else {
                $msgBox
                    .removeClass('d-none alert-success')
                    .addClass('alert-danger')
                    .text(response.message || 'Could not delete booking.');
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
                .text('Error deleting booking.');

            setTimeout(() => {
                $('#notificationMessage').addClass('d-none').text('');
            }, 4000);
        }
    });
});


// delete booking on confirm end


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


// block unblock user startt

// When Block/Unblock button clicked
// When the block/unblock button is clicked, open the modal and set user id
 // Open modal when block/unblock button clicked
    $(document).on('click', '.toggle-block-btn', function () {
        let userId = $(this).data('user-id');
        $('.confirm-block-unblock-btn').data('user-id', userId);
        $('#blockUnblockModal').modal('show');
    });

    // Confirm block/unblock
    $(document).on('click', '.confirm-block-unblock-btn', function () {
        let userId = $(this).data('user-id');

        $.ajax({
            url: toggleBlockUserUrl,
            type: 'POST',
            data: {
                user_id: userId,
                _token: csrfToken
            },
            success: function (response) {
                if (response.success) {
                    let btn = $(`.toggle-block-btn[data-user-id='${userId}']`);

                    if (response.is_blocked) {
                        btn.text('Unblock').removeClass('btn-danger').addClass('btn-warning');
                    } else {
                        btn.text('Block').removeClass('btn-warning').addClass('btn-danger');
                    }

                    // Hide modal
                    $('#blockUnblockModal').modal('hide');

                    // Show success message
                    let msgBox = $('#successMessage');
                    msgBox.text(response.message).removeClass('d-none');
                    setTimeout(() => msgBox.addClass('d-none').text(''), 4000);
                } else {
                    alert(response.message || 'Something went wrong!');
                }
            },
            error: function () {
                alert('Error occurred!');
            }
        });
    });


// block unblock user end 


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

// view booking details 
$(document).ready(function() {
    $(document).on('click', '.view-booking-details', function() {
        let payload = $(this).attr('data-user');
        let booking;

        try {
            booking = (typeof payload === 'string') ? JSON.parse(payload) : payload;
        } catch (e) {
            booking = $(this).data('user') || {};
        }

        // Passenger & Driver
        let passenger = booking.passenger?.name || booking.user?.name || 'N/A';
        let driver = booking.driver?.name || 'N/A';
        let passengerPhone = booking.passenger?.phone_number || booking.user?.phone_number || '-';
        let driverPhone = booking.driver?.phone_number || '-';

        // Pickup / Destination
        let pickup = booking.pickup_location || booking.ride?.pickup_location || booking.request?.pickup_location || '-';
        let destination = booking.destination || booking.ride?.destination || booking.request?.destination || '-';

        // Services
        let services = 'N/A';
        if (booking.services_details?.length > 0) {
            services = booking.services_details.map(s => {
                let icon = s.service_image ? `<img src="/${s.service_image}" width="20" height="20" class="me-1" />` : '';
                return icon + s.service_name;
            }).join(', ');
        }

        // Type
        let type = booking.type == 0 ? 'Ride' : (booking.type == 1 ? 'Parcel' : 'N/A');

        // Seats & Price
        let seats = booking.seats_booked || '-';
        let price = booking.price ? `₹${booking.price}` : '-';

        // Status
        let status = 'Pending';
        if (booking.status === 'cancelled') status = 'Cancelled';
        else if (booking.status === 'confirmed' && booking.active_status == 0) status = 'Confirmed';
        else if (booking.active_status == 1) status = 'Active';
        else if (booking.active_status == 2) status = 'Completed';

        // Date & Time
        let date = booking.ride_date
            ? new Date(booking.ride_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
            : '-';
        let time = booking.ride_time || '-';

        // Fill modal
        $('#userModalLabel').text('Booking Details');
        $('#modal-id').text(booking.id || '-');
        $('#modal-passenger').html(`${passenger} <br><small>${passengerPhone}</small>`);
        $('#modal-driver').html(`${driver} <br><small>${driverPhone}</small>`);
        $('#modal-pickup').text(pickup);
        $('#modal-destination').text(destination);
        $('#modal-service').html(services);
        $('#modal-type').text(type);
        $('#modal-status').text(status);
        $('#modal-date').html(`${date} ${time !== '-' ? '(' + time + ')' : ''}`);

        // Optional: Add price & seat info below existing fields
        if ($('#modal-price').length === 0) {
            $('#userModal table tbody').append(`
                <tr><th>Seats</th><td id="modal-seats">${seats}</td></tr>
                <tr><th>Price</th><td id="modal-price">${price}</td></tr>
            `);
        } else {
            $('#modal-seats').text(seats);
            $('#modal-price').text(price);
        }
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








