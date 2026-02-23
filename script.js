// ============================================
// DRIVELUXIA - Creative Code
// ============================================

// Configuration
const CONFIG = {
    name: 'DriveLuxia',
    currency: 'FCFA',
    colors: {
        primary: '#1a2b3c',
        secondary: '#c6a13b'
    }
};

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    initApp();
    setupEventListeners();
    initAnimations();
});

function initApp() {
    console.log('🚗 DriveLuxia - Creative Code');
    
    // Menu mobile
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileBtn && mobileMenu) {
        mobileBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Date pickers
    initDatePickers();
    
    // Prix calculator
    initPriceCalculator();
}

function setupEventListeners() {
    // Smooth scroll pour ancres
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
    
    // Alertes auto-fermantes
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => alert.remove());
    }, 5000);
}

function initDatePickers() {
    const today = new Date().toISOString().split('T')[0];
    document.querySelectorAll('input[type="date"]').forEach(input => {
        input.min = today;
    });
}

function initPriceCalculator() {
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    const priceInput = document.getElementById('pricePerDay');
    const totalDisplay = document.getElementById('totalPrice');
    
    if (startDate && endDate && priceInput && totalDisplay) {
        function calculate() {
            if (startDate.value && endDate.value) {
                const start = new Date(startDate.value);
                const end = new Date(endDate.value);
                const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
                
                if (days > 0) {
                    const total = days * parseFloat(priceInput.value) * 655;
                    totalDisplay.textContent = total.toLocaleString('fr-FR') + ' FCFA';
                }
            }
        }
        
        startDate.addEventListener('change', calculate);
        endDate.addEventListener('change', calculate);
    }
}

function initAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fadeIn');
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.luxury-card, .feature-icon').forEach(el => {
        observer.observe(el);
    });
}

// Notifications
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 bg-white shadow-lg rounded-lg p-4 border-l-4 ${
        type === 'success' ? 'border-green-500' : 'border-red-500'
    } z-50 animate-slideIn`;
    
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-3 text-${type === 'success' ? 'green' : 'red'}-500"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Formatage
function formatPrice(price) {
    return price.toLocaleString('fr-FR') + ' FCFA';
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

// Validation de formulaire
function validateForm(form) {
    let isValid = true;
    
    form.querySelectorAll('[required]').forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('border-red-500');
            isValid = false;
        } else {
            input.classList.remove('border-red-500');
        }
    });
    
    return isValid;
}

// Exports globaux
window.DriveLuxia = {
    showNotification,
    formatPrice,
    formatDate
};