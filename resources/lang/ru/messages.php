<?php

return [

    'login' => [
        'otp_sent_register' => 'OTP бо муваффақият фиристода шуд. Барои анҷоми сабти ном OTP-ро тасдиқ кунед.',
        'otp_sent_login' => 'OTP бо муваффақият фиристода шуд. Барои ворид шудан OTP-ро тасдиқ кунед.',
        'blocked_by_admin' => 'Шумо аз ҷониби администратор маҳдуд ҳастед.',
        'account_deleted' => 'Ин ҳисоб нест карда шудааст.',
        'user_not_found' => 'Корбар пайдо нашуд.',
        'invalid_or_expired_otp' => 'OTP нодуруст ё муҳлаташ гузашт.',
        'otp_verified_success' => 'OTP бо муваффақият тасдиқ шуд. Шумо ворид шудед.',

        'validation' => [
            'phone_required' => 'Рақами телефон ҳатмист.',
            'phone_digits_between' => 'Рақами телефон бояд аз 8 то 15 рақам бошад.',
            'otp_required' => 'OTP ҳатмист.',
            'otp_digits' => 'OTP бояд 6 рақам дошта бошад.',
            'device_type_string' => 'Намуди дастгоҳ бояд матн бошад.',
            'device_id_string' => 'ID-и дастгоҳ бояд матн бошад.',
            'fcm_token_string' => 'Токени FCM бояд матн бошад.',
        ],
    ],

    'otp_verify' => [
        'user_not_found' => 'Корбар пайдо нашуд.',
        'invalid_or_expired_otp' => 'OTP нодуруст ё муҳлаташ гузашт.',
        'otp_verified_success' => 'OTP бо муваффақият тасдиқ шуд. Шумо ворид шудед.',

        'validation' => [
            'phone_required' => 'Рақами телефон ҳатмист.',
            'phone_digits_between' => 'Рақами телефон бояд аз 8 то 15 рақам бошад.',
            'otp_required' => 'OTP ҳатмист.',
            'otp_digits' => 'OTP бояд 6 рақам дошта бошад.',
            'device_type_string' => 'Намуди дастгоҳ бояд матн бошад.',
            'device_id_string' => 'ID-и дастгоҳ бояд матн бошад.',
            'fcm_token_string' => 'Токени FCM бояд матн бошад.',
        ],
    ],

    'language' => [
        'updated' => 'Забон бо муваффақият навсозӣ шуд.',
        'validation' => [
            'required' => 'Лутфан забонро интихоб кунед.',
            'in' => 'Забони интихобшуда нодуруст аст.',
        ],
    ],

    'logout' => [
        'logout_success' => 'LВы успешно вышли из системы..',
         'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему.',
    ],

    'getProfile' => [
        'success' => 'Профиль загружен.',
        'user_not_authenticated' => 'User not authenticated.',
    ],

     'profile' => [
        'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему.',
        'updated_successfully' => 'Профиль успешно обновлён.',
        'validation' => [
            'name_required' => 'Пожалуйста, введите имя.',
            'name_string' => 'Имя должно быть корректным.',
            'name_max' => 'Имя не должно превышать 255 символов.',
            'gender_in' => 'Укажите пол: мужской, женский или другой.',
            'profile_file' => 'Загрузите изображение профиля.',
            'profile_mimes' => 'Фото должно быть в формате JPEG, PNG или JPG.',
            'profile_max' => 'Фото не должно превышать 4 МБ.',
            'govid_array' => 'Документы должны быть массивом.',
            'govid_file' => 'Каждый документ должен быть файлом.',
            'govid_mimes' => 'Документ должен быть в формате JPEG, PNG, JPG или PDF.',
            'govid_max' => 'Каждый документ не должен превышать 4 МБ.',
            'invalid_dob_format' => 'Неверный формат даты. Используйте ДД-ММ-ГГГГ.',
        ],
    ],

     'deleteAccount' => [
        'success' => 'Ваш аккаунт был успешно удалён.',
         'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему.',
    ],

     'bookRideOrParcel' => [
        'user_not_authenticated' => 'User not authenticated.',
        'booking_created' => 'Booking created successfully.',
        'validation' => [
            'ride_id_required' => 'Пожалуйста, укажите ID поездки.',
            'ride_not_exist' => 'Выбранная поездка не найдена.',
            'seats_required' => 'Укажите количество мест.',
            'seats_invalid' => 'Количество мест должно быть числом.',
            'seats_min' => 'Количество мест должно быть хотя бы 1.',
            'services_array' => 'Услуги должны быть массивом.',
            'service_invalid' => 'Выбранная услуга недействительна.',
            'type_required' => 'Укажите тип бронирования.',
            'type_invalid' => 'Тип должен быть 0 (поездка) или 1 (посылка).',
            'comment_invalid' => 'Комментарий должен быть текстом.',
            'comment_max' => 'Комментарий не должен превышать 500 символов.',
            'cannot_book_own' => 'Вы не можете забронировать свою поездку.',
            'already_booked_both' => 'You have already booked ride and parcel.',
            'already_booked_ride' => 'You have already booked this ride.',
            'already_booked_parcel' => 'You have already booked this parcel.',
        ],
    ],

    'getDriverBookings' => [
        'driver_not_authenticated' => 'Водитель не авторизован..',
        'success' => 'Список бронирований загружен.',
    ],

    'getPassengerBookingRequests' => [
        'passenger_not_authenticated' => 'Пассажир не авторизован.',
        'success' => 'Ваши запросы на поездку загружены..',
    ],

    'confirmBooking' => [
       'driver_not_authenticated' => 'Водитель не авторизован.',
        'unauthorized'             => 'вас нет прав для изменения этого бронирования.',
        'not_enough_seats'         => 'Недостаточно мест. Бронирование автоматически отменено.',
        'success'                  => 'Бронирование успешно :status.',
        
        'notification' => [
            'title' => 'Бронирование :status',
            'body'  => 'Ваше бронирование от :pickup до :destination было :status водителем :driver.',
        ],

        'validation' => [
            'booking_id_required' => 'Укажите ID бронирования.',
            'booking_not_exist'   => 'Такое бронирование не найдено.',
            'status_required'     => 'Укажите статус для обновления бронирования.',
            'status_invalid'      => 'Статус должен быть «подтвержден» или «отменен».',
        ],
    ],


    'updateBookingActiveStatus' => [
        'driver_not_authenticated' => 'Водитель не авторизован.',
        'unauthorized'             => 'Вы не имеете права начать это бронирование.',
        'invalid_structure'        => 'Недопустимая структура бронирования (отсутствует поездка или запрос).',
        'booking_not_found'        => 'Бронирование не найдено..',
        'success'                  => 'Статус бронирования изменён на «активный».',

        'notification' => [
            'title' => 'Бронирование активировано',
            'body'  => 'Ваше бронирование на поездку из :pickup в :destination было начато.',
        ],

        'validation' => [
            'booking_id_required' => 'Укажите ID бронирования..',
            'booking_not_exist'   => 'Такое бронирование не найдено.',
        ],
    ],


    'updateBookingCompleteStatus' => [
       'driver_not_authenticated' => 'Водитель не авторизован.',
        'unauthorized'             => 'Вы не имеете права завершить это бронирование.',
        'invalid_structure'        => 'Недопустимая структура бронирования (отсутствует поездка или запрос).',
        'booking_not_found'        => 'Бронирование не найдено..',
        'success'                  => 'Статус бронирования успешно обновлён на завершённый.',

        'notification' => [
            'title' => 'Бронирование завершено',
            'body'  => 'Ваше бронирование на поездку из :pickup в :destination было завершено.',
        ],

        'validation' => [
            'booking_id_required' => 'Укажите ID бронирования..',
            'booking_not_exist'   => 'Такое бронирование не найдено.',
        ],
    ],

    'getConfirmationStatus' => [
        'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему.',
        'success'                => 'Список поездок водителя успешно загружен.',
         'no_rides_found'         => 'По выбранному статусу поездок не найдено.',

        'validation' => [
            'status_type_invalid' => 'Неверный тип статуса. Должен быть: active, completed или cancelled.',
        ],
    ],


    'getSendResponse' => [
        'user_not_authenticated' => 'Пользователь не авторизован.',
        'success'                => 'Отправленные элементы успешно получены.',
        'no_items_found'         => 'Нет отправленных бронирований или запросов.',

            'types' => [
                'booking'          => 'Бронирование',
                'request_interest' => 'Интерес к запросу пассажира',
            ],

            'notification' => [
                'title' => 'Обновление бронирования/запроса',
                'body'  => 'Ваше бронирование или запрос пассажира обновлено.',
            ],

            'validation' => [
                'invalid_user' => 'Неверный пользователь.',
            ],
    ],


    'getReceivedResponse' => [
            'user_not_authenticated' => 'Пользователь не аутентифицирован.',
             'success'                 => 'Запросы на получение успешно получены.',
            
             'created_by' => [
                'driver'    => 'водитель',
                'passenger' => 'пассажир',
            ],

            'rides_with_bookings' => [
                'no_bookings' => 'Активные бронирования для этой поездки не найдены.',
            ],

            'passenger_requests' => [
                'no_requests' => 'Активные запросы пассажиров не найдены.',
            ],

            'driver_info' => [
                'name'             => 'Имя водителя',
                'phone_number'     => 'Номер телефона',
                'email'            => 'Электронная почта',
                'image'            => 'Фото профиля',
                'dob'              => 'Дата рождения',
                'gender'           => 'Пол',
                'id_verified'      => 'ID подтверждено',
                'is_phone_verify'  => 'Телефон подтверждён',
                'device_type'      => 'Тип устройства',
                'device_id'        => 'ID устройства',
            ],

            'vehicle_info' => [
                'vehicle_number' => 'Номер автомобиля',
                'vehicle_type'   => 'Тип транспортного средства',
                'vehicle_name'   => 'Марка автомобиля',
                'vehicle_model'  => 'Модель автомобиля',
                'vehicle_image'  => 'Изображение автомобиля',
            ],
     ],

    'start' => [
        'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему.',
        'success'                => 'Чат успешно создан.',

        'validation' => [
            'other_user_id_required'  => 'Укажите ID собеседника.',
            'other_user_id_not_exist' => 'Пользователь для чата не найден.',
            'other_user_id_not_in'    => 'Нельзя начать чат с самим собой.',
        ],
    ],


    'allConversation' => [
       'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему..',
        'success'                => 'Список чатов успешно загружен.',


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
        'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему.',
        'success'                => 'Сообщение успешно отправлено.',
        'not_participant'        => 'Вы не участник этого чата.',

        'validation' => [
            'conversation_not_exist' => 'Чат не найден.',
            'user_not_exist'         => 'Пользователь не существует.',
            'message_required'       => 'Нельзя отправить пустое сообщение.',
            'message_string'         => 'Сообщение должно содержать текст.',
            'message_max'            => 'Сообщение слишком длинное (до 5000 символов).',
            'type_invalid'           => 'Неверный тип сообщения.',
        ],

        'notification' => [
            'title' => '💬 Новое сообщение',
            'body'  => ':sender отправил вам сообщение: ":message"',
        ],
    ],

    'allMessages' => [
        'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему.',
        'success'                => 'Сообщения успешно загружены.',
        'not_participant'        => 'Вы не можете участвовать в этом чате.',

        'validation' => [
            'conversation_not_exist' => 'Такой чат не найден.',
            'conversation_required'  => 'Укажите ID чата..',
        ],
    ],


    'vehicle' => [
        'add_vehicle' => [
            'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему.',
            'success'                => 'Автомобиль успешно добавлен.',

        'validation' => [
                'brand_required'        => 'Укажите марку автомобиля.',
                'model_required'        => 'Укажите модель автомобиля.',
                'number_plate_required' => 'Введите номерной знак автомобиля.',
                'number_plate_unique'   => 'Этот номер уже зарегистрирован.',
                'vehicle_image_image'   => 'Фото автомобиля должно быть изображением.',
                'vehicle_image_mimes'   => 'Фото должно быть в формате JPG, JPEG или PNG.',
            ],
        ],

        'get_vehicles' => [
            'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему.',
            'success'                => 'Список автомобилей успешно загружен.',
            'no_vehicles_found'      => 'У вас ещё нет добавленных автомобилей.',
        ],


        'edit_vehicle' => [
            'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему.',
            'success'                => 'Данные автомобиля успешно обновлены..',
            'vehicle_not_found'      => 'Машина не найдена или не принадлежит вам.',
            'validation' => [
                'vehicle_id_required'   => 'Не указан ID автомобиля.',
                'vehicle_not_found'     => 'Машина не найдена или не принадлежит вам.',
                'brand_required'        => 'Укажите марку автомобиля..',
                'model_required'        => 'Укажите модель автомобиля.',
                'number_plate_required' => 'Введите номерной знак автомобиля.',
                'number_plate_unique'   => 'Этот номер уже зарегистрирован.',
                'vehicle_image_invalid' => 'Фото должно быть в формате JPG, JPEG или PNG.',
            ],
        ],

    ],


    'ride' => [
        'create' => [
            'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему.',
            'success' => 'Поездка успешно создана.',
            'validation' => [
                'vehicle_id_required'      => 'Идентификатор автомобиля обязателен.',
                'vehicle_id_exists'        => 'Выбранный автомобиль недействителен.',
                'pickup_location_required' => 'Укажите место отправления.',
                'destination_required'     => 'Укажите пункт назначения.',
                'number_of_seats_required' => 'Укажите количество мест..',
                'number_of_seats_integer'  => 'Количество мест должно быть целым числом.',
                'price_required'           => 'Укажите стоимость поездки.',
                'price_numeric'            => 'Цена должна быть числом..',
                'ride_date_required'       => 'Укажите дату поездки.',
                'ride_date_after_or_equal' => 'RДата поездки должна быть сегодняшней или позже..',
                'ride_time_required'       => 'Укажите время отправления.',
                'ride_time_format'         => 'Время должно быть в формате ЧЧ:ММ..',
                'accept_parcel_boolean'    => 'Поле «Принимать посылки» должно быть Да или Нет.',
                'services_exists'          => 'Одно или несколько выбранных услуг недействительны.',
                'reaching_time_format'     => 'Время прибытия должно быть в 24-часовом формате (ЧЧ:ММ).',
            ],
        ],

        'edit' => [
           'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему.',
            'ride_not_found' => 'Поездка не найдена или у вас нет доступа.',
            'success' => 'Поездка успешно обновлена.',
            'validation' => [
                'ride_id_required' => 'Пожалуйста, укажите ID поездки.',
                'ride_not_found'   => 'Выбранная поездка не найдена.',
                'vehicle_id_required' => 'Не указан ID автомобиля.',
                'vehicle_not_found'   => 'Выбранный автомобиль недействителен.',
                'pickup_location_required' => 'Укажите место отправления.',
                'destination_required'     => 'Укажите пункт назначения.',
                'number_of_seats_required' => 'Укажите количество мест..',
                'number_of_seats_integer'  => 'Количество мест должно быть целым числом.',
                'price_required'           => 'Укажите стоимость поездки.',
                'price_numeric'            => 'Цена должна быть числом..',
               'ride_date_required'       => 'Укажите дату поездки.',
                'ride_date_after_or_equal' => 'RДата поездки должна быть сегодняшней или позже..',
                'ride_time_required'       => 'Укажите время отправления.',
                'ride_time_format'         => 'Время должно быть в формате ЧЧ:ММ..',
                'reaching_time_format'     => 'Время прибытия должно быть в 24-часовом формате (ЧЧ:ММ).',
                'accept_parcel_boolean'    => 'Поле «Принимать посылки» должно быть Да или Нет.',
                'services_exists'          => 'Одно или несколько выбранных услуг недействительны.',
            ],
            
        ],

        'driver_rides' => [
            'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему.',
            'success' => 'Список поездок водителя успешно загружен.y.',
        ],

       

    ],


    'driver' => [
        'details' => [
            'validation' => [
                'user_id_required' => 'Не указан идентификатор пользователя.',
                'user_id_integer'  => 'ID пользователя должен быть числом.',
                'user_id_exists'   => 'Водитель не найден.',
            ],
            'success' => 'Данные водителя успешно загружены..',
             'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему.',
        ],
    ],

   'enquiry' => [
        'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему.',
        'success' => 'Запрос успешно отправлен.',
         'fetch_success'           => 'Запросы успешно получены.',
        'validation' => [
            'title_required'       => 'Укажите название.',
            'description_required' => 'Укажите описание.',
        ],
    ],


    'rating' => [
        'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему.',
        'ride_not_found' => 'Выбранная поездка не найдена.',
        'reviewed_user_not_found' => 'Выбранный пользователь не найден.',
       'booking_required' => 'Вы не бронировали эту поездку и не можете оставить отзыв.',
        'already_rated' => 'Вы уже оценили эту поездку/пользователя.',
        'success' => 'Оценка успешно отправлена.',
         'fetch_success' => 'Список оценок успешно загружен.',
        'validation' => [
            'ride_id_required' => 'Пожалуйста, укажите ID поездки.',
            'ride_id_exists' => 'Выбранная поездка не найдена.',
            'reviewed_id_required' => 'Пожалуйста, выберите пользователя для отзыва.',
            'reviewed_id_exists' => 'Выбранный пользователь не найден..',
            'rating_required' => 'Укажите оценку.',
            'rating_integer' => 'Оценка должна быть числом.',
            'rating_min' => 'Минимальная оценка — 1 звезда.',
            'rating_max' => 'Максимальная оценка — 5 звезд.',
            'review_string' => 'Отзыв должен быть текстом.',
            'review_max' => 'Отзыв не должен превышать 500 символов.',
        ],
    ],


     'createRideRequest' => [
       'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему.',
        'success' => 'Заявка на поездку успешно создана.',
        'validation' => [
            'pickup_location_required' => 'Укажите место отправления.',
            'pickup_location_string' => 'Адрес отправления должен быть корректным.',
            'pickup_location_max' => 'Адрес отправления слишком длинный (до 255 символов).',
            'destination_required' => 'Укажите пункт назначения.',
            'destination_string' => 'Адрес назначения должен быть корректным.',
            'destination_max' => 'Адрес назначения слишком длинный (до 255 символов).',
            'ride_date_required' => 'Укажите дату поездки.',
            'ride_date_format' => 'Используйте формат даты ДД-ММ-ГГГГ.',
            'ride_date_after_or_equal' => 'Дата поездки должна быть сегодняшней или позже.',
            'number_of_seats_integer' => 'Количество мест должно быть числом.',
            'number_of_seats_min' => 'Укажите хотя бы одно место.',
            'services_array' => 'Формат списка услуг неверный.',
            'services_exists' => 'Одно или несколько выбранных услуг недействительны.',
            'budget_required' => 'Укажите бюджет.',
            'budget_numeric' => 'Бюджет должен быть числом.',
            'budget_min' => 'Бюджет не может быть отрицательным.',
            'preferred_time_format' => 'Время должно быть в формате ЧЧ:ММ.',
        ],
    ],


    'createParcelRequest' => [
       'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему.',
        'success' => 'Parcel request created successfully.',
        'validation' => [
           'pickup_location_required' => 'Укажите место отправления.',
            'pickup_location_string' => 'Адрес отправления должен быть корректным.',
            'pickup_location_max' => 'Адрес отправления слишком длинный (до 255 символов).',
            'destination_required' => 'Укажите пункт назначения.',
            'destination_string' => 'Адрес назначения должен быть корректным.',
            'destination_max' => 'Адрес назначения слишком длинный (до 255 символов).',
            'ride_date_required' => 'Укажите дату поездки.',
            'ride_date_format' => 'Используйте формат даты ДД-ММ-ГГГГ.',
            'ride_date_after_or_equal' => 'Дата поездки должна быть сегодняшней или позже.',
            'ride_time_required' => 'Укажите время поездки..',
            'ride_time_format' => 'Время должно быть в формате ЧЧ:ММ..',
            'pickup_contact_name_required' => 'Укажите имя контактного лица для отправления.',
            'pickup_contact_no_required' => 'Укажите номер контакта для отправления..',
            'drop_contact_name_required' => 'Укажите имя контактного лица для доставки..',
            'drop_contact_no_required' => 'Укажите номер контакта для доставки.',
            'parcel_details_required' => 'Укажите данные о посылке.',
            'parcel_images_image' => 'Каждый файл должен быть изображением.',
            'parcel_images_mimes' => 'Изображение должно быть в формате JPG, JPEG, PNG или GIF.',
            'parcel_images_max' => 'Размер изображения не должен превышать 2 МБ.',
            'budget_required' => 'Укажите бюджет..',
            'budget_numeric' => 'Бюджет должен быть числом.',
            'budget_min' => 'Бюджет не может быть отрицательным.',
            'preferred_time_format' => 'Время должно быть в формате ЧЧ:ММ..',
        ],
    ],


     'getAllRideRequests' => [
        'user_not_authenticated' => 'Вы не авторизованы. Пожалуйста, войдите в систему.',
        'ride_requests_retrieved' => 'Заявки на поездку успешно загружены.',
        'validation' => [
             'pickup_location_required' => 'Укажите место отправления.',
            'pickup_location_string' => 'Адрес отправления должен быть корректным.',
            'pickup_location_max' => 'Адрес отправления слишком длинный (до 255 символов).',
            'destination_required' => 'Укажите пункт назначения.',
            'destination_string' => 'Адрес назначения должен быть корректным.',

            'ride_date_required' => 'Укажите дату поездки.',
            'ride_date_format' => 'Используйте формат даты ДД-ММ-ГГГГ.',
            'ride_date_after_or_equal' => 'Дата поездки должна быть сегодняшней или позже.',
            'number_of_seats_integer' => 'Количество мест должно быть числом.',
            'number_of_seats_min' => 'Укажите хотя бы одно место.',
        ],
        'invalid_ride_date_format' => 'Неверный формат даты. Используйте ДД-ММ-ГГГГ.',
    ],


    'getAllParcelRequests' => [
         'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
         'parcel_requests_retrieved' => 'Запросы на посылки успешно получены.',
        'validation' => [
             'pickup_location_required' => 'Укажите место отправления.',
            'pickup_location_string' => 'Адрес отправления должен быть корректным.',
            'pickup_location_max' => 'Адрес отправления слишком длинный (до 255 символов).',
            'destination_required' => 'Укажите пункт назначения.',
            'destination_string' => 'Адрес назначения должен быть корректным.',

            'ride_date_required' => 'Укажите дату поездки.',
            'ride_date_format' => 'Используйте формат даты ДД-ММ-ГГГГ.',
            'ride_date_after_or_equal' => 'Дата поездки должна быть сегодняшней или позже.',
            'number_of_seats_integer' => 'Количество мест должно быть числом.',
            'number_of_seats_min' => 'Укажите хотя бы одно место.',
        ],
        'invalid_ride_date_format' => 'Неверный формат даты. Используйте ДД-ММ-ГГГГ.',
    ],


    'listCurrentPassengerRequests' => [
        'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
        'requests_retrieved' => 'Запросы пассажиров успешно получены.',
    ],


    'getInterestedDrivers' => [
         'user_not_authenticated' => 'Шумо тасдиқ нашудаед. Лутфан ворид шавед.',
        'request_not_found' => 'Запрос не найден.',
        'drivers_retrieved' => 'Водители, проявившие интерес, успешно получены с деталями поездки.',
    ],


    'updateRequestInterestStatus' => [
        'driver_not_authenticated' => 'Водитель не аутентифицирован.',
        'already_interested'       => 'Вы уже выразили интерес.',
        'success'                  => 'Водитель успешно выразил интерес.',
        'default_driver_name'      => 'Водитель',

        'notification' => [
            'title' => 'Водитель заинтересован',
            'body'  => 'Водитель :driverName выразил интерес к вашему запросу на поездку от :pickup до :destination. Пожалуйста, подтвердите.',
        ],

        'validation' => [
            'request_id_required' => 'ID запроса обязателен.',
            'request_id_exists'   => 'Такого запроса не существует.',
        ],
    ],


    'confirmDriverByPassenger' => [
        'passenger_not_authenticated' => 'Вы должны быть авторизованы как пассажир, чтобы подтвердить водителя.',
        'request_not_found'           => 'Запрос поездки не найден или не принадлежит вам.',
        'already_confirmed'           => 'Водитель уже подтверждён для этого запроса.',
        'driver_not_interested'       => 'Выбранный водитель не выразил интерес к этой поездке.',
        'declined_success'            => 'Интерес водителя был отклонён и удалён успешно.',
        'success'                     => 'Водитель успешно подтверждён и бронирование создано.',
        'default_passenger_name'      => 'Пассажир',

        'notification' => [
            'confirmed' => [
                'title' => 'Запрос на поездку подтверждён',
                'body'  => ':passengerName подтвердил ваш интерес к поездке от :pickup до :destination.',
            ],
            'declined' => [
                'title' => 'Запрос на поездку отклонён',
                'body'  => ':passengerName отклонил ваш интерес к поездке от :pickup до :destination.',
            ],
        ],

        'validation' => [
            'request_id_required' => 'Требуется ID запроса.',
            'request_id_exists'   => 'Указанный запрос на поездку не существует.',
            'driver_id_required'  => 'Необходимо выбрать водителя для подтверждения.',
            'driver_id_exists'    => 'Выбранный водитель не существует.',
            'status_required'     => 'Требуется указать статус.',
            'status_in'           => 'Статус должен быть "confirmed" или "declined".',
        ],
    ],











];
