@props(['sliders'])

@if($sliders && $sliders->count() > 0)
<div class="slider-container position-relative mb-5">
    <div id="heroSlider" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
        <!-- Indicators -->
        <div class="carousel-indicators">
            @foreach($sliders as $index => $slider)
                <button type="button" data-bs-target="#heroSlider" data-bs-slide-to="{{ $index }}" 
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
                                 class="d-block w-100 h-100 object-fit-cover position-absolute" 
                                 alt="{{ $slider->image_alt ?: $slider->title }}"
                                 style="z-index: 1;">
                            <div class="position-absolute w-100 h-100 bg-dark opacity-40" style="z-index: 2;"></div>
                        @endif
                        
                        <!-- Content -->
                        <div class="carousel-caption position-absolute w-100 h-100 d-flex align-items-center justify-content-{{ $slider->text_position }}" 
                             style="z-index: 3; top: 0; {{ $slider->text_position === 'left' ? 'text-align: left; padding-left: 5%;' : ($slider->text_position === 'right' ? 'text-align: right; padding-right: 5%;' : 'text-align: center;') }}">
                            <div class="slider-content" style="color: {{ $slider->text_color }}; max-width: 600px;">
                                <h1 class="display-4 fw-bold mb-3 slide-title" 
                                    style="text-shadow: 2px 2px 4px rgba(0,0,0,0.5); animation: slideInUp 1s ease-out;">
                                    {{ $slider->title }}
                                </h1>
                                
                                @if($slider->description)
                                    <p class="lead mb-4 slide-description" 
                                       style="text-shadow: 1px 1px 2px rgba(0,0,0,0.5); animation: slideInUp 1s ease-out 0.2s both;">
                                        {{ $slider->description }}
                                    </p>
                                @endif
                                
                                @if($slider->button_text && $slider->button_link)
                                    <a href="{{ $slider->button_link }}" 
                                       class="btn btn-lg px-4 py-2 slide-button"
                                       style="background-color: {{ $slider->button_color }}; 
                                              border-color: {{ $slider->button_color }}; 
                                              color: white; 
                                              animation: slideInUp 1s ease-out 0.4s both;
                                              transition: all 0.3s ease;
                                              box-shadow: 0 4px 15px rgba(0,0,0,0.2);"
                                       onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.3)';"
                                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.2)';">
                                        {{ $slider->button_text }}
                                        <i class="bi bi-arrow-right ms-2"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Navigation Controls -->
        @if($sliders->count() > 1)
            <button class="carousel-control-prev" type="button" data-bs-target="#heroSlider" data-bs-slide="prev">
                <div class="carousel-control-icon bg-dark bg-opacity-50 rounded-circle p-2">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </div>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroSlider" data-bs-slide="next">
                <div class="carousel-control-icon bg-dark bg-opacity-50 rounded-circle p-2">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </div>
                <span class="visually-hidden">Next</span>
            </button>
        @endif
    </div>

    <!-- Progress Bar for Auto-play -->
    <div class="slider-progress-container position-absolute bottom-0 start-0 w-100" style="z-index: 4; height: 4px;">
        <div class="slider-progress bg-primary" style="height: 100%; width: 0%; transition: width 5s linear;"></div>
    </div>
</div>

<!-- Slider Styles -->
<style>
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.slider-container .carousel-item {
    transition: transform 0.6s ease-in-out;
}

.slider-container .carousel-indicators button {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin: 0 6px;
    background-color: rgba(255, 255, 255, 0.5);
    border: 2px solid white;
    transition: all 0.3s ease;
}

.slider-container .carousel-indicators button.active {
    background-color: white;
    transform: scale(1.2);
}

.carousel-control-icon {
    transition: all 0.3s ease;
}

.carousel-control-prev:hover .carousel-control-icon,
.carousel-control-next:hover .carousel-control-icon {
    background-color: rgba(0, 0, 0, 0.7) !important;
    transform: scale(1.1);
}

/* Responsive Design */
@media (max-width: 768px) {
    .slider-item {
        height: 400px !important;
    }
    
    .slider-content h1 {
        font-size: 2rem !important;
    }
    
    .slider-content p {
        font-size: 1rem !important;
    }
    
    .slider-content .btn {
        font-size: 0.9rem !important;
        padding: 8px 20px !important;
    }
}

@media (max-width: 576px) {
    .slider-item {
        height: 350px !important;
    }
    
    .slider-content h1 {
        font-size: 1.5rem !important;
    }
    
    .carousel-caption {
        padding-left: 3% !important;
        padding-right: 3% !important;
    }
}
</style>

<!-- Slider JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('heroSlider');
    const progressBar = document.querySelector('.slider-progress');
    
    if (slider && progressBar) {
        const carousel = new bootstrap.Carousel(slider, {
            interval: 5000,
            ride: 'carousel'
        });
        
        // Reset and animate progress bar
        function resetProgressBar() {
            progressBar.style.width = '0%';
            setTimeout(() => {
                progressBar.style.width = '100%';
            }, 50);
        }
        
        // Initialize progress bar
        resetProgressBar();
        
        // Reset progress bar on slide change
        slider.addEventListener('slide.bs.carousel', function() {
            resetProgressBar();
        });
        
        // Pause carousel on hover
        slider.addEventListener('mouseenter', function() {
            carousel.pause();
            progressBar.style.animationPlayState = 'paused';
        });
        
        slider.addEventListener('mouseleave', function() {
            carousel.cycle();
            progressBar.style.animationPlayState = 'running';
        });
        
        // Touch support for mobile
        let startX = 0;
        let endX = 0;
        
        slider.addEventListener('touchstart', function(e) {
            startX = e.touches[0].clientX;
        });
        
        slider.addEventListener('touchend', function(e) {
            endX = e.changedTouches[0].clientX;
            const diff = startX - endX;
            
            if (Math.abs(diff) > 50) { // Minimum swipe distance
                if (diff > 0) {
                    carousel.next();
                } else {
                    carousel.prev();
                }
            }
        });
    }
});
</script>
@endif
