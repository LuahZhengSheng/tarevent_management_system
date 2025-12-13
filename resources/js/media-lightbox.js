// resources/js/media-lightbox.js

/**
 * Media Lightbox Helper
 * Displays images and videos in a modal lightbox
 */
class MediaLightbox {
    constructor() {
        this.currentIndex = 0;
        this.mediaItems = [];
        this.overlay = null;
        this.init();
    }

    init() {
        // Create lightbox overlay
        this.createOverlay();
        
        // Bind keyboard events
        document.addEventListener('keydown', (e) => {
            if (this.overlay && this.overlay.classList.contains('active')) {
                if (e.key === 'Escape') this.close();
                if (e.key === 'ArrowLeft') this.prev();
                if (e.key === 'ArrowRight') this.next();
            }
        });
    }

    createOverlay() {
        // Check if overlay already exists
        if (document.getElementById('mediaLightbox')) {
            this.overlay = document.getElementById('mediaLightbox');
            return;
        }

        this.overlay = document.createElement('div');
        this.overlay.id = 'mediaLightbox';
        this.overlay.className = 'lightbox-overlay';
        this.overlay.innerHTML = `
            <button class="lightbox-close" aria-label="Close">
                <i class="bi bi-x"></i>
            </button>
            <button class="lightbox-prev" aria-label="Previous">
                <i class="bi bi-chevron-left"></i>
            </button>
            <button class="lightbox-next" aria-label="Next">
                <i class="bi bi-chevron-right"></i>
            </button>
            <div class="lightbox-content">
                <div class="lightbox-loading"></div>
            </div>
            <div class="lightbox-counter">
                <span class="lightbox-current">1</span> / <span class="lightbox-total">1</span>
            </div>
        `;

        document.body.appendChild(this.overlay);

        // Bind click events
        this.overlay.querySelector('.lightbox-close').addEventListener('click', () => this.close());
        this.overlay.querySelector('.lightbox-prev').addEventListener('click', () => this.prev());
        this.overlay.querySelector('.lightbox-next').addEventListener('click', () => this.next());
        
        // Close on overlay background click
        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay) {
                this.close();
            }
        });
    }

    open(mediaItems, startIndex = 0) {
        this.mediaItems = mediaItems;
        this.currentIndex = startIndex;
        
        if (this.mediaItems.length === 0) return;

        // Show overlay
        this.overlay.classList.add('active');
        document.body.style.overflow = 'hidden';

        // Update counter
        this.updateCounter();

        // Display current media
        this.displayMedia();

        // Update navigation buttons
        this.updateNavigation();
    }

    displayMedia() {
        const content = this.overlay.querySelector('.lightbox-content');
        const loading = this.overlay.querySelector('.lightbox-loading');
        const item = this.mediaItems[this.currentIndex];

        if (!item) return;

        // Show loading
        loading.style.display = 'block';

        // Clear previous content (except loading)
        const mediaElements = content.querySelectorAll('img, video');
        mediaElements.forEach(el => el.remove());

        if (item.type === 'image') {
            const img = document.createElement('img');
            img.src = item.url;
            img.alt = item.alt || 'Media preview';
            
            img.onload = () => {
                loading.style.display = 'none';
            };
            
            img.onerror = () => {
                loading.style.display = 'none';
                console.error('Failed to load image:', item.url);
            };
            
            content.appendChild(img);
        } else if (item.type === 'video') {
            const video = document.createElement('video');
            video.src = item.url;
            video.controls = true;
            video.autoplay = true;
            
            video.onloadeddata = () => {
                loading.style.display = 'none';
            };
            
            video.onerror = () => {
                loading.style.display = 'none';
                console.error('Failed to load video:', item.url);
            };
            
            content.appendChild(video);
        }
    }

    updateCounter() {
        const current = this.overlay.querySelector('.lightbox-current');
        const total = this.overlay.querySelector('.lightbox-total');
        
        current.textContent = this.currentIndex + 1;
        total.textContent = this.mediaItems.length;
    }

    updateNavigation() {
        const prevBtn = this.overlay.querySelector('.lightbox-prev');
        const nextBtn = this.overlay.querySelector('.lightbox-next');

        // Always show buttons if there's more than one item
        if (this.mediaItems.length > 1) {
            prevBtn.style.display = 'flex';
            nextBtn.style.display = 'flex';
            
            // Disable prev button on first item
            prevBtn.disabled = this.currentIndex === 0;
            
            // Disable next button on last item
            nextBtn.disabled = this.currentIndex === this.mediaItems.length - 1;
        } else {
            // Hide both buttons if only one item
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
        }
    }

    prev() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
            this.displayMedia();
            this.updateCounter();
            this.updateNavigation();
        }
    }

    next() {
        if (this.currentIndex < this.mediaItems.length - 1) {
            this.currentIndex++;
            this.displayMedia();
            this.updateCounter();
            this.updateNavigation();
        }
    }

    close() {
        this.overlay.classList.remove('active');
        document.body.style.overflow = '';
        
        // Stop any playing videos
        const videos = this.overlay.querySelectorAll('video');
        videos.forEach(video => {
            video.pause();
            video.currentTime = 0;
        });
    }
}

// Initialize lightbox when DOM is ready
let lightbox;
document.addEventListener('DOMContentLoaded', () => {
    lightbox = new MediaLightbox();
});

// Global function to open lightbox (can be called from anywhere)
window.openLightbox = function(index) {
    if (!lightbox) {
        lightbox = new MediaLightbox();
    }
    
    // Get all media items from the grid
    const mediaGrid = document.getElementById('mediaGrid');
    if (!mediaGrid) return;
    
    const mediaElements = mediaGrid.querySelectorAll('.media-item');
    const mediaItems = Array.from(mediaElements).map(el => {
        const img = el.querySelector('img');
        const video = el.querySelector('video');
        
        if (img) {
            return {
                type: 'image',
                url: img.src,
                alt: img.alt
            };
        } else if (video) {
            // 从 source 标签或 video 标签获取 src
            const source = video.querySelector('source');
            const videoSrc = source ? source.src : video.src;
            
            return {
                type: 'video',
                url: videoSrc
            };

        }
    }).filter(item => item !== undefined);
    
    lightbox.open(mediaItems, index);
};
