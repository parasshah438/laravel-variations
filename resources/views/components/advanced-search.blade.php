<!-- Advanced Search Component -->
<div class="advanced-search-component position-relative">
    <form action="{{ route('search') }}" method="GET" class="search-form d-flex position-relative">
        <!-- Search Input -->
        <div class="input-group search-input-group">
            <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" 
                   class="form-control border-start-0 search-input" 
                   name="q" 
                   id="header-search-input"
                   placeholder="Search products, brands, categories..."
                   value="{{ request('q') }}"
                   autocomplete="off">
            
            <!-- Voice Search Button -->
            <button type="button" class="btn btn-outline-secondary voice-search-btn" id="voice-search-btn" title="Voice Search">
                <i class="bi bi-mic"></i>
                <i class="fas fa-microphone" style="display: none;"></i>
                <span style="display: none;">🎤</span>
            </button>
            
            <!-- Visual Search Button -->
            <button type="button" class="btn btn-outline-secondary visual-search-btn" id="visual-search-btn" title="Search by Image">
                <i class="bi bi-camera"></i>
                <i class="fas fa-camera" style="display: none;"></i>
                <span style="display: none;">📷</span>
            </button>
            
            <!-- Search Button -->
            <button type="submit" class="btn btn-primary search-submit-btn">
                <span class="d-none d-md-inline">Search</span>
                <i class="bi bi-arrow-right d-md-none"></i>
            </button>
        </div>
        
        <!-- Search Suggestions Dropdown -->
        <div class="search-suggestions-dropdown position-absolute w-100 bg-white border rounded-bottom shadow-lg" 
             id="search-suggestions" style="top: 100%; z-index: 1050; display: none;">
            
            <!-- Loading State -->
            <div class="suggestions-loading p-3 text-center" style="display: none;">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="ms-2 text-muted">Searching...</span>
            </div>
            
            <!-- Quick Results -->
            <div class="quick-results" id="quick-results" style="display: none;">
                <div class="suggestions-header p-2 border-bottom bg-light">
                    <small class="text-muted fw-bold">Quick Results</small>
                </div>
                <div class="quick-results-list" id="quick-results-list">
                    <!-- Dynamic content -->
                </div>
            </div>
            
            <!-- Search Suggestions -->
            <div class="search-suggestions-list" id="suggestions-list" style="display: none;">
                <div class="suggestions-header p-2 border-bottom bg-light">
                    <small class="text-muted fw-bold">Search Suggestions</small>
                </div>
                <div class="suggestions-content" id="suggestions-content">
                    <!-- Dynamic content -->
                </div>
            </div>
            
            <!-- Trending Searches -->
            <div class="trending-searches" id="trending-searches" style="display: none;">
                <div class="suggestions-header p-2 border-bottom bg-light">
                    <small class="text-muted fw-bold">
                        <i class="bi bi-fire me-1 text-danger"></i>Trending
                    </small>
                </div>
                <div class="trending-content p-2">
                    <div class="d-flex flex-wrap gap-1" id="trending-content">
                        <!-- Dynamic content -->
                    </div>
                </div>
            </div>
            
            <!-- Recent Searches (if user is logged in) -->
            @auth
            <div class="recent-searches" id="recent-searches" style="display: none;">
                <div class="suggestions-header p-2 border-bottom bg-light">
                    <small class="text-muted fw-bold">
                        <i class="bi bi-clock me-1"></i>Recent Searches
                    </small>
                </div>
                <div class="recent-content" id="recent-content">
                    <!-- Dynamic content -->
                </div>
            </div>
            @endauth
            
            <!-- View All Results Link -->
            <div class="view-all-results p-2 border-top bg-light" id="view-all-link" style="display: none;">
                <a href="#" class="btn btn-sm btn-outline-primary w-100" id="view-all-results-btn">
                    View all results for "<span id="current-query"></span>"
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Voice Search Modal -->
<div class="modal" id="voiceSearchModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-mic me-2"></i>Voice Search
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="voice-animation mb-4">
                    <div class="voice-circle" id="voice-circle">
                        <i class="bi bi-mic voice-icon" id="voice-icon"></i>
                    </div>
                </div>
                <p class="voice-status" id="voice-status">Click the microphone to start speaking</p>
                <button type="button" class="btn btn-primary btn-lg" id="start-voice-search">
                    <i class="bi bi-mic me-2"></i>Start Voice Search
                </button>
                <div class="mt-3">
                    <small class="text-muted">Say something like "Show me red shoes" or "Search for laptops"</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Visual Search Modal -->
<div class="modal" id="visualSearchModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-camera me-2"></i>Visual Search
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="upload-area border-2 border-dashed rounded p-4 text-center" id="upload-area">
                            <i class="bi bi-cloud-upload display-4 text-muted mb-3"></i>
                            <h6>Upload an Image</h6>
                            <p class="text-muted mb-3">Drag and drop or click to select</p>
                            <input type="file" class="form-control d-none" id="image-upload" accept="image/*">
                            <button type="button" class="btn btn-outline-primary" onclick="$('#image-upload').click()">
                                Choose File
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="camera-area border-2 border-dashed rounded p-4 text-center">
                            <i class="bi bi-camera display-4 text-muted mb-3"></i>
                            <h6>Use Camera</h6>
                            <p class="text-muted mb-3">Take a photo to search</p>
                            <button type="button" class="btn btn-outline-primary" id="start-camera">
                                <i class="bi bi-camera me-2"></i>Start Camera
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Camera Preview -->
                <div class="camera-preview mt-4" id="camera-preview" style="display: none;">
                    <video id="camera-video" class="w-100 rounded" autoplay></video>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-primary me-2" id="capture-photo">
                            <i class="bi bi-camera me-2"></i>Capture
                        </button>
                        <button type="button" class="btn btn-secondary" id="stop-camera">
                            Stop Camera
                        </button>
                    </div>
                </div>
                
                <!-- Preview Selected Image -->
                <div class="image-preview mt-4" id="image-preview" style="display: none;">
                    <div class="text-center">
                        <img id="preview-image" class="img-fluid rounded" style="max-height: 300px;">
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" id="search-by-image">
                                <i class="bi bi-search me-2"></i>Search Similar Products
                            </button>
                            <button type="button" class="btn btn-outline-secondary ms-2" id="remove-image">
                                Remove Image
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Voice and Visual Search Button Styles */
.voice-search-btn,
.visual-search-btn {
    border-color: #dee2e6 !important;
    color: #6c757d !important;
    min-width: 44px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 10px 12px !important;
    background-color: white !important;
}

.voice-search-btn:hover,
.visual-search-btn:hover {
    background-color: #f8f9fa !important;
    border-color: #6c757d !important;
    color: #495057 !important;
}

.voice-search-btn i,
.visual-search-btn i {
    font-size: 18px !important;
    line-height: 1 !important;
    width: 18px !important;
    height: 18px !important;
    display: inline-block !important;
}

/* Force Bootstrap Icons to display */
@font-face {
    font-family: "bootstrap-icons";
    src: url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/fonts/bootstrap-icons.woff2") format("woff2"),
         url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/fonts/bootstrap-icons.woff") format("woff");
}

.bi {
    font-family: "bootstrap-icons", "Bootstrap Icons" !important;
    font-style: normal !important;
    font-weight: normal !important;
    font-variant: normal !important;
    text-transform: none !important;
    line-height: 1 !important;
    vertical-align: -.125em !important;
    -webkit-font-smoothing: antialiased !important;
    -moz-osx-font-smoothing: grayscale !important;
}

.bi-mic::before {
    content: "\f3e5" !important;
}

.bi-camera::before {
    content: "\f1c0" !important;
}

/* Modal Backdrop Fix */
.modal {
    z-index: 9999 !important;
}

.modal-backdrop {
    display: none !important;
}

.modal-dialog {
    z-index: 10000 !important;
    position: relative;
}

/* Ensure modal content is clickable */
.modal-content {
    z-index: 10001 !important;
    position: relative;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3) !important;
    border: none !important;
}

/* Custom modal overlay effect without backdrop */
.modal.show {
    background-color: rgba(0,0,0,0.3);
}

.advanced-search-component {
    min-width: 300px;
    max-width: 600px;
    width: 100%;
}

.search-input-group {
    border-radius: 25px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: box-shadow 0.3s ease;
}

.search-input-group:focus-within {
    box-shadow: 0 4px 20px rgba(13, 110, 253, 0.2);
}

.search-input {
    border: none;
    padding: 12px 16px;
    font-size: 16px;
}

.search-input:focus {
    box-shadow: none;
    border-color: transparent;
}

.search-suggestions-dropdown {
    max-height: 400px;
    overflow-y: auto;
    border-top: none;
    margin-top: -1px;
}

.search-suggestion {
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s ease;
}

.search-suggestion:hover,
.search-suggestion.active {
    background-color: #f8f9fa;
}

.search-suggestion:last-child {
    border-bottom: none;
}

.quick-result-item {
    display: flex;
    align-items: center;
    padding: 8px 16px;
    text-decoration: none;
    color: inherit;
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s ease;
}

.quick-result-item:hover {
    background-color: #f8f9fa;
    color: inherit;
    text-decoration: none;
}

.quick-result-image {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
    margin-right: 12px;
}

.quick-result-info h6 {
    font-size: 14px;
    margin: 0 0 4px 0;
}

.quick-result-info small {
    color: #6c757d;
}

.trending-tag {
    display: inline-block;
    padding: 4px 8px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 12px;
    font-size: 12px;
    color: #495057;
    text-decoration: none;
    margin: 2px;
    transition: all 0.2s ease;
}

.trending-tag:hover {
    background-color: #e9ecef;
    color: #495057;
    text-decoration: none;
}

/* Voice Search Animation */
.voice-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(45deg, #007bff, #6610f2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.voice-circle:hover {
    transform: scale(1.05);
}

.voice-circle.listening {
    animation: pulse 1.5s infinite;
}

.voice-icon {
    font-size: 2rem;
    color: white;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7);
    }
    70% {
        box-shadow: 0 0 0 20px rgba(0, 123, 255, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
    }
}

/* Visual Search Styles */
.upload-area {
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-area:hover,
.upload-area.dragover {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.camera-area {
    transition: all 0.3s ease;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .advanced-search-component {
        min-width: auto;
    }
    
    .search-input-group {
        border-radius: 20px;
    }
    
    .search-input {
        padding: 10px 12px;
        font-size: 14px;
    }
    
    .voice-search-btn,
    .visual-search-btn {
        padding: 8px 10px;
        font-size: 12px;
    }
    
    .search-suggestions-dropdown {
        max-height: 300px;
    }
}

@media (max-width: 576px) {
    .search-submit-btn .d-none {
        display: inline !important;
    }
    
    .search-submit-btn .d-md-none {
        display: none !important;
    }
}
</style>

@push('scripts')
<script>
// Prevent multiple initialization
if (typeof window.advancedSearchInitialized === 'undefined') {
    window.advancedSearchInitialized = true;
    
    // Global scope variables and functions
    let stream = null;
    let capturedImageBlob = null;

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        $('#camera-preview').hide();
        $('.camera-area').show();
    }

    $(document).ready(function() {
    let searchTimeout;
    let currentSuggestionIndex = -1;
    let suggestions = [];
    let isVoiceSearchSupported = 'webkitSpeechRecognition' in window || 'SpeechRecognition' in window;
    let recognition;

    const searchInput = $('#header-search-input');
    const suggestionsDropdown = $('#search-suggestions');
    const suggestionsLoading = $('.suggestions-loading');
    const quickResults = $('#quick-results');
    const suggestionsList = $('#suggestions-list');
    const trendingSearches = $('#trending-searches');
    const recentSearches = $('#recent-searches');
    const viewAllLink = $('#view-all-link');

    // Initialize voice search
    if (isVoiceSearchSupported) {
        initializeVoiceSearch();
    } else {
        // Show button but with fallback behavior
        $('.voice-search-btn').show().click(function() {
            alert('Voice search is not supported in your browser. Please try Chrome, Firefox, or Edge.');
        });
    }

    // Initialize visual search (always available)
    initializeVisualSearch();
    
    // Icon fallback system
    setTimeout(function() {
        // Check if Bootstrap Icons are working
        const testBootstrapIcon = $('<i class="bi bi-heart-fill"></i>').appendTo('body');
        const bsIconContent = window.getComputedStyle(testBootstrapIcon[0], ':before').content;
        testBootstrapIcon.remove();
        
        if (!bsIconContent || bsIconContent === 'none' || bsIconContent === '""') {
            // Hide Bootstrap Icons, show Font Awesome
            $('.bi').hide();
            $('.fas').show();
        } else {
            // Bootstrap Icons are working
        }
        
        // Final fallback to emoji if neither icon font works
        setTimeout(function() {
            if (!$('#voice-search-btn i:visible').length) {
                $('#voice-search-btn i').hide();
                $('#voice-search-btn span').show();
                $('#visual-search-btn i').hide();
                $('#visual-search-btn span').show();
            }
        }, 100);
    }, 500);

    // Search input events
    searchInput.on('input', function() {
        const query = $(this).val().trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => {
                fetchSuggestions(query);
            }, 300);
        } else if (query.length === 0) {
            showTrendingSearches();
        } else {
            hideSuggestions();
        }
    });

    searchInput.on('focus', function() {
        const query = $(this).val().trim();
        if (query.length >= 2) {
            fetchSuggestions(query);
        } else {
            showTrendingSearches();
        }
    });

    searchInput.on('blur', function() {
        // Delay hiding to allow clicking on suggestions
        setTimeout(() => {
            hideSuggestions();
        }, 200);
    });

    // Keyboard navigation
    searchInput.on('keydown', function(e) {
        const suggestionItems = $('.search-suggestion:visible, .quick-result-item:visible');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentSuggestionIndex = Math.min(currentSuggestionIndex + 1, suggestionItems.length - 1);
            updateSuggestionHighlight(suggestionItems);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentSuggestionIndex = Math.max(currentSuggestionIndex - 1, -1);
            updateSuggestionHighlight(suggestionItems);
        } else if (e.key === 'Enter' && currentSuggestionIndex >= 0) {
            e.preventDefault();
            suggestionItems.eq(currentSuggestionIndex).click();
        } else if (e.key === 'Escape') {
            hideSuggestions();
        }
    });

    // Voice search button
    $('#voice-search-btn').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (isVoiceSearchSupported) {
            const voiceModal = new bootstrap.Modal(document.getElementById('voiceSearchModal'), {
                backdrop: false,
                keyboard: true,
                focus: true
            });
            voiceModal.show();
        }
    });

    // Visual search button
    $('#visual-search-btn').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const visualModal = new bootstrap.Modal(document.getElementById('visualSearchModal'), {
            backdrop: false,
            keyboard: true,
            focus: true
        });
        visualModal.show();
    });

    // Modal event handlers
    $('#voiceSearchModal').on('shown.bs.modal', function() {
        // Voice modal opened
    });
    
    $('#voiceSearchModal').on('hidden.bs.modal', function() {
        // Voice modal closed
    });
    
    $('#visualSearchModal').on('shown.bs.modal', function() {
        // Visual modal opened
    });
    
    $('#visualSearchModal').on('hidden.bs.modal', function() {
        // Visual modal closed
        stopCamera();
        $('#image-preview').hide();
        $('#upload-area, .camera-area').show();
        $('#image-upload').val('');
    });

    // Fetch suggestions from server
    function fetchSuggestions(query) {
        showLoading();
        
        // Fetch both suggestions and quick results
        $.when(
            $.get('{{ route("search.suggestions") }}', { q: query }),
            $.get('{{ route("search.quick") }}', { q: query })
        ).done(function(suggestionsResponse, quickResponse) {
            hideLoading();
            
            if (suggestionsResponse[0].success) {
                displaySuggestions(suggestionsResponse[0].suggestions, query);
            }
            
            if (quickResponse[0].success && quickResponse[0].results.length > 0) {
                displayQuickResults(quickResponse[0].results, query);
            }
            
            showSuggestions();
        }).fail(function() {
            hideLoading();
            console.error('Failed to fetch suggestions');
        });
    }

    // Display search suggestions
    function displaySuggestions(suggestionsData, query) {
        const suggestionsContent = $('#suggestions-content');
        let html = '';
        
        suggestionsData.forEach(suggestion => {
            html += `
                <div class="search-suggestion" data-suggestion="${suggestion}">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-search text-muted me-3"></i>
                        <span>${highlightQuery(suggestion, query)}</span>
                    </div>
                </div>
            `;
        });
        
        suggestionsContent.html(html);
        suggestionsList.show();
        
        // Add click handlers
        $('.search-suggestion').click(function() {
            const suggestion = $(this).data('suggestion');
            searchInput.val(suggestion);
            $('.search-form').submit();
        });
    }

    // Display quick results
    function displayQuickResults(results, query) {
        const quickResultsList = $('#quick-results-list');
        let html = '';
        
        results.slice(0, 5).forEach(product => {
            const imageUrl = product.images && product.images.length > 0 
                ? `{{ asset('storage/') }}/${product.images[0].image_path}` 
                : '{{ asset("images/no-image.jpg") }}';
            
            html += `
                <a href="/product/${product.slug}" class="quick-result-item">
                    <img src="${imageUrl}" alt="${product.name}" class="quick-result-image">
                    <div class="quick-result-info">
                        <h6>${highlightQuery(product.name, query)}</h6>
                        <small class="text-muted">
                            ${product.category ? product.category.name : ''} 
                            ${product.brand ? '• ' + product.brand.name : ''}
                        </small>
                    </div>
                </a>
            `;
        });
        
        if (results.length > 5) {
            $('#current-query').text(query);
            $('#view-all-results-btn').attr('href', `{{ route('search') }}?q=${encodeURIComponent(query)}`);
            viewAllLink.show();
        }
        
        quickResultsList.html(html);
        quickResults.show();
    }

    // Show trending searches
    function showTrendingSearches() {
        $.get('{{ route("search.trending") }}')
        .done(function(response) {
            if (response.success) {
                displayTrendingSearches(response.trending);
                showSuggestions();
            }
        });
    }

    // Display trending searches
    function displayTrendingSearches(trending) {
        const trendingContent = $('#trending-content');
        let html = '';
        
        trending.forEach(trend => {
            html += `<a href="{{ route('search') }}?q=${encodeURIComponent(trend)}" class="trending-tag">${trend}</a>`;
        });
        
        trendingContent.html(html);
        trendingSearches.show();
    }

    // Utility functions
    function showLoading() {
        hideSuggestionSections();
        suggestionsLoading.show();
        suggestionsDropdown.show();
    }

    function hideLoading() {
        suggestionsLoading.hide();
    }

    function showSuggestions() {
        suggestionsDropdown.show();
    }

    function hideSuggestions() {
        suggestionsDropdown.hide();
        hideSuggestionSections();
        currentSuggestionIndex = -1;
    }

    function hideSuggestionSections() {
        quickResults.hide();
        suggestionsList.hide();
        trendingSearches.hide();
        recentSearches.hide();
        viewAllLink.hide();
    }

    function updateSuggestionHighlight(items) {
        items.removeClass('active');
        if (currentSuggestionIndex >= 0) {
            items.eq(currentSuggestionIndex).addClass('active');
        }
    }

    function highlightQuery(text, query) {
        if (!query) return text;
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<strong>$1</strong>');
    }

    // Voice search functionality
    function initializeVoiceSearch() {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US';

        recognition.onstart = function() {
            $('#voice-circle').addClass('listening');
            $('#voice-status').text('Listening... Speak now');
            $('#start-voice-search').prop('disabled', true);
        };

        recognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            $('#voice-status').text(`You said: "${transcript}"`);
            
            setTimeout(() => {
                searchInput.val(transcript);
                $('#voiceSearchModal').modal('hide');
                $('.search-form').submit();
            }, 1000);
        };

        recognition.onerror = function(event) {
            $('#voice-status').text('Error occurred in recognition: ' + event.error);
            $('#voice-circle').removeClass('listening');
            $('#start-voice-search').prop('disabled', false);
        };

        recognition.onend = function() {
            $('#voice-circle').removeClass('listening');
            $('#start-voice-search').prop('disabled', false);
        };

        $('#start-voice-search').click(function() {
            recognition.start();
        });
    }

    // Visual search functionality
    function initializeVisualSearch() {
        // File upload
        $('#image-upload').change(function(e) {
            const file = e.target.files[0];
            if (file) {
                displayImagePreview(file);
            }
        });

        // Drag and drop
        $('#upload-area')
            .on('dragover dragenter', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            })
            .on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            })
            .on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    displayImagePreview(files[0]);
                }
            });

        // Camera functionality
        $('#start-camera').click(function() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(mediaStream) {
                    stream = mediaStream;
                    $('#camera-video')[0].srcObject = stream;
                    $('#camera-preview').show();
                    $('.camera-area').hide();
                })
                .catch(function(err) {
                    alert('Error accessing camera: ' + err.message);
                });
        });

        $('#capture-photo').click(function() {
            const video = $('#camera-video')[0];
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);
            
            canvas.toBlob(function(blob) {
                capturedImageBlob = blob;
                displayImagePreview(blob);
                stopCamera();
            }, 'image/jpeg', 0.8);
        });

        $('#stop-camera').click(stopCamera);

        $('#remove-image').click(function() {
            $('#image-preview').hide();
            $('#upload-area, .camera-area').show();
            $('#image-upload').val('');
            capturedImageBlob = null;
        });

        $('#search-by-image').click(function() {
            const imageFile = $('#image-upload')[0].files[0] || capturedImageBlob;
            
            if (!imageFile) {
                alert('Please select an image first');
                return;
            }

            // Show loading state
            $(this).html('<i class="spinner-border spinner-border-sm me-2"></i>Searching...');
            $(this).prop('disabled', true);

            // Create FormData for file upload
            const formData = new FormData();
            formData.append('image', imageFile);
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

            // Send visual search request
            $.ajax({
                url: '/visual-search/image',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Hide modal
                        $('#visualSearchModal').modal('hide');
                        
                        // Redirect to search results with visual search parameter
                        const searchUrl = new URL('{{ route("search") }}', window.location.origin);
                        searchUrl.searchParams.set('visual', '1');
                        searchUrl.searchParams.set('results', response.total);
                        
                        // Store results in session storage for the search page
                        sessionStorage.setItem('visualSearchResults', JSON.stringify(response.results));
                        
                        window.location.href = searchUrl.toString();
                    } else {
                        alert(response.message || 'Visual search failed. Please try again.');
                    }
                },
                error: function(xhr) {
                    console.error('Visual search error:', xhr);
                    alert('Error processing image. Please try again.');
                },
                complete: function() {
                    // Reset button state
                    $('#search-by-image').html('<i class="bi bi-search me-2"></i>Search Similar Products');
                    $('#search-by-image').prop('disabled', false);
                }
            });
        });

        function displayImagePreview(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview-image').attr('src', e.target.result);
                $('#image-preview').show();
                $('#upload-area, .camera-area').hide();
            };
            reader.readAsDataURL(file);
        }

        // Clean up on modal close
        $('#visualSearchModal').on('hidden.bs.modal', function() {
            stopCamera();
            $('#image-preview').hide();
            $('#upload-area, .camera-area').show();
            $('#image-upload').val('');
            capturedImageBlob = null;
        });
    }

    // Close dropdown when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('.advanced-search-component').length) {
            hideSuggestions();
        }
    });
});

} // End of advancedSearchInitialized check
</script>
@endpush
