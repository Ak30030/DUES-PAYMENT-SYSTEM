document.addEventListener('DOMContentLoaded', () => {
    const indexNumber = document.getElementById('index_number');
    const indexHint = document.getElementById('indexHint');
    const password = document.getElementById('password');
    const confirm = document.getElementById('confirm_password');
    const strengthFill = document.getElementById('strengthFill');
    const matchHint = document.getElementById('matchHint');
    const form = document.getElementById('registerForm');
    const submitBtn = document.getElementById('registerBtn');

    indexNumber.addEventListener('input', () => {
        indexNumber.value = indexNumber.value.replace(/\D/g, '');

        const len = indexNumber.value.length;
        if (len === 0) {
            indexHint.textContent = '';
            indexHint.className = 'field-hint';
        } else if (len < 12) {
            indexHint.textContent = `${len}/12 digits`;
            indexHint.className = 'field-hint';
        } else {
            indexHint.textContent = 'Valid index number';
            indexHint.className = 'field-hint match';
        }
    });

    function checkStrength(value) {
        let score = 0;
        if (value.length >= 8) score++;
        if (/[A-Z]/.test(value)) score++;
        if (/[0-9]/.test(value)) score++;
        if (/[^A-Za-z0-9]/.test(value)) score++;

        const colors = ['#b71c1c', '#e65100', '#f9a825', '#2e7d32'];
        const widths = ['25%', '50%', '75%', '100%'];

        if (value.length === 0) {
            strengthFill.style.width = '0%';
            return;
        }
        const idx = Math.max(score - 1, 0);
        strengthFill.style.width = widths[idx];
        strengthFill.style.background = colors[idx];
    }

    function checkMatch() {
        if (confirm.value === '') {
            matchHint.textContent = '';
            matchHint.className = 'field-hint';
            return;
        }
        if (password.value === confirm.value) {
            matchHint.textContent = 'Passwords match';
            matchHint.className = 'field-hint match';
        } else {
            matchHint.textContent = 'Passwords do not match';
            matchHint.className = 'field-hint mismatch';
        }
    }

    password.addEventListener('input', () => {
        checkStrength(password.value);
        checkMatch();
    });
    confirm.addEventListener('input', checkMatch);

    form.addEventListener('submit', (e) => {
        if (indexNumber.value.length !== 12) {
            e.preventDefault();
            indexHint.textContent = 'Index number must be exactly 12 digits';
            indexHint.className = 'field-hint mismatch';
            return;
        }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span>Creating account...';
    });
});