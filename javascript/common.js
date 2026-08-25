function togglePassword(id, el) {
    const field = document.getElementById(id);
    if (field.type === 'password') {
        field.type = 'text';
        el.style.opacity = '0.5';
    } else {
        field.type = 'password';
        el.style.opacity = '1';
    }
}