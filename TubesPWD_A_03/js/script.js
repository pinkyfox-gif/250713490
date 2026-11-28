
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('register-form');

    if (!form) {
        return;
    }

    var usernameInput = document.getElementById('username');
    var emailInput = document.getElementById('email');
    var usernameFeedback = document.getElementById('username-feedback');
    var emailFeedback = document.getElementById('email-feedback');
    var clientError = document.getElementById('client-error');
    var csrfToken = form.querySelector('[name="csrf_token"]').value;

    var usernameTimer;
    var emailTimer;

    var usernamePattern = /^[A-Za-z0-9_]{4,30}$/;
    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function showFeedback(element, message, status) {
        element.textContent = message;
        element.className = 'field-feedback ' + status;
    }

    async function checkAvailability(type, value, feedback) {
        value = value.trim();

        if (value === '') {
            showFeedback(feedback, '', '');
            return null;
        }

        if (type === 'username' && !usernamePattern.test(value)) {
            showFeedback(
                feedback,
                'Username harus 4–30 karakter.',
                'unavailable'
            );
            return false;
        }

        if (type === 'email' && !emailPattern.test(value)) {
            showFeedback(
                feedback,
                'Format email tidak valid.',
                'unavailable'
            );
            return false;
        }

        showFeedback(feedback, 'Memeriksa...', 'checking');

        try {
            var response = await fetch('cek_ketersediaan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    type: type,
                    value: value,
                    csrf_token: csrfToken
                })
            });

            var result = await response.json();

            if (!result.success) {
                showFeedback(feedback, result.message, 'unavailable');
                return null;
            }

            showFeedback(
                feedback,
                result.message,
                result.available ? 'available' : 'unavailable'
            );

            return result.available;

        } catch (error) {
            showFeedback(
                feedback,
                'Gagal memeriksa data. Coba lagi.',
                'unavailable'
            );
            return null;
        }
    }

    usernameInput.addEventListener('input', function () {
        clearTimeout(usernameTimer);

        showFeedback(usernameFeedback, '', '');

        usernameTimer = setTimeout(function () {
            checkAvailability(
                'username',
                usernameInput.value,
                usernameFeedback
            );
        }, 500);
    });

    emailInput.addEventListener('input', function () {
        clearTimeout(emailTimer);

        showFeedback(emailFeedback, '', '');

        emailTimer = setTimeout(function () {
            checkAvailability(
                'email',
                emailInput.value,
                emailFeedback
            );
        }, 500);
    });

    usernameInput.addEventListener('blur', function () {
        clearTimeout(usernameTimer);

        checkAvailability(
            'username',
            usernameInput.value,
            usernameFeedback
        );
    });

    emailInput.addEventListener('blur', function () {
        clearTimeout(emailTimer);

        checkAvailability(
            'email',
            emailInput.value,
            emailFeedback
        );
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        clientError.textContent = '';

        var fullName = document.getElementById('full_name').value.trim();
        var username = usernameInput.value.trim();
        var email = emailInput.value.trim();
        var password = document.getElementById('password').value;
        var confirmPassword = document.getElementById('confirm_password').value;

        var message = '';

        if (fullName === '') {
            message = 'Nama lengkap wajib diisi.';
        } else if (!usernamePattern.test(username)) {
            message = 'Username harus 4–30 karakter dan hanya berisi huruf, angka, atau garis bawah.';
        } else if (!emailPattern.test(email)) {
            message = 'Masukkan alamat email dengan format yang benar.';
        } else if (password.length < 8) {
            message = 'Password minimal 8 karakter.';
        } else if (password !== confirmPassword) {
            message = 'Konfirmasi password tidak sama.';
        }

        if (message !== '') {
            clientError.textContent = message;
            return;
        }

        clearTimeout(usernameTimer);
        clearTimeout(emailTimer);

        var usernameAvailable = await checkAvailability(
            'username',
            username,
            usernameFeedback
        );

        var emailAvailable = await checkAvailability(
            'email',
            email,
            emailFeedback
        );

        if (usernameAvailable === false || emailAvailable === false) {
            clientError.textContent = 'Username atau email sudah digunakan. Silakan periksa kembali.';
            return;
        }

        if (usernameAvailable === null || emailAvailable === null) {
            clientError.textContent = 'Pengecekan data gagal. Silakan coba lagi.';
            return;
        }

        form.submit();
    });
});

