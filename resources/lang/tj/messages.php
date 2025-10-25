<?php

return [

    'login' => [
        'otp_sent_register' => 'OTP успешно отправлен. Подтвердите OTP для завершения регистрации.',
        'otp_sent_login' => 'OTP успешно отправлен. Подтвердите OTP для входа.',
        'blocked_by_admin' => 'Вы заблокированы администратором.',
        'account_deleted' => 'Этот аккаунт удален.',
        'user_not_found' => 'Пользователь не найден.',
        'invalid_or_expired_otp' => 'Неверный или истекший OTP.',
        'otp_verified_success' => 'OTP успешно подтвержден. Вы вошли в систему.',

        'validation' => [
            'phone_required' => 'Номер телефона обязателен.',
            'phone_digits_between' => 'Номер телефона должен быть от 8 до 15 цифр.',
            'otp_required' => 'OTP обязателен.',
            'otp_digits' => 'OTP должен содержать 6 цифр.',
            'device_type_string' => 'Тип устройства должен быть строкой.',
            'device_id_string' => 'ID устройства должен быть строкой.',
            'fcm_token_string' => 'FCM токен должен быть строкой.',
        ],
    ],

    'otp_verify' => [
        'user_not_found' => 'Пользователь не найден.',
        'invalid_or_expired_otp' => 'Неверный или истекший OTP.',
        'otp_verified_success' => 'OTP успешно подтвержден. Вы вошли в систему.',

        'validation' => [
            'phone_required' => 'Номер телефона обязателен.',
            'phone_digits_between' => 'Номер телефона должен быть от 8 до 15 цифр.',
            'otp_required' => 'OTP обязателен.',
            'otp_digits' => 'OTP должен содержать 6 цифр.',
            'device_type_string' => 'Тип устройства должен быть строкой.',
            'device_id_string' => 'ID устройства должен быть строкой.',
            'fcm_token_string' => 'FCM токен должен быть строкой.',
        ],
    ],

    'language' => [
        'updated' => 'Язык успешно обновлен.',
        'validation' => [
            'required' => 'Выберите язык.',
            'in' => 'Выбран неверный язык.',
        ],
    ],

    'logout' => [
        'logout_success' => 'Шумо муваффақона аз система баромадед.',
         'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
    ],

    'getProfile' => [
        'success' => 'Профил бор карда шуд.',
        'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
    ],

     'profile' => [
        'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
        'updated_successfully' => 'Корбар муваффақона навсозӣ шуд.',
        'validation' => [
            'name_required' => 'Лутфан, номро ворид кунед.',
            'name_string' => 'Ном бояд дуруст бошад.',
            'name_max' => 'Ном набояд аз 255 аломат зиёд бошад.',
            'gender_in' => 'Ҷинсро нишон диҳед: мард, зан ё дигар.',
            'profile_file' => 'Тасвири корбарро бор кунед.',
            'profile_mimes' => 'Тасвир бояд дар формати JPEG, PNG ё JPG бошад.',
            'profile_max' => 'ТҲуҷҷатҳо бояд массив бошанд.',
            'govid_file' => 'Ҳар як ҳуҷҷат бояд файл бошад.',
            'govid_mimes' => 'Ҳуҷҷат бояд дар формати JPEG, PNG, JPG ё PDF бошад.',
            'govid_max' => 'Ҳар як ҳуҷҷат набояд аз 4MB зиёд бошад.',
            'invalid_dob_format' => 'Формати сана нодуруст аст. Аз ДД-ММ-СССС истифода баред.',
        ],
    ],
     'deleteAccount' => [
        'success' => 'Ҳисобатон бо муваффақият ҳазф карда шуд.',
         'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
    ],

     'bookRideOrParcel' => [
        'user_not_authenticated' => 'User not authenticated.',
        'booking_created' => 'Booking created successfully.',
        'validation' => [
            'ride_id_required' => 'Лутфан ID-и сафарро нишон диҳед.',
            'ride_not_exist' => 'Сафар интихобшуда пайдо нашуд.',
            'seats_required' => 'Миқдори ҷойро нишон диҳед.',
            'seats_invalid' => 'Миқдори ҷой бояд рақам бошад.',
            'seats_min' => 'Миқдори ҷой набояд аз 1 кам бошад..',
            'services_array' => 'Хизматрасониҳо бояд массив бошанд.',
            'service_invalid' => 'Хизматрасонии интихобшуда нодуруст аст.',
            'type_required' => 'Навъи бронро нишон диҳед.',
            'type_invalid' => 'Навъи сафар бояд 0 (сафар) ё 1 (баста) бошад.',
            'comment_invalid' => 'Шарҳ бояд матн бошад.',
            'comment_max' => 'Шарҳ набояд аз 500 аломат зиёд бошад.',
            'cannot_book_own' => 'Шумо наметавонед сафарҳои худро брон кунед.',
            'already_booked_both' => 'You have already booked ride and parcel.',
            'already_booked_ride' => 'You have already booked this ride.',
            'already_booked_parcel' => 'You have already booked this parcel.',
        ],
    ],

    'getDriverBookings' => [
        'driver_not_authenticated' => 'Ронанда тасдиқ нашудааст.',
        'success' => 'Рӯйхати бронҳо бор карда шуд.',
    ],

    'getPassengerBookingRequests' => [
        'passenger_not_authenticated' => 'Мусофир тасдиқ нашудааст.',
        'success' => 'Дархостҳои сафари шумо бор карда шуданд.',
    ],

    'confirmBooking' => [
       'driver_not_authenticated' => 'Ронанда тасдиқ нашудааст.',
        'unauthorized'             => 'Шумо ҳуқуқи навсозии ин бронро надоред.',
        'not_enough_seats'         => 'Ҷойҳо кофӣ нестанд. Брон худкор бекор карда шуд.',
        'success'                  => 'Брон бо муваффақият :status шуд.',
        
        'notification' => [
            'title' => 'Брон :status',
            'body'  => 'Брони шумо аз :pickup то :destination аз ҷониби ронанда :driver :status шуд.',
        ],

        'validation' => [
            'booking_id_required' => 'Лутфан ID-и бронро ворид кунед.',
            'booking_not_exist'   => 'Ин брон вуҷуд надорад.',
            'status_required'     => 'Лутфан ҳолати бронро нишон диҳед.',
            'status_invalid'      => 'Ҳолат бояд “тасдиқшуда” ё “бекоршуда” бошад.',
        ],
    ],


    'updateBookingActiveStatus' => [
        'driver_not_authenticated' => 'Ронанда тасдиқ нашудааст.',
        'unauthorized'             => 'Шумо ҳуқуқ надоред, ки ин фармоишро оғоз кунед',
        'invalid_structure'        => 'Сохтори фармоиш нодуруст аст (сафар ё дархост намерасад).',
        'booking_not_found'        => 'Брон ёфт нашуд.',
        'success'                  => 'Ҳолати брон ба “фаъол” иваз карда шуд..',

        'notification' => [
            'title' => 'Фармоиш фаъол шуд',
            'body'  => 'Фармоиши шумо барои сафар аз :pickup то :destination оғоз ёфт.',
        ],

        'validation' => [
            'booking_id_required' => 'Лутфан, ID-и бронро ворид кунед.',
            'booking_not_exist'   => 'Ин брон вуҷуд надорад.',
        ],
    ],

    'updateBookingCompleteStatus' => [
        'driver_not_authenticated' => 'Ронанда тасдиқ нашудааст.',
        'unauthorized'             => 'Шумо ҳуқуқ надоред, ки ин фармоишро анҷом диҳед.',
        'invalid_structure'        => 'Сохтори фармоиш нодуруст аст (сафар ё дархост намерасад).',
        'booking_not_found'        => 'Брон ёфт нашуд.',
            'success'                  => 'Ҳолати фармоиш бомуваффақият ба анҷомёфта иваз шуд.',

        'notification' => [
            'title' => 'Фармоиш анҷом ёфт',
            'body'  => 'Фармоиши шумо барои сафар аз :pickup то :destination анҷом ёфт.',
        ],

        'validation' => [
            'booking_id_required' => 'Лутфан, ID-и бронро ворид кунед.',
            'booking_not_exist'   => 'Ин брон вуҷуд надорад.',
        ],
    ],


    'getConfirmationStatus' => [
        'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
        'success'                => 'Сафарҳои ронанда муваффақона бор карда шуданд.',
         'no_rides_found'         => 'Барои ин ҳолат ягон сафар ёфт нашуд.',

        'validation' => [
            'status_type_invalid' => 'Навъи ҳолати нодуруст. Бояд active, completed ё cancelled бошад.',
        ],
    ],


    'getSendResponse' => [
        'user_not_authenticated' => 'Истифодабаранда ворид нашудааст.',
        'success'                => 'Маводҳои фиристодашуда бомуваффақият гирифта шуданд.',
        'no_items_found'         => 'Ҳеҷ як брон ё дархост фиристода нашудааст.',

        'types' => [
            'booking'          => 'Брон',
            'request_interest' => 'Манфиат ба дархости мусофир',
        ],

        'notification' => [
            'title' => 'Навсозии брон/дархост',
            'body'  => 'Брони шумо ё дархости мусофир навсозӣ шудааст.',
        ],

        'validation' => [
            'invalid_user' => 'Истифодабарандаи нодуруст.',
        ],
    ],


   'getReceivedResponse' => [
        'user_not_authenticated' => 'Истифодабаранда тасдиқ нашудааст.',
        'success'                 => 'Дархостҳои гирифташуда бо муваффақият фаро гирифта шуданд.',
        
        'created_by' => [
            'driver'    => 'рҳондор',
            'passenger' => 'савор',
        ],

        'rides_with_bookings' => [
            'no_bookings' => 'Барои ин сафар ҳеҷ фармоиши фаъол вуҷуд надорад.',
        ],

        'passenger_requests' => [
            'no_requests' => 'Ҳеҷ дархости фаъол барои савор вуҷуд надорад.',
        ],

        'driver_info' => [
            'name'             => 'Номи ронанда',
            'phone_number'     => 'Рақами телефон',
            'email'            => 'Почтаи электронӣ',
            'image'            => 'Акс профил',
            'dob'              => 'Санаи таваллуд',
            'gender'           => 'Ҷинс',
            'id_verified'      => 'ID тасдиқ шуд',
            'is_phone_verify'  => 'Телефон тасдиқ шудааст',
            'device_type'      => 'Навъи дастгоҳ',
            'device_id'        => 'ID дастгоҳ',
        ],

        'vehicle_info' => [
            'vehicle_number' => 'Рақами мошин',
            'vehicle_type'   => 'Навъи мошин',
            'vehicle_name'   => 'Бренди мошин',
            'vehicle_model'  => 'Модели мошин',
            'vehicle_image'  => 'Акс мошин',
        ],
    ],


    'start' => [
        'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
        'success'                => 'Чат муваффақона оғоз ёфт.',

        'validation' => [
            'other_user_id_required'  => 'ID-и ҳамсӯҳбатро ворид кунед.',
            'other_user_id_not_exist' => 'Корбар барои чат ёфт нашуд.',
            'other_user_id_not_in'    => 'Бо худатон чат кардан мумкин нест.',
        ],
    ],


    'allConversation' => [
       'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
        'success'                => 'Рӯйхати чатҳо муваффақона бор карда шуд.',


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
        'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
        'success'                => 'Паём муваффақона фиристода шуд.',
        'not_participant'        => 'Шумо дар ин чат иштирок надоред..',

        'validation' => [
            'conversation_not_exist' => 'Чат ёфт нашуд.',
            'user_not_exist'         => 'Корбар вуҷуд надорад.',
            'message_required'       => 'Паём набояд холӣ бошад.',
            'message_string'         => 'Паём бояд матнӣ бошад.',
            'message_max'            => 'Паём хеле дароз аст (на бештар аз 5000 аломат).',
            'type_invalid'           => 'Навъи паём нодуруст аст.',
        ],

        'notification' => [
            'title' => '💬 Паёми нав',
            'body'  => ':sender ба шумо паём фиристод: ":message"',
        ],
    ],


    'allMessages' => [
        'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
        'success'                => 'Паёмҳо муваффақона бор карда шуданд.',
        'not_participant'        => 'Шумо наметавонед дар ин чат иштирок кунед.',

        'validation' => [
            'conversation_not_exist' => 'Ин чат вуҷуд надорад.',
            'conversation_required'  => 'ID-и чатро ворид кунед.',
        ],
    ],


    'vehicle' => [
        'add_vehicle' => [
            'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
            'success'                => 'Нақлиет бо муваффақият илова шуд.',

        'validation' => [
                'brand_required'        => 'Номи бренди нақлиет ҳатмист.',
                'model_required'        => 'Модели нақлиетро ворид кунед.',
                'number_plate_required' => 'Рақами қайди нақлиет лозим аст.',
                'number_plate_unique'   => 'Ин рақами қайдшуда аллакай истифода шудааст.',
                'vehicle_image_image'   => 'Сурат бояд расм бошад.',
                'vehicle_image_mimes'   => 'Сурат бояд дар формати JPG, JPEG ё PNG бошад.',
            ],
        ],

        'get_vehicles' => [
            'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
            'success'                => 'Рӯйхати мошинҳо муваффақона бор шуд.',
            'no_vehicles_found'      => 'Барои ин корбар ҳоло мошин нест.',
        ],


        'edit_vehicle' => [
            'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
            'success'                => 'Маълумоти мошин муваффақона нав шуд..',
            'vehicle_not_found'      => 'Мошин ёфт нашуд ё ба шумо тааллуқ надорад.',
            'validation' => [
                'vehicle_id_required'   => 'ID мошин лозим аст.',
                'vehicle_not_found'     => 'Vehicle not found.',
                'brand_required'        => 'Номи бренди нақлиет ҳатмист.',
                'model_required'        => 'Модели нақлиетро ворид кунед.',
                'number_plate_required' => 'Рақами қайди нақлиет лозим аст.',
                'number_plate_unique'   => 'Ин рақами қайдшуда аллакай истифода шудааст.',
                'vehicle_image_invalid' => 'Сурат бояд дар формати JPG, JPEG ё PNG бошад.',
            ],
        ],
    ],

    'ride' => [
            'create' => [
            'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
                'success' => 'Сафар бо муваффақият эҷод шуд.',
                'validation' => [
                    'vehicle_id_required'      => 'ID-и мошин лозим аст.',
                    'vehicle_id_exists'        => 'Мошини интихобшуда нодуруст аст.',
                    'pickup_location_required' => 'Ҷойи гирифтани мусофир лозим аст.',
                    'destination_required'     => 'Ҷойи таъинотро ворид кунед..',
                    'number_of_seats_required' => 'Шумораи ҷойҳо лозим аст.',
                    'number_of_seats_integer'  => 'Шумораи ҷойҳо бояд адади бутун бошад.',
                    'price_required'           => 'Нархи сафар лозим аст..',
                    'price_numeric'            => 'Нарх бояд рақам бошад.',
                    'ride_date_required'       => 'Санаи сафар лозим аст.',
                    'ride_date_after_or_equal' => 'Санаи сафар бояд имрӯз ё баъд аз он бошад.',
                    'ride_time_required'       => 'Вақти сафар лозим аст.',
                    'ride_time_format'         => 'Вақт бояд дар формати СС:ДД бошад.',
                    'accept_parcel_boolean'    => 'Қабули бор бояд "ҳа" ё "не" бошад.',
                    'services_exists'          => 'Як ё якчанд хизматҳои интихобшуда нодурустанд.',
                    'reaching_time_format'     => 'Вақти расидан бояд дар формати 24-соатӣ (СС:ДД) бошад.',
                ],
            ],


            'edit' => [
            'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
            'ride_not_found' => 'Сафар ёфт нашуд ё дастрасӣ надоред.',
            'success' => 'Маълумоти сафар муваффақона нав шуд.',
            'validation' => [
                'ride_id_required' => 'Лутфан ID-и сафарро нишон диҳед.',
                'ride_not_found'   => 'Сафар интихобшуда пайдо нашуд.',
                'vehicle_id_required' => 'ID мошин лозим аст.',
                'vehicle_not_found'   => 'Мошини интихобшуда нодуруст аст.',
                'pickup_location_required' => 'Ҷойи гирифтани мусофир лозим аст.',
                 'destination_required'     => 'Ҷойи таъинотро ворид кунед..',
                'number_of_seats_required' => 'Шумораи ҷойҳо лозим аст.',
                'number_of_seats_integer'  => 'Шумораи ҷойҳо бояд адади бутун бошад.',
                'price_required'           => 'Нархи сафар лозим аст..',
                'price_numeric'            => 'Нарх бояд рақам бошад.',
                'ride_date_required'       => 'Санаи сафар лозим аст.',
                'ride_date_after_or_equal' => 'Санаи сафар бояд имрӯз ё баъд аз он бошад.',
                'ride_time_required'       => 'Вақти сафар лозим аст.',
                'ride_time_format'         => 'Вақт бояд дар формати СС:ДД бошад.',
                'accept_parcel_boolean'    => 'Қабули бор бояд "ҳа" ё "не" бошад.',
                'services_exists'          => 'Як ё якчанд хизматҳои интихобшуда нодурустанд.',
                'reaching_time_format'     => 'Вақти расидан бояд дар формати 24-соатӣ (СС:ДД) бошад.',
            ],
        ],

        'driver_rides' => [
             'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
            'success' => 'Рӯйхати сафарҳои ронанда бо муваффақият бор шуд.',
        ],


    ],


    'driver' => [
        'details' => [
            'validation' => [
                'user_id_required' => 'ID-и корбар лозим аст..',
                'user_id_integer'  => 'ID-и корбар бояд рақам бошад.',
                'user_id_exists'   => 'Ронанда ёфт нашуд.',
            ],
            'success' => 'Маълумоти ронанда муваффақона бор шуд.',
             'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
        ],
    ],


   'enquiry' => [
        'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
        'success' => 'Дархост бо муваффақият фиристода шуд.',
        'fetch_success'           => 'Дархостҳо бо муваффақият гирифта шуданд.',
        'validation' => [
            'title_required'       => 'Ном лозим аст..',
            'description_required' => 'Тавсифи дархост лозим аст.',
        ],
    ],


    'rating' => [
       'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
        'ride_not_found' => 'Сафаре, ки интихоб шуд, ёфт нашуд.',
        'reviewed_user_not_found' => 'Корбари интихобшуда ёфт нашуд.',
         'booking_required' => 'Шумо ин сафарро захира накардаед ва наметавонед баррасӣ кунед.',
        'already_rated' => 'Шумо аллакай ин сафар/корбарро баҳо додаед.',
        'success' => 'Рейтинг бо муваффақият фиристода шуд.',
         'fetch_success' => 'Рӯйхати рейтингҳо бо муваффақият бор шуд.',
        'validation' => [
            'ride_id_required' => 'Лутфан ID-и сафарро нишон диҳед.',
            'ride_id_exists' => 'Сафаре, ки интихоб шуд, ёфт нашуд..',
            'reviewed_id_required' => 'Лутфан як корбарро барои баррасӣ интихоб кунед.',
            'reviewed_id_exists' => 'Корбари интихобшуда ёфт нашуд.',
            'rating_required' => 'Рейтинг лозим аст.',
            'rating_integer' => 'Рейтинг бояд рақам бошад.',
            'rating_min' => 'Рейтинг бояд ҳадди ақал 1 ситора бошад.',
            'rating_max' => 'Рейтинг набояд аз 5 ситора зиёд бошад.',
            'review_string' => 'Шарҳ бояд матнӣ бошад.',
            'review_max' => 'Шарҳ набояд аз 500 аломат зиёд бошад.',
        ],
    ],


    'createRideRequest' => [
       'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
        'success' => 'Дархости сафар бо муваффақият эҷод шуд.',
        'validation' => [
            'pickup_location_required' => 'Ҷойи гирифтани мусофир лозим аст.',
            'pickup_location_string' => 'Ҷойи гирифтани мусофир бояд дуруст ворид шавад.',
            'pickup_location_max' => 'Ҷойи гирифтани мусофир набояд аз 255 аломат зиёд бошад.',
            'destination_required' => 'Ҷойи таъинот лозим аст.',
            'destination_string' => 'Ҷойи таъинот бояд дуруст ворид шавад.',
            'destination_max' => 'Ҷойи таъинот набояд аз 255 аломат зиёд бошад.',
            'ride_date_required' => 'Санаи сафар лозим аст.',
            'ride_date_format' => 'Сана бояд дар формати РР-ММ-СССС бошад.',
            'ride_date_after_or_equal' => 'Санаи сафар бояд имрӯз ё баъд аз он бошад.',
            'number_of_seats_integer' => 'Шумораи ҷойҳо бояд рақам бошад.',
            'number_of_seats_min' => 'Ҳадди ақал як ҷой лозим аст.',
            'services_array' => 'Рӯйхати хизматҳо нодуруст ворид шудааст.',
            'services_exists' => 'Як ё якчанд хизматҳои интихобшуда нодурустанд.',
            'budget_required' => 'Буҷет лозим аст.',
            'budget_numeric' => 'Буҷет бояд рақам бошад.',
            'budget_min' => 'Буҷет бояд аз сифр кам набошад.',
            'preferred_time_format' => 'Вақт бояд дар формати СС:ДД бошад.',
        ],
    ],

    'createParcelRequest' => [
        'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
        'success' => 'Parcel request created successfully.',
        'validation' => [
            'pickup_location_required' => 'Ҷойи гирифтани мусофир лозим аст.',
            'pickup_location_string' => 'Ҷойи гирифтани мусофир бояд дуруст ворид шавад.',
            'pickup_location_max' => 'Ҷойи гирифтани мусофир набояд аз 255 аломат зиёд бошад.',
            'destination_required' => 'Ҷойи таъинот лозим аст.',
            'destination_string' => 'Ҷойи таъинот бояд дуруст ворид шавад.',
            'destination_max' => 'Ҷойи таъинот набояд аз 255 аломат зиёд бошад.',
            'ride_date_required' => 'Санаи сафар лозим аст.',
            'ride_date_format' => 'Сана бояд дар формати РР-ММ-СССС бошад.',
            'ride_date_after_or_equal' => 'Санаи сафар бояд имрӯз ё баъд аз он бошад.',
            'ride_time_required' => 'Вақти сафар лозим аст.',
            'ride_time_format' => 'Вақт бояд дар формати СС:ДД бошад.',
            'pickup_contact_name_required' => 'Номи шахси тамос барои гирифтани мусофир лозим аст.',
            'pickup_contact_no_required' => 'Рақами шахси тамос барои гирифтани мусофир лозим аст.',
            'drop_contact_name_required' => 'Номи шахси тамос барои расонидан лозим аст.',
            'drop_contact_no_required' => 'Рақами шахси тамос барои расонидан лозим аст.',
            'parcel_details_required' => 'Маълумоти бор лозим аст.',
            'parcel_images_image' => 'Ҳар файл бояд расм бошад..',
            'parcel_images_mimes' => 'Сурат бояд дар формати JPG, JPEG, PNG ё GIF бошад.',
            'parcel_images_max' => 'Андозаи расм набояд аз 2MB зиёд бошад.',
            'budget_required' => 'Буҷет лозим аст..',
            'budget_numeric' => 'Буҷет бояд рақам бошад.',
            'budget_min' => 'Буҷет бояд аз сифр кам набошад.',
            'preferred_time_format' => 'Вақт бояд дар формати СС:ДД бошад.',
        ],
    ],


    'getAllRideRequests' => [
        'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
        'ride_requests_retrieved' => 'Дархостҳои сафар бо муваффақият бор шуданд..',
        'validation' => [
             'pickup_location_required' => 'Ҷойи гирифтани мусофир лозим аст.',
            'pickup_location_string' => 'Ҷойи гирифтани мусофир бояд дуруст ворид шавад.',
            'pickup_location_max' => 'Ҷойи гирифтани мусофир набояд аз 255 аломат зиёд бошад.',
            'destination_required' => 'Ҷойи таъинот лозим аст.',
            'destination_string' => 'Ҷойи таъинот бояд дуруст ворид шавад.',
            'destination_max' => 'Ҷойи таъинот набояд аз 255 аломат зиёд бошад.',
            'ride_date_required' => 'Санаи сафар лозим аст.',
            'ride_date_format' => 'Сана бояд дар формати РР-ММ-СССС бошад.',
            'ride_date_after_or_equal' => 'Санаи сафар бояд имрӯз ё баъд аз он бошад.',
            'number_of_seats_integer' => 'Шумораи ҷойҳо бояд рақам бошад.',
            'number_of_seats_min' => 'Ҳадди ақал як ҷой лозим аст.',
        ],
        'invalid_ride_date_format' => 'Формати сана нодуруст аст. Аз формати РР-ММ-СССС истифода баред.',
    ],


    'getAllParcelRequests' => [
         'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
        'parcel_requests_retrieved' => 'Дархостҳои посылка бомуваффақият гирифта шуданд.',
        'validation' => [
             'pickup_location_required' => 'Ҷойи гирифтани мусофир лозим аст.',
            'pickup_location_string' => 'Ҷойи гирифтани мусофир бояд дуруст ворид шавад.',
            'pickup_location_max' => 'Ҷойи гирифтани мусофир набояд аз 255 аломат зиёд бошад.',
            'destination_required' => 'Ҷойи таъинот лозим аст.',
            'destination_string' => 'Ҷойи таъинот бояд дуруст ворид шавад.',
            'destination_max' => 'Ҷойи таъинот набояд аз 255 аломат зиёд бошад.',
            'ride_date_required' => 'Санаи сафар лозим аст.',
            'ride_date_format' => 'Сана бояд дар формати РР-ММ-СССС бошад.',
            'ride_date_after_or_equal' => 'Санаи сафар бояд имрӯз ё баъд аз он бошад.',
            'number_of_seats_integer' => 'Шумораи ҷойҳо бояд рақам бошад.',
            'number_of_seats_min' => 'Ҳадди ақал як ҷой лозим аст.',
        ],
        'invalid_ride_date_format' => 'Формати сана нодуруст аст. Аз формати РР-ММ-СССС истифода баред.',
    ],


    'listCurrentPassengerRequests' => [
        'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
        'requests_retrieved' => 'Дархостҳои мусофирон бо муваффақият гирифта шуданд.',
    ],

    'getInterestedDrivers' => [
        'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
        'request_not_found' => 'Дархост ёфт нашуд.',
        'drivers_retrieved' => 'Ронандагони манфиатдор бо ҷузъиёти сафар бо муваффақият гирифта шуданд.',
    ],


    'updateRequestInterestStatus' => [
        'driver_not_authenticated' => 'Ронанда тасдиқ нашудааст.',
        'already_interested'       => 'Шумо аллакай манфиат ҷиҳати ин дархост нишон додед.',
        'success'                  => 'Ронанда бо муваффақият манфиат нишон дод.',
        'default_driver_name'      => 'Ронанда',

        'notification' => [
            'title' => 'Ронанда манфиатдор аст',
            'body'  => 'Ронанда :driverName ба дархости шумо барои сафар аз :pickup то :destination манфиат нишон дод. Лутфан тасдиқ кунед.',
        ],

        'validation' => [
            'request_id_required' => 'ID-и дархост зарур аст.',
            'request_id_exists'   => 'Ин дархост вуҷуд надорад.',
        ],
    ],


    'confirmDriverByPassenger' => [
        'passenger_not_authenticated' => 'Шумо бояд ҳамчун мусофир ворид шавед, то ронандаро тасдиқ кунед.',
        'request_not_found'           => 'Дархости сафар ёфт нашуд ё ба шумо тааллуқ надорад.',
        'already_confirmed'           => 'Барои ин дархост ронанда аллакай тасдиқ шудааст.',
        'driver_not_interested'       => 'Ронандаи интихобшуда ба ин сафар таваҷҷӯҳ нишон надодааст.',
        'declined_success'            => 'Таваҷҷӯҳи ронанда рад ва муваффақона ҳазф шуд.',
        'success'                     => 'Ронанда муваффақона тасдиқ шуд ва брон сохта шуд.',
        'default_passenger_name'      => 'Мусофир',

        'notification' => [
            'confirmed' => [
                'title' => 'Дархости сафар тасдиқ шуд',
                'body'  => ':passengerName таваҷҷӯҳ ва иштироки шуморо барои сафар аз :pickup то :destination тасдиқ кард.',
            ],
            'declined' => [
                'title' => 'Дархости сафар рад шуд',
                'body'  => ':passengerName таваҷҷӯҳи шуморо барои сафар аз :pickup то :destination рад кард.',
            ],
        ],

        'validation' => [
            'request_id_required' => 'ID-и дархост талаб карда мешавад.',
            'request_id_exists'   => 'Ин дархости сафар вуҷуд надорад.',
            'driver_id_required'  => 'Шумо бояд ронандаро барои тасдиқ интихоб кунед.',
            'driver_id_exists'    => 'Ронандаи интихобшуда вуҷуд надорад.',
            'status_required'     => 'Статус талаб карда мешавад.',
            'status_in'           => 'Статус бояд confirmed ё declined бошад.',
        ],
    ],




];
