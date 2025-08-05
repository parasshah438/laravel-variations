@props(['sliders'])

@if($sliders && $sliders->count() > 0)
<div class="slider-container position-relative mb-5">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <!-- Indicators -->
        <div class="carousel-indicators">
            @foreach($sliders as $index => $slider)
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $index }}" 
                        class="{{ $index === 0 ? 'active' : '' }}" 
                        aria-current="{{ $index === 0 ? 'true' : 'false' }}" 
                        aria-label="Slide {{ $index + 1 }}"></button>
            @endforeach
        </div>

        <!-- Slider Items -->
        <div class="carousel-inner">
            @foreach($sliders as $index => $slider)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                    <div class="slider-item position-relative d-flex align-items-center" 
                         style="height: 500px; 
                                background: linear-gradient(135deg, 
                                    {{ $slider->button_color }}40 0%, 
                                    {{ $slider->button_color }}80 100%),
                                    linear-gradient(45deg, #667eea 0%, #764ba2 100%);">
                        
                        <!-- Background Image with Overlay (if image exists) -->
                        @if(file_exists(storage_path('app/public/' . $slider->image_path)))
                            <img src="{{ $slider->image_url }}" 
                                 class="position-absolute w-100 h-100" 
                                 alt="{{ $slider->image_alt ?: $slider->title }}"
                                 style="z-index: 1; object-fit: cover;">
                            <div class="position-absolute w-100 h-100 bg-dark" style="z-index: 2; opacity: 0.4;"></div>
                        @endif
                        
                        <!-- Content Container -->
                        <div class="container position-relative" style="z-index: 3;">
                            <div class="row h-100 align-items-center">
                                <div class="col-lg-8 {{ $slider->text_position === 'center' ? 'mx-auto text-center' : ($slider->text_position === 'right' ? 'ms-auto text-end' : '') }}">
                                    <div class="slider-content" style="color: {{ $slider->text_color }};">
                                        <h1 class="display-4 fw-bold mb-3 slide-title" 
                                            style="text-shadow: 2px 2px 4px rgba(0,0,0,0.7);">
                                            {{ $slider->title }}
                                        </h1>
                                        
                                        @if($slider->description)
                                            <p class="lead mb-4 slide-description" 
                                               style="text-shadow: 1px 1px 3px rgba(0,0,0,0.7); max-width: 600px;">
                                                {{ $slider->description }}
                                            </p>
                                        @endif
                                        
                                        @if($slider->button_text && $slider->button_link)
                                            <a href="{{ $slider->button_link }}" 
                                               class="btn btn-lg px-4 py-3 slide-button shadow-lg rounded-pill"
                                               style="background-color: {{ $slider->button_color }}; 
                                                      border-color: {{ $slider->button_color }}; 
                                                      color: white; 
                                                      transition: all 0.3s ease;">
                                                {{ $slider->button_text }}
                                                <i class="bi bi-arrow-right ms-2"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Navigation Controls -->
        @if($sliders->count() > 1)
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        @endif
    </div>

    <!-- Progress Bar -->
    <div class="slider-progress-container position-absolute bottom-0 start-0 w-100" style="z-index: 4; height: 4px; background: rgba(255,255,255,0.3);">
        <div class="slider-progress bg-white" style="height: 100%; width: 0%;"></div>
    </div>
</div>

<!-- Enhanced Bootstrap 5 Slider Styles -->
<style>
/* Carousel Fade Effect */
.carousel-fade .carousel-item {
    opacity: 0;
    transition-property: opacity;
    transform: none;
}

.carousel-fade .carousel-item.active,
.carousel-fade .carousel-item-next.carousel-item-start,
.carousel-fade .carousel-item-prev.carousel-item-end {
    z-index: 1;
    opacity: 1;
}

.carousel-fade .active.carousel-item-start,
.carousel-fade .active.carousel-item-end {
    z-index: 0;
    opacity: 0;
    transition: opacity 0s 0.6s;
}

/* Custom Indicators */
.slider-container .carousel-indicators {
    bottom: 20px;
    margin-bottom: 0;
}

.slider-container .carousel-indicators [data-bs-target] {
    width: 15px;
    height: 15px;
    border-radius: 50%;
    margin: 0 8px;
    background-color: rgba(255, 255, 255, 0.4);
    border: 2px solid rgba(255, 255, 255, 0.8);
    transition: all 0.3s ease;
    opacity: 1;
}

.slider-container .carousel-indicators [data-bs-target].active {
    background-color: #ffffff;
    border-color: #ffffff;
    transform: scale(1.3);
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
}

/* Enhanced Navigation Controls */
.slider-container .carousel-control-prev,
.slider-container .carousel-control-next {
    width: 5%;
    opacity: 0.8;
    transition: all 0.3s ease;
}

.slider-container .carousel-control-prev:hover,
.slider-container .carousel-control-next:hover {
    opacity: 1;
}

.slider-container .carousel-control-prev-icon,
.slider-container .carousel-control-next-icon {
    width: 3rem;
    height: 3rem;
    background-color: rgba(0, 0, 0, 0.5);
    border-radius: 50%;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.slider-container .carousel-control-prev:hover .carousel-control-prev-icon,
.slider-container .carousel-control-next:hover .carousel-control-next-icon {
    background-color: rgba(0, 0, 0, 0.7);
    transform: scale(1.1);
    border-color: rgba(255, 255, 255, 0.6);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

/* Button Hover Effects */
.slide-button:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3) !important;
}

/* Content Animations */
.carousel-item.active .slide-title {
    animation: slideInUp 0.8s ease-out;
}

.carousel-item.active .slide-description {
    animation: slideInUp 0.8s ease-out 0.2s both;
}

.carousel-item.active .slide-button {
    animation: slideInUp 0.8s ease-out 0.4s both;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Design */
@media (max-width: 992px) {
    .slider-item {
        height: 450px !important;
    }
    
    .slider-content h1 {
        font-size: 2.5rem !important;
    }
}

@media (max-width: 768px) {
    .slider-item {
        height: 400px !important;
    }
    
    .slider-content h1 {
        font-size: 2rem !important;
        line-height: 1.2;
    }
    
    .slider-content p {
        font-size: 1.1rem !important;
        line-height: 1.4;
    }
    
    .slider-content .btn {
        font-size: 0.95rem !important;
        padding: 12px 24px !important;
    }
    
    .carousel-control-prev,
    .carousel-control-next {
        width: 10%;
    }
    
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        width: 2.5rem;
        height: 2.5rem;
    }
}

@media (max-width: 576px) {
    .slider-item {
        height: 350px !important;
    }
    
    .slider-content h1 {
        font-size: 1.75rem !important;
        margin-bottom: 1rem !important;
    }
    
    .slider-content p {
        font-size: 1rem !important;
        margin-bottom: 1.5rem !important;
    }
    
    .slider-content .btn {
        font-size: 0.9rem !important;
        padding: 10px 20px !important;
    }
    
    .carousel-indicators {
        bottom: 15px !important;
    }
    
    .carousel-indicators [data-bs-target] {
        width: 12px;
        height: 12px;
        margin: 0 5px;
    }
    
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        width: 2rem;
        height: 2rem;
    }
}
</style>

<!-- Enhanced Slider JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('heroCarousel');
    const progressBar = document.querySelector('.slider-progress');
    
    if (carousel && progressBar) {
        // Initialize Bootstrap Carousel
        const bsCarousel = new bootstrap.Carousel(carousel, {
            interval: 5000,
            ride: 'carousel',
            pause: 'hover',
            wrap: true,
            keyboard: true
        });
        
        // Progress bar animation
        function startProgressBar() {
            progressBar.style.width = '0%';
            progressBar.style.transition = 'width 5s linear';
            
            setTimeout(() => {
                progressBar.style.width = '100%';
            }, 50);
        }
        
        function resetProgressBar() {
            progressBar.style.transition = 'none';
            progressBar.style.width = '0%';
        }
        
        // Initialize progress bar
        startProgressBar();
        
        // Handle slide events
        carousel.addEventListener('slide.bs.carousel', function() {
            resetProgressBar();
        });
        
        carousel.addEventListener('slid.bs.carousel', function() {
            startProgressBar();
        });
        
        // Pause/Resume on hover
        carousel.addEventListener('mouseenter', function() {
            bsCarousel.pause();
        });
        
        carousel.addEventListener('mouseleave', function() {
            bsCarousel.cycle();
            startProgressBar();
        });
        
        // Touch/Swipe Support
        let startX = 0;
        let startY = 0;
        const minSwipeDistance = 50;
        
        carousel.addEventListener('touchstart', function(e) {
            const touch = e.touches[0];
            startX = touch.clientX;
            startY = touch.clientY;
        }, { passive: true });
        
        carousel.addEventListener('touchend', function(e) {
            const touch = e.changedTouches[0];
            const endX = touch.clientX;
            const endY = touch.clientY;
            
            const diffX = startX - endX;
            const diffY = Math.abs(startY - endY);
            
            // Only trigger swipe if horizontal movement is greater than vertical
            if (Math.abs(diffX) > minSwipeDistance && Math.abs(diffX) > diffY) {
                if (diffX > 0) {
                    bsCarousel.next();
                } else {
                    bsCarousel.prev();
                }
            }
        }, { passive: true });
        
        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (carousel.contains(document.activeElement) || document.activeElement === document.body) {
                switch(e.key) {
                    case 'ArrowLeft':
                        e.preventDefault();
                        bsCarousel.prev();
                        break;
                    case 'ArrowRight':
                        e.preventDefault();
                        bsCarousel.next();
                        break;
                }
            }
        });
        
        // Auto-pause when page is not visible
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                bsCarousel.pause();
            } else {
                bsCarousel.cycle();
                startProgressBar();
            }
        });
    }
});
</script>
@endif
