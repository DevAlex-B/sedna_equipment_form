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

    const defaultBtn = day.querySelector('.status-btn.online');
    if (defaultBtn) {
        defaultBtn.classList.add('active');
        hiddenInput.value = defaultBtn.dataset.value;
    }
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

document.querySelectorAll('select[required]').forEach(sel => {
    sel.addEventListener('change', () => sel.classList.remove('error'));
});

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    feedback.textContent = '';
    feedback.className = 'form-feedback';
    submitBtn.classList.add('loading');

    const requiredFields = ['operator', 'equipment', 'status'];
    for (let field of requiredFields) {
        const el = document.getElementById(field);
        if (!el.value) {
            el.classList.add('error');
            feedback.textContent = `Please select a ${field}.`;
            feedback.classList.add('error');
            submitBtn.classList.remove('loading');
            return;
        }
    }

    const formData = new FormData(form);

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            feedback.textContent = 'Thanks! Your status has been submitted.';
            feedback.classList.add('success');
            form.reset();
            document.querySelectorAll('.status-btn.active').forEach(b => b.classList.remove('active'));
            daySections.forEach(day => {
                const defaultBtn = day.querySelector('.status-btn.online');
                const hiddenInput = day.querySelector('input[type="hidden"]');
                if (defaultBtn && hiddenInput) {
                    defaultBtn.classList.add('active');
                    hiddenInput.value = defaultBtn.dataset.value;
                }
            });
            downtimeDetails.classList.add('hidden');
        } else {
            feedback.textContent = data.message || 'Submission failed.';
            feedback.classList.add('error');
        }
    } catch (err) {
        feedback.textContent = 'Network error.';
        feedback.classList.add('error');
    } finally {
        submitBtn.classList.remove('loading');
    }
});
