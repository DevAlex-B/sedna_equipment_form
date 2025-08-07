document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('equipment-form');
    const downtimeCheckbox = document.getElementById('downtime');
    const downtimeFields = document.getElementById('downtime-fields');
    const operatorInput = document.getElementById('operator');
    const operatorError = document.getElementById('operator-error');
    const toast = document.getElementById('toast');

    // Daily status toggles
    document.querySelectorAll('.day').forEach(day => {
        const hidden = day.querySelector('input[type="hidden"]');
        day.querySelectorAll('.pill').forEach(btn => {
            btn.addEventListener('click', () => {
                day.querySelectorAll('.pill').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                hidden.value = btn.dataset.value;
            });
        });
    });

    // Downtime toggle logic
    downtimeCheckbox.addEventListener('change', () => {
        if (downtimeCheckbox.checked) {
            downtimeFields.classList.remove('hidden');
        } else {
            downtimeFields.classList.add('hidden');
            downtimeFields.querySelectorAll('input').forEach(i => i.value = '');
        }
    });

    // Real-time validation for operator
    operatorInput.addEventListener('input', () => {
        if (operatorInput.value.trim() === '') {
            operatorError.textContent = 'Operator is required';
        } else {
            operatorError.textContent = '';
        }
    });

    // Form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (operatorInput.value.trim() === '') {
            operatorError.textContent = 'Operator is required';
            operatorInput.focus();
            return;
        }

        const formData = new FormData(form);
        try {
            const response = await fetch('submit.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            showToast(data.message);
            if (data.success) {
                form.reset();
                document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
                downtimeFields.classList.add('hidden');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        } catch (err) {
            showToast('Submission failed');
        }
    });

    function showToast(msg) {
        toast.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }
});
