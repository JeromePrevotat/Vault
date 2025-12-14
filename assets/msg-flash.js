export function showFlash(message, type = 'info') {
    const container = document.getElementById('flash-messages');
    
    const alertClasses = {
        success: 'alert-success',
        error: 'alert-danger',
        warning: 'alert-warning',
        info: 'alert-info'
    };
    
    const alertClass = alertClasses[type] || 'alert-info';
    
    const alertDiv = document.createElement('div');

    alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" aria-label="Close"></button>
    `;
    
    while (container.children.length >= 3) {
        container.removeChild(container.firstChild);
    }
    container.appendChild(alertDiv);
    
    // Auto-dismiss après 5 secondes
    setTimeout(() => {
        alertDiv.classList.remove('show');
        setTimeout(() => alertDiv.remove(), 150);
    }, 5000);

    const closeBtn = alertDiv.querySelector('.btn-close');
    closeBtn.addEventListener('click', () => {
        alertDiv.classList.remove('show');
        setTimeout(() => alertDiv.remove(), 150);
    });
}

