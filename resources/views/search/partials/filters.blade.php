<!-- Categories Filter -->
@if(isset($results['facets']['categories']) && $results['facets']['categories']->count() > 0)
<div class="filter-group">
    <h6 class="filter-title mb-3">
        <i class="bi bi-tag me-2"></i>Categories
    </h6>
    <div class="filter-options">
        @foreach($results['facets']['categories']->take(8) as $category)
            <div class="form-check mb-2">
                <input class="form-check-input filter-checkbox" type="checkbox" 
                       id="category-{{ $category->id }}" 
                       data-filter-type="categories" 
                       data-label="{{ $category->name }}"
                       value="{{ $category->id }}"
                       {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}>
                <label class="form-check-label d-flex justify-content-between align-items-center" 
                       for="category-{{ $category->id }}">
                    <span>{{ $category->name }}</span>
                    <small class="text-muted filter-count" 
                           data-filter-type="categories" 
                           data-filter-value="{{ $category->id }}">
                        ({{ $category->products_count ?? 0 }})
                    </small>
                </label>
            </div>
        @endforeach
        
        @if($results['facets']['categories']->count() > 8)
            <button type="button" class="btn btn-link btn-sm p-0 show-more-categories">
                Show {{ $results['facets']['categories']->count() - 8 }} more
            </button>
        @endif
    </div>
</div>
@endif

<!-- Brands Filter -->
@if(isset($results['facets']['brands']) && $results['facets']['brands']->count() > 0)
<div class="filter-group">
    <h6 class="filter-title mb-3">
        <i class="bi bi-award me-2"></i>Brands
    </h6>
    <div class="filter-options">
        @foreach($results['facets']['brands']->take(8) as $brand)
            <div class="form-check mb-2">
                <input class="form-check-input filter-checkbox" type="checkbox" 
                       id="brand-{{ $brand->id }}" 
                       data-filter-type="brands" 
                       data-label="{{ $brand->name }}"
                       value="{{ $brand->id }}"
                       {{ in_array($brand->id, request('brands', [])) ? 'checked' : '' }}>
                <label class="form-check-label d-flex justify-content-between align-items-center" 
                       for="brand-{{ $brand->id }}">
                    <span>{{ $brand->name }}</span>
                    <small class="text-muted filter-count" 
                           data-filter-type="brands" 
                           data-filter-value="{{ $brand->id }}">
                        ({{ $brand->products_count ?? 0 }})
                    </small>
                </label>
            </div>
        @endforeach
        
        @if($results['facets']['brands']->count() > 8)
            <button type="button" class="btn btn-link btn-sm p-0 show-more-brands">
                Show {{ $results['facets']['brands']->count() - 8 }} more
            </button>
        @endif
    </div>
</div>
@endif

<!-- Price Range Filter -->
<div class="filter-group">
    <h6 class="filter-title mb-3">
        <i class="bi bi-currency-rupee me-2"></i>Price Range
    </h6>
    <div class="price-filter">
        @if(isset($results['facets']['price_ranges']) && count($results['facets']['price_ranges']) > 0)
            <!-- Predefined Price Ranges -->
            <div class="price-ranges mb-3">
                @foreach($results['facets']['price_ranges'] as $range)
                    <div class="form-check mb-2">
                        <input class="form-check-input price-range-option" type="radio" 
                               name="price_range" 
                               id="price-range-{{ $loop->index }}" 
                               data-min="{{ $range['min'] }}" 
                               data-max="{{ $range['max'] }}"
                               value="{{ $range['min'] }}-{{ $range['max'] }}">
                        <label class="form-check-label d-flex justify-content-between align-items-center" 
                               for="price-range-{{ $loop->index }}">
                            <span>{{ $range['label'] }}</span>
                            <small class="text-muted">({{ $range['count'] }})</small>
                        </label>
                    </div>
                @endforeach
            </div>
            
            <div class="border-top pt-3">
                <small class="text-muted d-block mb-2">Custom Range</small>
        @endif
        
        <!-- Custom Price Range -->
        <div class="row g-2">
            <div class="col-6">
                <div class="form-floating">
                    <input type="number" class="form-control form-control-sm" 
                           id="price-min" placeholder="Min" 
                           value="{{ request('price_min') }}" min="0">
                    <label for="price-min">Min ₹</label>
                </div>
            </div>
            <div class="col-6">
                <div class="form-floating">
                    <input type="number" class="form-control form-control-sm" 
                           id="price-max" placeholder="Max" 
                           value="{{ request('price_max') }}" min="0">
                    <label for="price-max">Max ₹</label>
                </div>
            </div>
        </div>
        
        @if(isset($results['facets']['price_ranges']))
            </div>
        @endif
    </div>
</div>

<!-- Attributes Filter -->
@if(isset($results['facets']['attributes']) && $results['facets']['attributes']->count() > 0)
    @foreach($results['facets']['attributes'] as $attribute)
        @if($attribute->attributeValues && $attribute->attributeValues->count() > 0)
        <div class="filter-group">
            <h6 class="filter-title mb-3">
                <i class="bi bi-list-check me-2"></i>{{ $attribute->name }}
            </h6>
            <div class="filter-options">
                @foreach($attribute->attributeValues->take(6) as $value)
                    <div class="form-check mb-2">
                        <input class="form-check-input filter-checkbox" type="checkbox" 
                               id="attr-{{ $attribute->id }}-{{ $value->id }}" 
                               data-filter-type="attributes[{{ $attribute->id }}]" 
                               data-label="{{ $value->value }}"
                               value="{{ $value->id }}"
                               {{ isset(request('attributes')[$attribute->id]) && in_array($value->id, request('attributes')[$attribute->id]) ? 'checked' : '' }}>
                        <label class="form-check-label d-flex justify-content-between align-items-center" 
                               for="attr-{{ $attribute->id }}-{{ $value->id }}">
                            <span>{{ $value->value }}</span>
                            <small class="text-muted filter-count" 
                                   data-filter-type="attributes" 
                                   data-filter-value="{{ $value->id }}">
                                ({{ $value->product_variations_count ?? 0 }})
                            </small>
                        </label>
                    </div>
                @endforeach
                
                @if($attribute->attributeValues->count() > 6)
                    <button type="button" class="btn btn-link btn-sm p-0 show-more-{{ strtolower($attribute->name) }}">
                        Show {{ $attribute->attributeValues->count() - 6 }} more
                    </button>
                @endif
            </div>
        </div>
        @endif
    @endforeach
@endif

<!-- Availability Filter -->
<div class="filter-group">
    <h6 class="filter-title mb-3">
        <i class="bi bi-check-circle me-2"></i>Availability
    </h6>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="in-stock-filter" 
               {{ request('in_stock') ? 'checked' : '' }}>
        <label class="form-check-label" for="in-stock-filter">
            In Stock Only
        </label>
    </div>
</div>

<!-- Rating Filter (Future Implementation) -->
<!--
<div class="filter-group">
    <h6 class="filter-title mb-3">
        <i class="bi bi-star me-2"></i>Customer Rating
    </h6>
    <div class="filter-options">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="rating-4-plus">
            <label class="form-check-label d-flex align-items-center" for="rating-4-plus">
                <div class="rating-stars me-2">
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star text-muted"></i>
                </div>
                <span>4 & Up (234)</span>
            </label>
        </div>
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="rating-3-plus">
            <label class="form-check-label d-flex align-items-center" for="rating-3-plus">
                <div class="rating-stars me-2">
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star text-muted"></i>
                    <i class="bi bi-star text-muted"></i>
                </div>
                <span>3 & Up (456)</span>
            </label>
        </div>
    </div>
</div>
-->

<script>
// Ensure jQuery is available for filters
(function() {
    function initFilters() {
        if (typeof jQuery === 'undefined') {
            return;
        }
        
        var $ = jQuery;
        
        $(document).ready(function() {
            // Price range options
            $('.price-range-option').change(function() {
                if ($(this).is(':checked')) {
                    const min = $(this).data('min');
                    const max = $(this).data('max');
            
            $('#price-min').val(min);
            $('#price-max').val(max);
            
            // Trigger the search
            $('#price-min').trigger('input');
        }
    });
    
    // Show more/less functionality
    $('.filter-options').each(function() {
        const $container = $(this);
        const $showMoreBtn = $container.find('[class*="show-more"]');
        
        if ($showMoreBtn.length) {
            $showMoreBtn.click(function() {
                const hiddenItems = $container.find('.form-check:hidden');
                if (hiddenItems.length) {
                    hiddenItems.show();
                    $(this).text('Show less').removeClass('show-more').addClass('show-less');
                } else {
                    $container.find('.form-check').slice(8).hide();
                    $(this).text($(this).text().replace('Show less', 'Show more')).removeClass('show-less').addClass('show-more');
                }
            });
        }
    });
        });
    }
    
    // Initialize when jQuery is available
    if (typeof jQuery !== 'undefined') {
        initFilters();
    } else {
        var checkJQuery = setInterval(function() {
            if (typeof jQuery !== 'undefined') {
                clearInterval(checkJQuery);
                initFilters();
            }
        }, 50);
        
        setTimeout(function() {
            clearInterval(checkJQuery);
        }, 2000);
    }
})();
</script>
