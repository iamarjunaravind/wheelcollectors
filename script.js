/**
 * THE VAULT - Main Script
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // Mobile Menu Toggle
    const humbergerOpen = document.querySelector('.humberger-open');
    const humbergerWrapper = document.querySelector('.humberger-menu-wrapper');
    const humbergerOverlay = document.querySelector('.humberger-menu-overlay');

    if (humbergerOpen) {
        humbergerOpen.addEventListener('click', () => {
            humbergerWrapper.classList.add('active');
            humbergerOverlay.classList.add('active');
            document.body.style.overflow = 'hidden'; 
        });
    }

    if (humbergerOverlay) {
        humbergerOverlay.addEventListener('click', () => {
            humbergerWrapper.classList.remove('active');
            humbergerOverlay.classList.remove('active');
            document.body.style.overflow = 'auto'; 
        });
    }

    // Hero Sidebar Toggle (Mobile)
    const sidebarTitle = document.querySelector('.hero-sidebar-title');
    const sidebarList = document.querySelector('.hero-sidebar ul');

    if (sidebarTitle && sidebarList) {
        sidebarTitle.addEventListener('click', () => {
            if (window.innerWidth <= 991) {
                sidebarTitle.classList.toggle('active');
                sidebarList.classList.toggle('active');
            }
        });
    }

    // Global Transition Fix
    let resizeTimer;
    window.addEventListener("resize", () => {
        document.body.classList.add("resize-animation-stopper");
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            document.body.classList.remove("resize-animation-stopper");
        }, 400);
    });

    // Hero Slider Logic
    const slides = document.querySelectorAll('.hero-slide');
    const dotsContainer = document.querySelector('.hero-slider-dots');
    let currentSlide = 0;
    let slideInterval;

    if (slides.length > 0 && dotsContainer) {
        // Create Dots
        slides.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.classList.add('dot');
            if (index === 0) dot.classList.add('active');
            dot.addEventListener('click', () => {
                goToSlide(index);
                resetInterval();
            });
            dotsContainer.appendChild(dot);
        });

        const dots = document.querySelectorAll('.hero-slider-dots .dot');

        function goToSlide(n) {
            slides[currentSlide].classList.remove('active');
            dots[currentSlide].classList.remove('active');
            currentSlide = (n + slides.length) % slides.length;
            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add('active');
        }

        function nextSlide() {
            goToSlide(currentSlide + 1);
        }

        function resetInterval() {
            clearInterval(slideInterval);
            slideInterval = setInterval(nextSlide, 5000);
        }

        slideInterval = setInterval(nextSlide, 5000);
    }

    // Premium Slider Logic
    const pSlides = document.querySelectorAll('.premium-slide');
    const pDotsContainer = document.querySelector('.premium-slider-dots');
    let pCurrentSlide = 0;
    let pSlideInterval;

    if (pSlides.length > 0 && pDotsContainer) {
        // Create Dots
        pSlides.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.classList.add('dot');
            if (index === 0) dot.classList.add('active');
            dot.addEventListener('click', () => {
                pGoToSlide(index);
                pResetInterval();
            });
            pDotsContainer.appendChild(dot);
        });

        const pDots = document.querySelectorAll('.premium-slider-dots .dot');

        function pGoToSlide(n) {
            pSlides[pCurrentSlide].classList.remove('active');
            pDots[pCurrentSlide].classList.remove('active');
            pCurrentSlide = (n + pSlides.length) % pSlides.length;
            pSlides[pCurrentSlide].classList.add('active');
            pDots[pCurrentSlide].classList.add('active');
        }

        function pNextSlide() {
            pGoToSlide(pCurrentSlide + 1);
        }

        function pResetInterval() {
            clearInterval(pSlideInterval);
            pSlideInterval = setInterval(pNextSlide, 6000); // Slightly slower for premium feel
        }

        pSlideInterval = setInterval(pNextSlide, 6000);
    }
});

