document.addEventListener('DOMContentLoaded', () => {
    const identifier = document.getElementById('identifier');
    const identifierHint = document.getElementById('identifierHint');
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('loginBtn');

    identifier.addEventListener('input', () => {
        const value = identifier.value.trim();
        const isAllDigits = /^\d+$/.test(value);

        if (value === '') {
            identifierHint.textContent = '';
            identifierHint.className = 'field-hint';
        } else if (isAllDigits && value.length < 12) {
            identifierHint.textContent = `${value.length}/12 digits`;
            identifierHint.className = 'field-hint';
        } else if (isAllDigits && value.length === 12) {
            identifierHint.textContent = 'Valid index number';
            identifierHint.className = 'field-hint match';
        } else {
            identifierHint.textContent = '';
            identifierHint.className = 'field-hint';
        }
    });

    form.addEventListener('submit', () => {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span>Logging in...';
    });
});