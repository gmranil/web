// ============================================
// L2 SAVIOR - MAIN JAVASCRIPT
// ============================================

// Loading Overlay
function showLoading() {
    let overlay = document.querySelector('.loading-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<div class="spinner"></div>';
        document.body.appendChild(overlay);
    }
    setTimeout(() => overlay.classList.add('active'), 10);
}

function hideLoading() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.classList.remove('active');
        setTimeout(() => overlay.remove(), 300);
    }
}

// Toast Notifications
const Toast = {
    container: null,
    
    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    },
    
    show(message, type = 'info', duration = 5000) {
        this.init();
        
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${icons[type] || icons.info}</span>
            <span class="toast-message">${message}</span>
            <button class="toast-close" onclick="Toast.remove(this.parentElement)">×</button>
        `;
        
        this.container.appendChild(toast);
        
        // Auto remove
        setTimeout(() => {
            this.remove(toast);
        }, duration);
    },
    
    remove(toast) {
        if (!toast || !toast.parentElement) return;
        toast.classList.add('removing');
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 300);
    },
    
    success(message, duration) {
        this.show(message, 'success', duration);
    },
    
    error(message, duration) {
        this.show(message, 'error', duration);
    },
    
    warning(message, duration) {
        this.show(message, 'warning', duration);
    },
    
    info(message, duration) {
        this.show(message, 'info', duration);
    }
};

// Modal Dialog
const Modal = {
    overlay: null,
    
    open(title, content, footer = '') {
        // Create overlay if not exists
        if (!this.overlay) {
            this.overlay = document.createElement('div');
            this.overlay.className = 'modal-overlay';
            this.overlay.addEventListener('click', (e) => {
                if (e.target === this.overlay) {
                    this.close();
                }
            });
            document.body.appendChild(this.overlay);
        }
        
        // Set content
        this.overlay.innerHTML = `
            <div class="modal">
                <div class="modal-header">
                    <h2>${title}</h2>
                    <button class="modal-close" onclick="Modal.close()">×</button>
                </div>
                <div class="modal-body">
                    ${content}
                </div>
                ${footer ? `<div class="modal-footer">${footer}</div>` : ''}
            </div>
        `;
        
        // Show modal
        setTimeout(() => this.overlay.classList.add('active'), 10);
        document.body.style.overflow = 'hidden';
    },
    
    close() {
        if (this.overlay) {
            this.overlay.classList.remove('active');
            document.body.style.overflow = '';
            setTimeout(() => {
                if (this.overlay) {
                    this.overlay.remove();
                    this.overlay = null;
                }
            }, 300);
        }
    }
};

// Form Validation
function validateForm(formElement) {
    let isValid = true;
    const inputs = formElement.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        // Remove previous error
        const existingError = input.parentElement.querySelector('.error-message');
        if (existingError) existingError.remove();
        input.classList.remove('error');
        
        // Validate
        if (!input.value.trim()) {
            showFieldError(input, 'Ez a mező kötelező');
            isValid = false;
        } else if (input.type === 'email' && !isValidEmail(input.value)) {
            showFieldError(input, 'Érvénytelen email cím');
            isValid = false;
        } else if (input.type === 'password' && input.value.length < 4) {
            showFieldError(input, 'A jelszó legalább 4 karakter legyen');
            isValid = false;
        }
    });
    
    return isValid;
}

function showFieldError(input, message) {
    input.classList.add('error');
    const error = document.createElement('div');
    error.className = 'error-message';
    error.style.color = '#fca5a5';
    error.style.fontSize = '0.85rem';
    error.style.marginTop = '0.3rem';
    error.textContent = message;
    input.parentElement.appendChild(error);
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Real-time Form Validation
function initRealTimeValidation() {
    document.querySelectorAll('input[required], textarea[required], select[required]').forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim()) {
                const existingError = this.parentElement.querySelector('.error-message');
                if (existingError) existingError.remove();
                this.classList.remove('error');
            }
        });
    });
}

// Search Functionality
function initSearch(inputId, targetSelector, searchKeys) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    const items = document.querySelectorAll(targetSelector);
    
    input.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        
        items.forEach(item => {
            let found = false;
            
            searchKeys.forEach(key => {
                const element = item.querySelector(key);
                if (element && element.textContent.toLowerCase().includes(query)) {
                    found = true;
                }
            });
            
            if (found || !query) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
}

// Pagination
class Pagination {
    constructor(items, itemsPerPage = 10) {
        this.items = Array.from(items);
        this.itemsPerPage = itemsPerPage;
        this.currentPage = 1;
        this.totalPages = Math.ceil(this.items.length / this.itemsPerPage);
    }
    
    render() {
        // Hide all items
        this.items.forEach(item => item.style.display = 'none');
        
        // Show current page items
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        this.items.slice(start, end).forEach(item => item.style.display = '');
    }
    
    createControls(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        container.innerHTML = `
            <div style="display: flex; justify-content: center; align-items: center; gap: 1rem; margin: 2rem 0;">
                <button onclick="pagination.prev()" ${this.currentPage === 1 ? 'disabled' : ''} 
                        class="btn btn-secondary" style="padding: 0.5rem 1rem;">
                    ← Előző
                </button>
                <span style="color: #b8b8c8;">
                    ${this.currentPage} / ${this.totalPages}
                </span>
                <button onclick="pagination.next()" ${this.currentPage === this.totalPages ? 'disabled' : ''} 
                        class="btn btn-secondary" style="padding: 0.5rem 1rem;">
                    Következő →
                </button>
            </div>
        `;
    }
    
    next() {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
            this.render();
            this.createControls('pagination-controls');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
    
    prev() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.render();
            this.createControls('pagination-controls');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
}

// Smooth Scroll to Top
function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Confirm with custom modal
function confirmAction(message, onConfirm) {
    Modal.open(
        '⚠️ Megerősítés',
        `<p style="font-size: 1.1rem;">${message}</p>`,
        `
            <button class="btn btn-secondary" onclick="Modal.close()">Mégse</button>
            <button class="btn btn-primary" onclick="Modal.close(); (${onConfirm})()">Megerősítés</button>
        `
    );
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add fade-in to main content
    const mainContent = document.querySelector('.container, .admin-container');
    if (mainContent) {
        mainContent.classList.add('fade-in');
    }
    
    // Init real-time validation
    initRealTimeValidation();
    
    // Show PHP success/error messages as toasts
    const successMsg = document.querySelector('.alert-success');
    if (successMsg) {
        Toast.success(successMsg.textContent.trim());
        successMsg.remove();
    }
    
    const errorMsg = document.querySelector('.alert-error');
    if (errorMsg) {
        Toast.error(errorMsg.textContent.trim());
        errorMsg.remove();
    }
});

// Form submit with loading
document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form.tagName === 'FORM' && !form.classList.contains('no-loading')) {
        if (validateForm(form)) {
            showLoading();
        } else {
            e.preventDefault();
        }
    }
});
