// Login Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const loginForms = document.querySelectorAll('.login-form');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons and forms
            tabButtons.forEach(btn => btn.classList.remove('active'));
            loginForms.forEach(form => form.classList.remove('active'));
            
            // Add active class to clicked button
            button.classList.add('active');
            
            // Show corresponding form
            const tabId = button.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });

    // Form validation
    const loginForm = document.getElementById('masuk');
    const registerForm = document.getElementById('daftar');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi.');
                return false;
            }
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const name = document.getElementById('reg-name').value;
            const email = document.getElementById('reg-email').value;
            const password = document.getElementById('reg-password').value;
            const confirmPassword = document.getElementById('reg-password-confirm').value;
            
            if (!name || !email || !password || !confirmPassword) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi.');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok.');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password harus minimal 8 karakter.');
                return false;
            }
        });
    }

    // Add CSRF token to all forms
    const forms = document.querySelectorAll('form');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    forms.forEach(form => {
        const existingToken = form.querySelector('input[name="_token"]');
        if (!existingToken && csrfToken) {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = '_token';
            hiddenInput.value = csrfToken;
            form.appendChild(hiddenInput);
        }
    });
});
