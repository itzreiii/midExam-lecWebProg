// Form Validation
function validateEventForm() {
    const title = document.getElementById('event-title');
    const date = document.getElementById('event-date');
    const time = document.getElementById('event-time');
    const capacity = document.getElementById('event-capacity');
    
    let isValid = true;
    
    // Clear previous error messages
    clearErrors();
    
    // Title validation
    if (title.value.trim().length < 3) {
        showError(title, 'Title must be at least 3 characters long');
        isValid = false;
    }
    
    // Date validation
    const selectedDate = new Date(date.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        showError(date, 'Date cannot be in the past');
        isValid = false;
    }
    
    // Capacity validation
    if (capacity.value < 1) {
        showError(capacity, 'Capacity must be at least 1');
        isValid = false;
    }
    
    return isValid;
}

// Show error message
function showError(element, message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    element.parentNode.appendChild(errorDiv);
    element.classList.add('error-input');
}

// Clear all error messages
function clearErrors() {
    document.querySelectorAll('.error-message').forEach(error => error.remove());
    document.querySelectorAll('.error-input').forEach(input => input.classList.remove('error-input'));
}

// Dynamic registration spots update
function updateAvailableSpots(eventId) {
    fetch(`/api/event-spots.php?event_id=${eventId}`)
        .then(response => response.json())
        .then(data => {
            const spotsElement = document.querySelector(`#spots-${eventId}`);
            if (spotsElement) {
                spotsElement.textContent = `${data.available} / ${data.capacity}`;
                
                const registerButton = document.querySelector(`#register-${eventId}`);
                if (registerButton) {
                    if (data.available === 0) {
                        registerButton.disabled = true;
                        registerButton.textContent = 'Full';
                    }
                }
            }
        })
        .catch(error => console.error('Error updating spots:', error));
}

// Initialize datepickers and timepickers if using any third-party libraries
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for form submissions
    const eventForm = document.getElementById('event-form');
    if (eventForm) {
        eventForm.addEventListener('submit', function(e) {
            if (!validateEventForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Add event listeners for registration buttons
    document.querySelectorAll('.register-button').forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to register for this event?')) {
                e.preventDefault();
            }
        });
    });
});

// Handle file uploads for event images
function handleImageUpload(input) {
    const file = input.files[0];
    const maxSize = 5 * 1024 * 1024; // 5MB
    
    if (file.size > maxSize) {
        alert('File size must be less than 5MB');
        input.value = '';
        return;
    }
    
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!allowedTypes.includes(file.type)) {
        alert('Only JPG, PNG and GIF files are allowed');
        input.value = '';
        return;
    }
    
    // Show preview
    const preview = document.getElementById('image-preview');
    if (preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}