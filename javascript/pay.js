document.addEventListener('DOMContentLoaded', () => {
    const payBtn = document.getElementById('payBtn');
    const payError = document.getElementById('payError');

    payBtn.addEventListener('click', async () => {
        payError.style.display = 'none';
        payBtn.disabled = true;
        payBtn.innerHTML = '<span class="spinner"></span>Processing...';

        try {
            const res = await fetch(`initialize_payment.php?due_id=${payBtn.dataset.dueId}`);
            const data = await res.json();

            if (data.status && data.authorization_url) {
                window.location.href = data.authorization_url;
            } else {
                throw new Error(data.message || 'Something went wrong.');
            }
        } catch (err) {
            payError.textContent = err.message;
            payError.style.display = 'block';
            payBtn.disabled = false;
            payBtn.innerHTML = 'Pay Now';
        }
    });
});