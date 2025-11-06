@include('admin.PrivacyPolicy.privacyHeader')

<div class="qadam-privacy-wrapper p-0">
    <div class="container qadam-policy-container">
        <div class="text-center qadam-policy-head">
            <div class="container-xl">
                <div class="qadam-logo">
                    <img src="{{ asset('assets/admin/images/qadampayk-dash.png') }}" alt="QadamPayk Logo">
                </div>
            </div>
            <h1 style="text-align:center; font-size:36px; font-weight:700; color:#008955; margin-bottom:30px;">
                Форма «Пользователи отчета»
            </h1>
        </div>

        <div class="qadam-policy-content" style="max-width: 600px; margin: 0 auto;">
            

            <form id="feedbackForm" style="text-align:left;">
                @csrf

                <div class="form-group mb-4">
                    <label for="email" style="font-weight:600;">Электронная почта</label>
                    <input type="email" name="email" id="email" class="form-control"
                           placeholder="Введите свой адрес электронной почты" required>
                </div>

                <div class="form-group mb-4">
                    <label for="description" style="font-weight:600;">Описание</label>
                    <textarea name="description" id="description" rows="5" class="form-control"
                              placeholder="Напишите здесь свой отзыв..." required></textarea>
                </div>

                <div class="text-center">
                    <button type="submit" id="submitBtn" class="btn btn-success"
                            style="background-color:#008955; border:none; padding:10px 30px; font-weight:600;">
                        представить
                    </button>
                </div><br>
                <div id="feedbackSuccess" class="alert alert-success text-center d-none" role="alert">
                Спасибо за ваш отчет!
            </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    $('#feedbackForm').on('submit', function (e) {
        e.preventDefault(); // stop page reload
        $('#submitBtn').prop('disabled', true).text('Отправка...');

        // Fake 1-second delay to feel real
        setTimeout(function() {
            $('#feedbackSuccess').removeClass('d-none').text('Спасибо за ваш отчет!');
            $('#feedbackForm')[0].reset(); // clear fields
            $('#submitBtn').prop('disabled', false).text('Отправить');

            // hide success message after 3 seconds
            setTimeout(() => {
                $('#feedbackSuccess').addClass('d-none');
            }, 3000);
        }, 1000);
    });
});
</script>