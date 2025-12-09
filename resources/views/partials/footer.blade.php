<footer class="footer mt-auto">
    <div class="footer-main">
        <div class="container">
            <div class="row g-4">
                <!-- About Section -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-section">
                        <div class="footer-brand mb-3">
                            <div class="brand-logo">
                                <i class="bi bi-calendar-event-fill"></i>
                            </div>
                            <span class="brand-text ms-2">
                                <strong>TAR</strong>Event
                            </span>
                        </div>
                        <p class="footer-description">
                            Your one-stop platform for discovering and participating in exciting campus events, 
                            connecting with clubs, and engaging with the TAR community.
                        </p>
                        <div class="social-links">
                            <a href="#" class="social-link" title="Facebook">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="#" class="social-link" title="Instagram">
                                <i class="bi bi-instagram"></i>
                            </a>
                            <a href="#" class="social-link" title="Twitter">
                                <i class="bi bi-twitter"></i>
                            </a>
                            <a href="#" class="social-link" title="LinkedIn">
                                <i class="bi bi-linkedin"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <div class="footer-section">
                        <h5 class="footer-title">Quick Links</h5>
                        <ul class="footer-links">
                            <li><a href="{{ route('home') }}">Home</a></li>
                            <li><a href="{{ route('events.index') }}">Events</a></li>
                            <li><a href="{{ route('home') }}">Clubs</a></li>
                            <li><a href="{{ route('home') }}">Forum</a></li>
                            @auth
                            <li><a href="{{ route('events.my') }}">My Events</a></li>
                            @else
                            <li><a href="{{ route('home') }}">Login</a></li>
                            @endauth
                        </ul>
                    </div>
                </div>

                <!-- Categories -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-section">
                        <h5 class="footer-title">Event Categories</h5>
                        <ul class="footer-links">
                            <li><a href="{{ route('events.index', ['category' => 'Academic']) }}">Academic</a></li>
                            <li><a href="{{ route('events.index', ['category' => 'Sports']) }}">Sports</a></li>
                            <li><a href="{{ route('events.index', ['category' => 'Cultural']) }}">Cultural</a></li>
                            <li><a href="{{ route('events.index', ['category' => 'Workshop']) }}">Workshops</a></li>
                            <li><a href="{{ route('events.index', ['category' => 'Social']) }}">Social Events</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-section">
                        <h5 class="footer-title">Contact Us</h5>
                        <ul class="footer-contact">
                            <li>
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Tunku Abdul Rahman University College<br>Kuala Lumpur, Malaysia</span>
                            </li>
                            <li>
                                <i class="bi bi-envelope-fill"></i>
                                <span><a href="mailto:events@tarc.edu.my">events@tarc.edu.my</a></span>
                            </li>
                            <li>
                                <i class="bi bi-phone-fill"></i>
                                <span><a href="tel:+60312345678">+60 3-1234 5678</a></span>
                            </li>
                            <li>
                                <i class="bi bi-clock-fill"></i>
                                <span>Mon - Fri: 9:00 AM - 5:00 PM</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">
                        &copy; {{ date('Y') }} TAREvent Management System. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <ul class="footer-bottom-links">
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Cookie Policy</a></li>
                        <li><a href="#">Help Center</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTop" title="Scroll to top">
        <i class="bi bi-arrow-up"></i>
    </button>
</footer>

<style>
.footer {
    background-color: var(--bg-primary);
    border-top: 1px solid var(--border-color);
    margin-top: 4rem;
}

.footer-main {
    padding: 4rem 0 2rem;
}

.footer-section {
    margin-bottom: 2rem;
}

.footer-brand {
    display: flex;
    align-items: center;
}

.footer-brand .brand-logo {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.footer-brand .brand-text {
    font-size: 1.25rem;
    color: var(--text-primary);
    font-weight: 600;
}

.footer-description {
    color: var(--text-secondary);
    line-height: 1.7;
    margin-bottom: 1.5rem;
}

.social-links {
    display: flex;
    gap: 0.75rem;
}

.social-link {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: var(--bg-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-primary);
    font-size: 1.25rem;
    transition: all 0.3s ease;
}

.social-link:hover {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    transform: translateY(-3px);
}

.footer-title {
    color: var(--text-primary);
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 1.25rem;
    position: relative;
    padding-bottom: 0.75rem;
}

.footer-title::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 40px;
    height: 3px;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    border-radius: 2px;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 0.75rem;
}

.footer-links a {
    color: var(--text-secondary);
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;
}

.footer-links a:hover {
    color: var(--primary);
    transform: translateX(5px);
}

.footer-contact {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-contact li {
    display: flex;
    align-items: start;
    gap: 0.75rem;
    margin-bottom: 1rem;
    color: var(--text-secondary);
}

.footer-contact i {
    color: var(--primary);
    font-size: 1.1rem;
    margin-top: 0.125rem;
}

.footer-contact a {
    color: var(--text-secondary);
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-contact a:hover {
    color: var(--primary);
}

.footer-bottom {
    background-color: var(--bg-secondary);
    padding: 1.5rem 0;
    border-top: 1px solid var(--border-color);
}

.footer-bottom p {
    color: var(--text-tertiary);
    font-size: 0.9rem;
}

.footer-bottom-links {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    flex-wrap: wrap;
}

@media (min-width: 768px) {
    .footer-bottom-links {
        justify-content: flex-end;
    }
}

.footer-bottom-links a {
    color: var(--text-tertiary);
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.3s ease;
}

.footer-bottom-links a:hover {
    color: var(--primary);
}

.scroll-to-top {
    position: fixed;
    bottom: 6rem;
    right: 2rem;
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    background: var(--primary);
    color: white;
    border: none;
    box-shadow: var(--shadow-lg);
    cursor: pointer;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 999;
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
    transform: translateY(-5px);
    box-shadow: var(--shadow-xl);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .footer-main {
        padding: 3rem 0 1.5rem;
    }

    .footer-section {
        text-align: center;
    }

    .footer-brand {
        justify-content: center;
    }

    .footer-title::after {
        left: 50%;
        transform: translateX(-50%);
    }

    .social-links {
        justify-content: center;
    }

    .footer-contact li {
        justify-content: center;
    }

    .footer-bottom-links {
        margin-top: 1rem;
    }

    .scroll-to-top {
        bottom: 5rem;
        right: 1rem;
        width: 2.5rem;
        height: 2.5rem;
    }
}

/* Dark Mode */
[data-theme="dark"] .footer-bottom {
    background-color: var(--bg-primary);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scrollToTopBtn = document.getElementById('scrollToTop');

    // Show/hide scroll to top button
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.classList.add('visible');
        } else {
            scrollToTopBtn.classList.remove('visible');
        }
    });

    // Scroll to top on click
    scrollToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
</script>
