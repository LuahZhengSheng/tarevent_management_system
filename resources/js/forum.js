/**
 * Forum JavaScript - TAREvent
 */

document.addEventListener('DOMContentLoaded', function() {
    initForumFeatures();
});

function initForumFeatures() {
    // Search input debounce
    initSearchDebounce();
    
    // Smooth scroll to top
    initScrollToTop();
    
    // Post card hover effects
    initPostCardEffects();
    
    // Filter animations
    initFilterAnimations();
}

/**
 * Search Input Debounce
 */
function initSearchDebounce() {
    const searchInput = document.querySelector('.search-input');
    if (!searchInput) return;

    let debounceTimer;
    
    searchInput.addEventListener('input', function(e) {
        clearTimeout(debounceTimer);
        
        const searchIcon = document.querySelector('.search-icon');
        searchIcon.classList.add('searching');
        
        debounceTimer = setTimeout(() => {
            searchIcon.classList.remove('searching');
            // Auto-submit after 500ms of no typing
            // Uncomment if you want auto-submit:
            // e.target.form.submit();
        }, 500);
    });
}

/**
 * Post Card Effects
 */
function initPostCardEffects() {
    const postCards = document.querySelectorAll('.post-card');
    
    postCards.forEach(card => {
        // Prevent card click when clicking on links inside
        card.addEventListener('click', function(e) {
            // If clicking on a link or button, don't navigate
            if (e.target.closest('a') || e.target.closest('button')) {
                e.stopPropagation();
                return;
            }
        });

        // Add ripple effect on click
        card.addEventListener('mousedown', function(e) {
            const ripple = document.createElement('div');
            ripple.className = 'card-ripple';
            
            const rect = card.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            
            card.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
}

/**
 * Filter Animations
 */
function initFilterAnimations() {
    const filterSelects = document.querySelectorAll('.filter-select');
    
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Add loading animation
            const form = this.closest('form');
            if (form) {
                form.classList.add('loading');
            }
        });
    });
}

/**
 * Scroll to Top Button
 */
function initScrollToTop() {
    const scrollBtn = document.createElement('button');
    scrollBtn.className = 'scroll-to-top';
    scrollBtn.innerHTML = '<i class="bi bi-arrow-up"></i>';
    scrollBtn.setAttribute('aria-label', 'Scroll to top');
    document.body.appendChild(scrollBtn);

    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollBtn.classList.add('visible');
        } else {
            scrollBtn.classList.remove('visible');
        }
    });

    scrollBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

/**
 * Add CSS for scroll-to-top button and ripple effect
 */
const style = document.createElement('style');
style.textContent = `
    .scroll-to-top {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-hover));
        color: white;
        border: none;
        box-shadow: var(--shadow-xl);
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .scroll-to-top.visible {
        opacity: 1;
        visibility: visible;
    }

    .scroll-to-top:hover {
        transform: translateY(-4px) scale(1.1);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }

    .scroll-to-top:active {
        transform: translateY(-2px) scale(1.05);
    }

    .card-ripple {
        position: absolute;
        border-radius: 50%;
        background: var(--primary);
        opacity: 0.3;
        pointer-events: none;
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
    }

    @keyframes ripple-animation {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }

    .search-icon.searching {
        animation: search-pulse 1s ease-in-out infinite;
    }

    @keyframes search-pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }

    .filter-form.loading {
        opacity: 0.6;
        pointer-events: none;
    }

    @media (max-width: 768px) {
        .scroll-to-top {
            bottom: 1rem;
            right: 1rem;
            width: 2.5rem;
            height: 2.5rem;
            font-size: 1rem;
        }
    }
`;
document.head.appendChild(style);
