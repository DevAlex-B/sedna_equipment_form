// Daily status toggles
const daySections = document.querySelectorAll('.day');
daySections.forEach(day => {
    const buttons = day.querySelectorAll('.status-btn');
    const hiddenInput = day.querySelector('input[type="hidden"]');
    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            buttons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            hiddenInput.value = btn.dataset.value;
        });
    });
});

// Downtime toggle
const downtimeCheckbox = document.getElementById('downtime');
const downtimeDetails = document.getElementById('downtime-details');
if (downtimeCheckbox) {
    downtimeCheckbox.addEventListener('change', () => {
        if (downtimeCheckbox.checked) {
            downtimeDetails.classList.remove('hidden');
        } else {
            downtimeDetails.classList.add('hidden');
            downtimeDetails.querySelectorAll('input[type="time"]').forEach(inp => inp.value = '');
        }
    });
}

// Form submission
const form = document.getElementById('equipment-form');
const submitBtn = document.getElementById('submit-btn');
const feedback = document.getElementById('form-feedback');

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    feedback.textContent = '';
    submitBtn.classList.add('loading');

    const formData = new FormData(form);

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            feedback.textContent = 'Form submitted successfully.';
            form.reset();
            document.querySelectorAll('.status-btn.active').forEach(b => b.classList.remove('active'));
            downtimeDetails.classList.add('hidden');
        } else {
            feedback.textContent = data.message || 'Submission failed.';
        }
    } catch (err) {
        feedback.textContent = 'Network error.';
    } finally {
        submitBtn.classList.remove('loading');
    }
});
