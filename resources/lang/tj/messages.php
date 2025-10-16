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

];
