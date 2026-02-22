<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удаление аккаунта - QADAMPAYK</title>
    <link rel="icon" href="{{ asset('favicon-qadampayk.png') }}" type="image/x-icon">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        /* HEADER */
        .qadam-policy-head {
            background-color: #e6f4ee;
            padding: 60px 0 40px;
            text-align: center;
        }

        .qadam-logo img {
            width: 140px;
            margin-bottom: 20px;
        }

        .qadam-policy-head h1 {
            font-size: 42px;
            color: #008955;
            font-weight: 700;
        }

        /* CONTENT BOX */
        .qadam-policy-container {
            max-width: 900px;
            margin: 140px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .list-group-item {
            border: none;
            background-color: #f1f5f9;
            margin-bottom: 8px;
            border-radius: 6px;
            color: #008955;
            font-weight: 500;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
            padding: 10px 30px;
            font-size: 18px;
            border-radius: 8px;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
            padding: 10px 30px;
            font-size: 18px;
            border-radius: 8px;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

    <!-- HEADER SECTION -->
    <div class="qadam-policy-head">
        <div class="qadam-logo">
            <img src="{{ asset('assets/admin/images/qadampayk-dash.png') }}" alt="QADAMPAYK Логотип">
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="qadam-policy-container">

        {{-- SUCCESS / ERROR MESSAGES --}}
        @if(session('success'))
            <div class="alert alert-success text-center" id="alert-box">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger text-center" id="alert-box">
                {{ session('error') }}
            </div>
        @endif

        <h3 class="text-center text-danger">Вы уверены, что хотите удалить свой аккаунт?</h3>
        <p class="text-center text-muted">
           Нам жаль, что вы уходите. Пожалуйста, обратите внимание, что после удаления аккаунта:
        </p>

        <ul class="list-group mb-4">
            <li class="list-group-item">• Мы больше не будем хранить записи о ваших заказах или переписке с командой поддержки.</li>
            <li class="list-group-item">• Ваши бронирования, история и сохраненные настройки будут удалены.</li>
            <li class="list-group-item">• Это действие необратимо.</li>
        </ul>

        <div class="text-center">
            @if(!empty($user->id))
            <form method="POST" action="{{ route('delete-account.confirm') }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <button type="submit" class="btn btn-danger btn-lg">
                    Да, удалить мой аккаунт
                </button>
            </form>
            @else
            <div class="alert alert-info">
                Пользователь не найден. Пожалуйста, убедитесь, что вы вошли в систему или предоставили правильную ссылку.
            </div>
            @endif
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Auto-hide alert --}}
    <script>
        setTimeout(() => {
            const alertBox = document.getElementById('alert-box');
            if (alertBox) {
                alertBox.style.transition = 'opacity 0.5s';
                alertBox.style.opacity = '0';
                setTimeout(() => alertBox.remove(), 500);
            }
        }, 4000);
    </script>

</body>
</html>
