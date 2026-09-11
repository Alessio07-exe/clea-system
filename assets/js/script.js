// CLEA System - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips if needed
    initializeTooltips();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize photo preview
    initializePhotoPreview();
});

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Check for required fields
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('error');
                    isValid = false;
                } else {
                    field.classList.remove('error');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Compila tutti i campi obbligatori');
            }
        });
    });
}

/**
 * Preview photos before upload
 */
function initializePhotoPreview() {
    const photoInputs = document.querySelectorAll('input[type="file"][name="photos[]"]');
    
    photoInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.files.length > 0) {
                console.log(this.files.length + ' foto selezionate');
                
                // Validate file sizes
                const maxSize = 5 * 1024 * 1024; // 5MB
                let totalSize = 0;
                
                Array.from(this.files).forEach(file => {
                    totalSize += file.size;
                    if (file.size > maxSize) {
                        alert('Il file ' + file.name + ' è troppo grande (max 5MB)');
                        this.value = '';
                        return;
                    }
                });
                
                if (totalSize > maxSize * 5) {
                    alert('Dimensione totale file troppo grande');
                    this.value = '';
                }
            }
        });
    });
}

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    // Add tooltip functionality if needed
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = this.getAttribute('data-tooltip');
            if (tooltip) {
                console.log('Tooltip: ' + tooltip);
            }
        });
    });
}

/**
 * Show confirmation dialog
 */
function confirmAction(message) {
    return confirm(message || 'Sei sicuro di voler continuare?');
}

/**
 * Format date for display
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('it-IT');
}

/**
 * Show loading indicator
 */
function showLoading(message = 'Caricamento...') {
    const loader = document.createElement('div');
    loader.className = 'loader';
    loader.innerHTML = '<p>' + message + '</p>';
    document.body.appendChild(loader);
}

/**
 * Hide loading indicator
 */
function hideLoading() {
    const loader = document.querySelector('.loader');
    if (loader) {
        loader.remove();
    }
}
