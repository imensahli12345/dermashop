// Form validation for login and registration
document.addEventListener('DOMContentLoaded', function() {
    // Get forms
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    // Login form validation
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = loginForm.querySelector('input[name="email"]').value;
            const password = loginForm.querySelector('input[name="password"]').value;
            
            let isValid = true;
            let errorMessage = '';

            // Email validation
            if (!email) {
                isValid = false;
                errorMessage = 'Email is required';
            } else if (!isValidEmail(email)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }

            // Password validation
            if (!password) {
                isValid = false;
                errorMessage = 'Password is required';
            } else if (password.length < 6) {
                isValid = false;
                errorMessage = 'Password must be at least 6 characters long';
            }

            if (isValid) {
                loginForm.submit();
            } else {
                showError(errorMessage);
            }
        });
    }

    // Registration form validation
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = registerForm.querySelector('input[name="username"]').value;
            const email = registerForm.querySelector('input[name="email"]').value;
            const password = registerForm.querySelector('input[name="password"]').value;
            
            let isValid = true;
            let errorMessage = '';

            // Username validation
            if (!username) {
                isValid = false;
                errorMessage = 'Username is required';
            } else if (username.length < 3) {
                isValid = false;
                errorMessage = 'Username must be at least 3 characters long';
            }

            // Email validation
            if (!email) {
                isValid = false;
                errorMessage = 'Email is required';
            } else if (!isValidEmail(email)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }

            // Password validation
            if (!password) {
                isValid = false;
                errorMessage = 'Password is required';
            } else if (password.length < 8) {
                isValid = false;
                errorMessage = 'Password must be at least 8 characters long';
            } else if (!hasUpperCase(password) || !hasLowerCase(password) || !hasNumber(password)) {
                isValid = false;
                errorMessage = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
            }

            if (isValid) {
                registerForm.submit();
            } else {
                showError(errorMessage);
            }
        });
    }

    // Helper functions
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function hasUpperCase(str) {
        return /[A-Z]/.test(str);
    }

    function hasLowerCase(str) {
        return /[a-z]/.test(str);
    }

    function hasNumber(str) {
        return /[0-9]/.test(str);
    }

    function showError(message) {
        // Remove any existing error messages
        const existingError = document.querySelector('.form-error');
        if (existingError) {
            existingError.remove();
        }

        // Create error message element
        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error';
        errorDiv.style.color = '#e51414';
        errorDiv.style.marginBottom = '10px';
        errorDiv.style.fontSize = '14px';
        errorDiv.textContent = message;

        // Insert error message before the form
        const form = document.querySelector('form');
        form.parentNode.insertBefore(errorDiv, form);
    }
}); 