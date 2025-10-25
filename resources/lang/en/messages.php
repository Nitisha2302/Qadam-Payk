<?php

return [

    'login' => [
        'otp_sent_register' => 'OTP sent successfully. Please verify OTP to complete registration.',
        'otp_sent_login' => 'OTP sent successfully. Please verify OTP to complete login.',
        'blocked_by_admin' => 'You are blocked by the admin.',
        'account_deleted' => 'This account has been deleted.',
        'user_not_found' => 'User not found.',
        'invalid_or_expired_otp' => 'Invalid or expired OTP.',
        'otp_verified_success' => 'OTP verified successfully. You are now logged in.',

        'validation' => [
            'phone_required' => 'Phone number is required.',
            'phone_digits_between' => 'Phone number must be between 8 and 15 digits.',
            'otp_required' => 'OTP is required.',
            'otp_digits' => 'OTP must be 6 digits.',
            'device_type_string' => 'Device type must be a string.',
            'device_id_string' => 'Device ID must be a string.',
            'fcm_token_string' => 'FCM token must be a string.',
        ],
    ],

    'otp_verify' => [
        'user_not_found' => 'User not found.',
        'invalid_or_expired_otp' => 'Invalid or expired OTP.',
        'otp_verified_success' => 'OTP verified successfully. You are now logged in.',

        'validation' => [
            'phone_required' => 'Phone number is required.',
            'phone_digits_between' => 'Phone number must be between 8 and 15 digits.',
            'otp_required' => 'OTP is required.',
            'otp_digits' => 'OTP must be 6 digits.',
            'device_type_string' => 'Device type must be a string.',
            'device_id_string' => 'Device ID must be a string.',
            'fcm_token_string' => 'FCM token must be a string.',
        ],
    ],

    'language' => [
        'updated' => 'Language updated successfully.',
        'validation' => [
            'required' => 'Please select a language.',
            'in' => 'Invalid language selected.',
        ],
    ],

    
    'logout' => [
        'logout_success' => 'Logout successful..',
         'user_not_authenticated' => 'User not authenticated.',
    ],

    'getProfile' => [
        'success' => 'Profile fetched successfully.',
        'user_not_authenticated' => 'User not authenticated.',
    ],

     'profile' => [
        'user_not_authenticated' => 'User not authenticated.',
        'updated_successfully' => 'Profile updated successfully.',
        'validation' => [
            'name_required' => 'Name is required.',
            'name_string' => 'Name must be a valid string.',
            'name_max' => 'Name must not exceed 255 characters.',
            'gender_in' => 'Gender must be male, female, or other.',
            'profile_file' => 'Profile image must be a file.',
            'profile_mimes' => 'Profile image must be jpeg, png, or jpg.',
            'profile_max' => 'Profile image must not exceed 4MB.',
            'govid_array' => 'Government ID must be an array.',
            'govid_file' => 'Each government ID must be a file.',
            'govid_mimes' => 'Each government ID must be jpeg, png, jpg, or pdf.',
            'govid_max' => 'Each government ID must not exceed 4MB.',
            'invalid_dob_format' => 'Invalid date format. Use DD-MM-YYYY.',
        ],
    ],

    'deleteAccount' => [
        'success' => 'Your account has been deleted successfully',
         'user_not_authenticated' => 'User not authenticated.',
    ],


    'bookRideOrParcel' => [
        'user_not_authenticated' => 'User not authenticated.',
        'booking_created' => 'Booking created successfully.',
        
        'validation' => [
            'ride_id_required' => 'Ride ID is required.',
            'ride_not_exist' => 'Selected ride does not exist.',
            'seats_required' => 'Number of seats is required for rides.',
            'seats_invalid' => 'Seats must be a valid number.',
            'seats_min' => 'Seats must be at least 1.',
            'services_array' => 'Services must be an array.',
            'service_invalid' => 'Selected service is invalid.',
            'type_required' => 'Booking type is required.',
            'type_invalid' => 'Type must be 0 (ride) or 1 (parcel).',
            'comment_invalid' => 'Comment must be a valid text.',
            'comment_max' => 'Comment cannot exceed 500 characters.',
            'cannot_book_own' => 'You cannot book your own ride or parcel.',
            'already_booked_both' => 'You have already booked ride and parcel.',
            'already_booked_ride' => 'You have already booked this ride.',
            'already_booked_parcel' => 'You have already booked this parcel.',
        ],
    ],


    'getDriverBookings' => [
        'driver_not_authenticated' => 'Driver not authenticated.',
        'success' => 'Bookings retrieved successfully.',
    ],

    'getPassengerBookingRequests' => [
        'passenger_not_authenticated' => 'Passenger not authenticated.',
        'success' => 'Your ride requests retrieved successfully.',
    ],

    'confirmBooking' => [
       'driver_not_authenticated' => 'Driver not authenticated.',
        'unauthorized'             => 'You are not authorized to update this booking.',
        'not_enough_seats'         => 'Not enough seats available. Booking automatically cancelled.',
        'success'                  => 'Booking :status successfully.',
        
        'notification' => [
            'title' => 'Booking :status',
            'body'  => 'Your booking for ride from :pickup to :destination has been :status by the :driver.',
        ],

        'validation' => [
            'booking_id_required' => 'Booking ID is required.',
            'booking_not_exist'   => 'This booking does not exist.',
            'status_required'     => 'Status is required to update the booking.',
            'status_invalid'      => 'Status must be either "confirmed" or "cancelled".',
        ],
    ],

    'updateBookingActiveStatus' => [
        'driver_not_authenticated' => 'Driver not authenticated.',
        'unauthorized'             => 'You are not authorized to start this booking.',
        'invalid_structure'        => 'Invalid booking structure (missing ride or request).',
        'booking_not_found'        => 'Booking not found.',
        'success'                  => 'Booking status updated to active successfully.',

        'notification' => [
            'title' => 'Booking Activated',
            'body'  => 'Your booking for the ride from :pickup to :destination has been started.',
        ],

        'validation' => [
            'booking_id_required' => 'Booking ID is required.',
            'booking_not_exist'   => 'This booking does not exist.',
        ],
    ],

    'updateBookingCompleteStatus' => [
        'driver_not_authenticated' => 'Driver not authenticated.',
        'unauthorized'             => 'You are not authorized to complete this booking.',
        'invalid_structure'        => 'Invalid booking structure (missing ride or request).',
        'booking_not_found'        => 'Booking not found.',
        'success'                  => 'Booking status updated to complete successfully.',

        'notification' => [
            'title' => 'Booking Completed',
            'body'  => 'Your booking for the ride from :pickup to :destination has been completed.',
        ],

        'validation' => [
            'booking_id_required' => 'Booking ID is required.',
            'booking_not_exist'   => 'This booking does not exist.',
        ],
    ],


    'getConfirmationStatus' => [
        'user_not_authenticated' => 'User not authenticated.',
        'success'                => 'Rides fetched successfully.',
        'no_rides_found'         => 'No rides found for the selected status.',

        'validation' => [
            'status_type_invalid' => 'Invalid status type provided. Must be active, completed, or cancelled.',
        ],
    ],


    'getSendResponse' => [
        'user_not_authenticated' => 'User not authenticated.',
        'success'                => 'Sent items fetched successfully.',
        'no_items_found'         => 'No sent bookings or requests found.',

        'types' => [
            'booking'          => 'Booking',
            'request_interest' => 'Passenger request interest',
        ],

        'notification' => [
            'title' => 'Booking/Request Update',
            'body'  => 'Your booking or passenger request has a status update.',
        ],

        'validation' => [
            'invalid_user' => 'Invalid user.',
        ],
    ],



    'getReceivedResponse' => [
            'user_not_authenticated' => 'User not authenticated.',
            'success'                 => 'Received requests fetched successfully.',
            
            'created_by' => [
                'driver'    => 'driver',
                'passenger' => 'passenger',
            ],

            'rides_with_bookings' => [
                'no_bookings' => 'No active bookings found for this ride.',
            ],

            'passenger_requests' => [
                'no_requests' => 'No active passenger requests found.',
            ],

            'driver_info' => [
                'name'             => 'Driver name',
                'phone_number'     => 'Phone number',
                'email'            => 'Email',
                'image'            => 'Profile image',
                'dob'              => 'Date of birth',
                'gender'           => 'Gender',
                'id_verified'      => 'ID verified',
                'is_phone_verify'  => 'Phone verified',
                'device_type'      => 'Device type',
                'device_id'        => 'Device ID',
            ],

            'vehicle_info' => [
                'vehicle_number' => 'Vehicle number',
                'vehicle_type'   => 'Vehicle type',
                'vehicle_name'   => 'Vehicle brand',
                'vehicle_model'  => 'Vehicle model',
                'vehicle_image'  => 'Vehicle image',
            ],
     ],


    'start' => [
        'user_not_authenticated' => 'User not authenticated.',
        'success'                => 'Conversation started successfully.',

        'validation' => [
            'other_user_id_required'  => 'Other user ID is required.',
            'other_user_id_not_exist' => 'The user you are trying to chat with does not exist.',
            'other_user_id_not_in'    => 'You cannot start a chat with yourself.',
        ],
    ],


    'allConversation' => [
       'user_not_authenticated' => 'User not authenticated.',
        'success'                => 'Conversations fetched successfully.',


        'fields' => [
            'conversation_id'    => 'Conversation ID',
            'other_user_id'      => 'Other user ID',
            'other_user_name'    => 'Other user name',
            'other_user_phone'   => 'Other user phone',
            'other_user_image'   => 'Other user profile image',
            'last_message'       => 'Last message',
            'last_message_time'  => 'Last message time',
            'unread_count'       => 'Unread messages count',
        ],
    ],


    'send' => [
        'user_not_authenticated' => 'User not authenticated.',
        'success'                => 'Message sent successfully.',
        'not_participant'        => 'You are not a participant in this conversation.',

        'validation' => [
            'conversation_not_exist' => 'Conversation not found.',
            'user_not_exist'         => 'User does not exist.',
            'message_required'       => 'Message cannot be empty.',
            'message_string'         => 'Message must be text.',
            'message_max'            => 'Message is too long (max 5000 chars).',
            'type_invalid'           => 'Invalid message type.',
        ],

        'notification' => [
            'title' => '💬 New Message',
            'body'  => ':sender sent you a message: :message',
        ],

    ],


    'allMessages' => [
        'user_not_authenticated' => 'User not authenticated.',
        'success'                => 'Messages fetched successfully.',
        'not_participant'        => 'You are not a participant in this conversation.',

        'validation' => [
            'conversation_not_exist' => 'Conversation does not exist.',
            'conversation_required'  => 'Conversation ID is required.',
        ],
    ],


    // 'markRead' => [
    //     'user_not_authenticated'        => 'User not authenticated.',
    //     'message_marked_success'        => 'Message marked as read successfully.',
    //     'messages_marked_success'       => 'Messages marked as read successfully.',
    //     'not_participant'               => 'You are not a participant in this conversation.',
    //     'cannot_mark_own_message'       => 'You cannot mark your own message as read.',
    //     'message_already_read'          => 'This message is already marked as read.',
    //     'no_unread_messages'            => 'No unread messages to mark as read.',
    //     'conversation_or_message_required' => 'You must provide either a conversation_id or a message_id.',

    //     'validation' => [
    //         'conversation_not_exist'     => 'The conversation you are trying to access does not exist.',
    //         'message_not_exist'          => 'The message you are trying to mark as read does not exist.',
    //     ],
    // ],


   'vehicle' => [
        'add_vehicle' => [
            'user_not_authenticated' => 'User not authenticated.',
            'success'                => 'Vehicle added successfully.',

            'validation' => [
                    'brand_required'        => 'Vehicle brand is required.',
                    'model_required'        => 'Vehicle model is required.',
                    'number_plate_required' => 'Number plate is required.',
                    'number_plate_unique'   => 'This number plate is already registered.',
                    'vehicle_image_image'   => 'Vehicle image must be an image file.',
                    'vehicle_image_mimes'   => 'Vehicle image must be a file of type: jpg, jpeg, png.',
            ],
        ],

        'get_vehicles' => [
            'user_not_authenticated' => 'User not authenticated.',
            'success'                => 'Vehicles fetched successfully.',
            'no_vehicles_found'      => 'No vehicles found for this user.',
        ],

        'edit_vehicle' => [
            'user_not_authenticated' => 'User not authenticated.',
            'success'                => 'Vehicle updated successfully.',
            'vehicle_not_found'      => 'Vehicle not found or not owned by this user.',
            'validation' => [
                'vehicle_id_required'   => 'Vehicle ID is required.',
                'vehicle_not_found'     => 'Vehicle not found.',
                'brand_required'        => 'Vehicle brand is required.',
                'model_required'        => 'Vehicle model is required.',
                'number_plate_required' => 'Number plate is required.',
                'number_plate_unique'   => 'This number plate is already registered.',
                'vehicle_image_invalid' => 'Vehicle image must be a valid image file (jpg, jpeg, png).',
            ],
        ],

    ],


    'ride' => [
        'create' => [
            'user_not_authenticated' => 'User not authenticated.',
            'success' => 'Ride created successfully.',
            'validation' => [
                'vehicle_id_required'      => 'Vehicle ID is required.',
                'vehicle_id_exists'        => 'The selected vehicle is invalid.',
                'pickup_location_required' => 'Pickup location is required.',
                'destination_required'     => 'Destination is required.',
                'number_of_seats_required' => 'Number of seats is required.',
                'number_of_seats_integer'  => 'Number of seats must be an integer.',
                'price_required'           => 'Price is required.',
                'price_numeric'            => 'Price must be a number.',
                'ride_date_required'       => 'Ride date is required.',
                'ride_date_after_or_equal' => 'Ride date must be today or later.',
                'ride_time_required'       => 'Ride time is required.',
                'ride_time_format'         => 'Ride time must be in the format HH:MM.',
                'accept_parcel_boolean'    => 'Accept parcel must be true or false.',
                'services_exists'          => 'One or more selected services are invalid.',
                'reaching_time_format'     => 'Reaching time must be in HH:MM (24-hour) format.',
            ],
        ],

        'edit' => [
            'user_not_authenticated' => 'User not authenticated.',
            'ride_not_found' => 'Ride not found or not authorized.',
            'success' => 'Ride updated successfully.',
            'validation' => [
                'ride_id_required' => 'Ride ID is required.',
                'ride_not_found'   => 'Selected ride does not exist.',
                'vehicle_id_required' => 'Vehicle ID is required.',
                'vehicle_not_found'   => 'Selected vehicle is invalid.',
                'pickup_location_required' => 'Pickup location is required.',
                'destination_required' => 'Destination is required.',
                'number_of_seats_required' => 'Number of seats is required.',
                'number_of_seats_integer' => 'Number of seats must be an integer.',
                'price_required' => 'Price is required.',
                'price_numeric' => 'Price must be a number.',
                'ride_date_required' => 'Ride date is required.',
                'ride_date_after_or_equal' => 'Ride date must be today or later.',
                'ride_time_required' => 'Ride time is required.',
                'ride_time_format' => 'Ride time must be in format HH:MM.',
                'reaching_time_format' => 'Reaching time must be in HH:MM (24-hour) format.',
                'accept_parcel_boolean' => 'Accept parcel must be true or false.',
                'services_array' => 'Services must be an array.',
                'services_exists' => 'One or more selected services are invalid.',
            ],
        ],

        'driver_rides' => [
            'user_not_authenticated' => 'User not authenticated.',
            'success' => 'Driver rides fetched successfully.',
        ],

        
        'search' => [
            'user_not_authenticated' => 'User not authenticated.',
            'success' => 'Rides found successfully.',
            'validation' => [
                'pickup_location_string' => 'Pickup location must be a valid string.',
                'pickup_location_max'    => 'Pickup location must not exceed 255 characters.',
                'destination_string'     => 'Destination must be a valid string.',
                'destination_max'        => 'Destination must not exceed 255 characters.',
                'ride_date_format'       => 'Ride date must be in DD-MM-YYYY format.',
                'ride_date_after_or_equal' => 'Ride date must be today or a future date.',
                'number_of_seats_integer'  => 'Number of seats must be a valid number.',
                'number_of_seats_min'      => 'Number of seats must be at least 1.',
                'services_array'           => 'Services must be an array.',
                'services_string'          => 'Each service must be a string.',
                'services_max'             => 'Each service cannot exceed 50 characters.',
            ],
        ],

    ],


    'driver' => [
        'details' => [
            'validation' => [
                'user_id_required' => 'User ID is required.',
                'user_id_integer'  => 'User ID must be a valid integer.',
                'user_id_exists'   => 'Driver not found.',
            ],
            'success' => 'Driver details fetched successfully.',
             'user_not_authenticated' => 'User not authenticated.',
        ],
    ],


    'enquiry' => [
        'user_not_authenticated' => 'User not authenticated.',
        'success' => 'Enquiry submitted successfully.',
        'fetch_success'           => 'Queries fetched successfully.',
        'validation' => [
            'title_required'       => 'Title is required.',
            'description_required' => 'Description is required.',
        ],
    ],


    'rating' => [
        'user_not_authenticated' => 'User not authenticated.',
        'ride_not_found' => 'The selected ride does not exist.',
        'reviewed_user_not_found' => 'The selected user does not exist.',
        'booking_required' => 'You have not booked this ride and cannot review it.',
        'already_rated' => 'You have already rated this ride/user.',
        'success' => 'Rating submitted successfully.',
         'fetch_success' => 'Ratings fetched successfully.',
        'validation' => [
            'ride_id_required' => 'Ride ID is required.',
            'ride_id_exists' => 'The selected ride does not exist.',
            'reviewed_id_required' => 'Please select a user to review.',
            'reviewed_id_exists' => 'The selected user does not exist.',
            'rating_required' => 'Rating is required.',
            'rating_integer' => 'Rating must be a number.',
            'rating_min' => 'Rating must be at least 1 star.',
            'rating_max' => 'Rating cannot exceed 5 stars.',
            'review_string' => 'Review must be text.',
            'review_max' => 'Review cannot exceed 500 characters.',
        ],
    ],
    

    'createRideRequest' => [
        'user_not_authenticated' => 'User not authenticated.',
        'success' => 'Ride request created successfully.',
        'validation' => [
            'pickup_location_required' => 'Pickup location is required.',
            'pickup_location_string' => 'Pickup location must be a string.',
            'pickup_location_max' => 'Pickup location cannot exceed 255 characters.',
            'destination_required' => 'Destination is required.',
            'destination_string' => 'Destination must be a string.',
            'destination_max' => 'Destination cannot exceed 255 characters.',
            'ride_date_required' => 'Ride date is required.',
            'ride_date_format' => 'Ride date must be in DD-MM-YYYY format.',
            'ride_date_after_or_equal' => 'Ride date must be today or a future date.',
            'number_of_seats_integer' => 'Number of seats must be an integer.',
            'number_of_seats_min' => 'Number of seats must be at least 1.',
            'services_array' => 'Services must be an array.',
            'services_exists' => 'One or more selected services are invalid.',
            'budget_required' => 'Budget must be required.',
            'budget_numeric' => 'Budget must be a valid number.',
            'budget_min' => 'Budget must be at least 0.',
            'preferred_time_format' => 'Preferred time must be in HH:MM format.',
        ],
    ],


    'createParcelRequest' => [
        'user_not_authenticated' => 'User not authenticated.',
        'success' => 'Parcel request created successfully.',
        'validation' => [
            'pickup_location_required' => 'Pickup location is required.',
            'pickup_location_string' => 'Pickup location must be a string.',
            'pickup_location_max' => 'Pickup location cannot exceed 255 characters.',
            'destination_required' => 'Destination is required.',
            'destination_string' => 'Destination must be a string.',
            'destination_max' => 'Destination cannot exceed 255 characters.',
            'ride_date_required' => 'Ride date is required.',
            'ride_date_format' => 'Ride date must be in DD-MM-YYYY format.',
            'ride_date_after_or_equal' => 'Ride date must be today or a future date.',
            'ride_time_required' => 'Ride time is required.',
            'ride_time_format' => 'Ride time must be in HH:MM format.',
            'pickup_contact_name_required' => 'Pickup contact name is required.',
            'pickup_contact_no_required' => 'Pickup contact number is required.',
            'drop_contact_name_required' => 'Drop contact name is required.',
            'drop_contact_no_required' => 'Drop contact number is required.',
            'parcel_details_required' => 'Parcel details are required.',
            'parcel_images_image' => 'Each file must be an image.',
            'parcel_images_mimes' => 'Image must be jpeg, png, jpg, or gif.',
            'parcel_images_max' => 'Each image may not exceed 2MB.',
            'budget_required' => 'Budget is required.',
            'budget_numeric' => 'Budget must be a valid number.',
            'budget_min' => 'Budget must be at least 0.',
            'preferred_time_format' => 'Preferred time must be in HH:MM format.',
        ],
    ],


    'getAllRideRequests' => [
        'user_not_authenticated' => 'User not authenticated.',
        'ride_requests_retrieved' => 'Ride requests retrieved successfully.',
        'validation' => [
            'pickup_location_required' => 'Pickup location is required.',
            'pickup_location_string' => 'Pickup location must be a string.',
            'pickup_location_max' => 'Pickup location cannot exceed 255 characters.',

            'destination_required' => 'Destination is required.',
            'destination_string' => 'Destination must be a string.',
            'destination_max' => 'Destination cannot exceed 255 characters.',

            'ride_date_required' => 'Ride date is required.',
            'ride_date_format' => 'Ride date must be in DD-MM-YYYY format.',
            'ride_date_after_or_equal' => 'Ride date must be today or a future date.',

            'number_of_seats_integer' => 'Number of seats must be a valid number.',
            'number_of_seats_min' => 'Number of seats must be at least 1.',
        ],
        'invalid_ride_date_format' => 'Invalid ride_date format. Use DD-MM-YYYY.',
    ],



    'getAllParcelRequests' => [
        'user_not_authenticated' => 'User not authenticated.',
        'parcel_requests_retrieved' => 'Parcel requests retrieved successfully.',
        'validation' => [
            'pickup_location_required' => 'Pickup location is required.',
            'pickup_location_string'   => 'Pickup location must be a string.',
            'pickup_location_max'      => 'Pickup location cannot exceed 255 characters.',

            'destination_required' => 'Destination is required.',
            'destination_string'   => 'Destination must be a string.',
            'destination_max'      => 'Destination cannot exceed 255 characters.',

            'ride_date_required' => 'Ride date is required.',
            'ride_date_format'   => 'Ride date must be in DD-MM-YYYY format.',
            'ride_date_after_or_equal' => 'Ride date must be today or a future date.',

            'number_of_seats_integer' => 'Number of seats must be a valid number.',
            'number_of_seats_min'     => 'Number of seats must be at least 1.',
        ],
        'invalid_ride_date_format' => 'Invalid ride_date format. Use DD-MM-YYYY.',
    ],

    'listCurrentPassengerRequests' => [
        'user_not_authenticated' => 'User not authenticated.',
        'requests_retrieved' => 'Passenger requests retrieved successfully.',
    ],


    'getInterestedDrivers' => [
        'user_not_authenticated' => 'User not authenticated.',
        'request_not_found' => 'Request not found.',
        'drivers_retrieved' => 'Interested drivers with ride details fetched successfully.',
    ],



    'updateRequestInterestStatus' => [
        'driver_not_authenticated' => 'Driver not authenticated.',
        'already_interested'       => 'You already expressed interest.',
        'success'                  => 'Driver expressed interest successfully.',
        'default_driver_name'      => 'Driver',

        'notification' => [
            'title' => 'Driver interested',
            'body'  => 'Driver :driverName has expressed interest in your ride request from :pickup to :destination. Please confirm.',
        ],

        'validation' => [
            'request_id_required' => 'Request ID is required.',
            'request_id_exists'   => 'This request does not exist.',
        ],
    ],


    'confirmDriverByPassenger' => [
        'passenger_not_authenticated' => 'You must be logged in as a passenger to confirm a driver.',
        'request_not_found' => 'Ride request not found or does not belong to you.',
        'already_confirmed' => 'A driver has already been confirmed for this request.',
        'driver_not_interested' => 'The driver you selected did not express interest in this ride.',
        'declined_success' => 'Driver interest has been declined and removed successfully.',
        'success' => 'Driver confirmed successfully and booking has been created.',
        'default_passenger_name' => 'Passenger',

        'notification' => [
            'confirmed' => [
                'title' => 'Ride Request Confirmed',
                'body' => ':passengerName has confirmed your interest for ride from :pickup to :destination.',
            ],
            'declined' => [
                'title' => 'Ride Request Declined',
                'body' => ':passengerName has declined your interest for the ride from :pickup to :destination.',
            ],
        ],

        'validation' => [
            'request_id_required' => 'Request ID is required.',
            'request_id_exists' => 'The specified ride request does not exist.',
            'driver_id_required' => 'You must select a driver to confirm.',
            'driver_id_exists' => 'The selected driver does not exist.',
            'status_required' => 'Status is required.',
            'status_in' => 'Status must be either confirmed or declined.',
        ],
    ],









    










];
