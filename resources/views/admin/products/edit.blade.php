@extends('admin.layout')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product')

@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" id="product-form">
            @csrf
            @method('PUT')
            
            <!-- Basic Information -->
            <div class="card admin-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>Basic Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                                {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="brand_id" class="form-label">Brand</label>
                                <select class="form-select @error('brand_id') is-invalid @enderror" 
                                        id="brand_id" name="brand_id">
                                    <option value="">Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" 
                                                {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('brand_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status">
                                    <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Images Management -->
            <div class="card admin-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-images me-2"></i>Product Images
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Current General Images -->
                    @if($product->images->where('product_variation_id', null)->count() > 0)
                        <div class="mb-4">
                            <h6>Current General Images</h6>
                            <div class="row">
                                @foreach($product->images->where('product_variation_id', null) as $image)
                                    <div class="col-md-3 mb-3">
                                        <div class="card">
                                            <img src="{{ Storage::url($image->image_path) }}" class="card-img-top" style="height: 150px; object-fit: cover;">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">{{ $image->is_main ? 'Main Image' : 'Gallery' }}</small>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteImage({{ $image->id }})">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Add New General Images -->
                    <div class="mb-4">
                        <label class="form-label">Add New General Images</label>
                        <input type="file" class="form-control @error('general_images') is-invalid @enderror" 
                               name="general_images[]" id="general_images" multiple accept="image/*" 
                               onchange="showImagePreview(this, 'general-images-preview')">
                        @error('general_images')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Upload multiple images. Max size: 2MB each. Formats: JPG, PNG, WebP</div>
                        <div id="general-images-preview" class="mt-3 row"></div>
                    </div>

                    <!-- Current Variation Images -->
                    @if($product->images->where('product_variation_id', '!=', null)->count() > 0)
                        <div class="mb-4">
                            <h6>Current Variation Images</h6>
                            @foreach($product->variations as $variation)
                                @php
                                    $variationImages = $product->images->where('product_variation_id', $variation->id);
                                @endphp
                                @if($variationImages->count() > 0)
                                    <div class="mb-3">
                                        <strong>{{ $variation->variation_name }}</strong>
                                        <div class="row mt-2">
                                            @foreach($variationImages as $image)
                                                <div class="col-md-3 mb-2">
                                                    <div class="card">
                                                        <img src="{{ Storage::url($image->image_path) }}" class="card-img-top" style="height: 120px; object-fit: cover;">
                                                        <div class="card-body p-2">
                                                            <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="deleteImage({{ $image->id }})">
                                                                <i class="bi bi-trash"></i> Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Current Variations -->
            @if($product->variations->count() > 0)
            <div class="card admin-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-grid me-2"></i>Current Product Variations
                    </h5>
                    <small class="text-muted">{{ $product->variations->count() }} variations found</small>
                </div>
                <div class="card-body">
                    @foreach($product->variations as $index => $variation)
                        <div class="row mb-4 p-3 border rounded">
                            <div class="col-md-12 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $variation->variation_name ?: 'Default' }}</strong>
                                        <br><small class="text-muted">SKU: {{ $variation->sku }}</small>
                                        @if($variation->attributeValues->count() > 0)
                                            <br><small class="badge bg-info">
                                                {{ $variation->attributeValues->pluck('value')->join(' / ') }}
                                            </small>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                            onclick="deleteVariation({{ $variation->id }})" title="Delete Variation">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label small">Price (₹)</label>
                                <input type="number" class="form-control form-control-sm" 
                                       name="variations[{{ $variation->id }}][price]" 
                                       value="{{ old('variations.'.$variation->id.'.price', $variation->price) }}"
                                       step="0.01" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Stock</label>
                                <input type="number" class="form-control form-control-sm" 
                                       name="variations[{{ $variation->id }}][stock]" 
                                       value="{{ old('variations.'.$variation->id.'.stock', $variation->stock) }}"
                                       min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">SKU</label>
                                <input type="text" class="form-control form-control-sm" 
                                       name="variations[{{ $variation->id }}][sku]" 
                                       value="{{ old('variations.'.$variation->id.'.sku', $variation->sku) }}">
                            </div>

                            <!-- Variation Images Upload -->
                            @php
                                $hasColor = $variation->attributeValues->where('attribute.name', 'LIKE', '%color%')->count() > 0;
                            @endphp
                            @if($hasColor)
                                <div class="col-md-12 mt-3">
                                    <label class="form-label small">
                                        <i class="bi bi-images me-1"></i>
                                        Upload Images for {{ $variation->variation_name }}
                                        @php
                                            $colorValue = $variation->attributeValues->where('attribute.name', 'LIKE', '%color%')->first();
                                        @endphp
                                        @if($colorValue)
                                            <span class="badge bg-secondary">{{ $colorValue->value }}</span>
                                        @endif
                                    </label>
                                    <input type="file" class="form-control form-control-sm" 
                                           name="variations[{{ $variation->id }}][images][]" 
                                           multiple accept="image/*"
                                           onchange="showVariationImagePreview(this, 'variation-preview-{{ $variation->id }}')">
                                    <div class="form-text">Upload images specific to this color variation</div>
                                    <div id="variation-preview-{{ $variation->id }}" class="mt-2 row"></div>
                                </div>
                            @endif
                            
                            <input type="hidden" name="variations[{{ $variation->id }}][id]" value="{{ $variation->id }}">
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Add New Variations -->
            <div class="card admin-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-plus-circle me-2"></i>Add New Variations
                    </h5>
                    <small class="text-muted">Select attributes to create new variations</small>
                </div>
                <div class="card-body">
                    <div class="row" id="attributes-container">
                        @foreach($attributes as $attribute)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card border">
                                    <div class="card-body p-3">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input attribute-checkbox" type="checkbox" 
                                                   value="{{ $attribute->id }}" id="attr_{{ $attribute->id }}"
                                                   name="new_attributes[]">
                                            <label class="form-check-label fw-bold" for="attr_{{ $attribute->id }}">
                                                {{ $attribute->name }}
                                            </label>
                                        </div>
                                        
                                        <div class="attribute-values" id="values_{{ $attribute->id }}" style="display: none">
                                            <small class="text-muted d-block mb-2">Select values:</small>
                                            @foreach($attribute->attributeValues as $value)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input attribute-value-checkbox" 
                                                           type="checkbox" 
                                                           id="value_{{ $value->id }}" 
                                                           name="new_attribute_values[{{ $attribute->id }}][]" 
                                                           value="{{ $value->id }}">
                                                    <label class="form-check-label small" for="value_{{ $value->id }}">
                                                        {{ $value->value }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-outline-primary" id="generate-variations-btn" disabled>
                            <i class="bi bi-gear me-2"></i>Generate New Variations
                        </button>
                        
                        <!-- Selection Summary -->
                        <div id="selection-summary" class="mt-3 p-3 border rounded bg-info-subtle" style="display: none;">
                            <h6>📝 Your Selection Summary:</h6>
                            <div id="summary-content"></div>
                            <div class="mt-2">
                                <strong>This will create <span id="combo-count">0</span> new variations</strong>
                            </div>
                        </div>
                    </div>
                    
                    <!-- New variations preview -->
                    <div id="new-variations-preview" style="display: none;" class="mt-4">
                        <h6>New Variations Preview:</h6>
                        <div id="new-variations-list"></div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card admin-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Products
                        </a>
                        
                        <div>
                            <button type="button" class="btn btn-outline-danger me-2" 
                                    onclick="deleteProduct({{ $product->id }})">
                                <i class="bi bi-trash me-2"></i>Delete Product
                            </button>
                            <button type="submit" class="btn btn-gradient">
                                <i class="bi bi-check-circle me-2"></i>Update Product
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle attribute checkbox changes for new variations
    $('.attribute-checkbox').change(function() {
        const attributeId = $(this).val();
        const valuesDiv = $('#values_' + attributeId);
        
        if ($(this).is(':checked')) {
            valuesDiv.show();
        } else {
            valuesDiv.hide();
            valuesDiv.find('.attribute-value-checkbox').prop('checked', false);
        }
        
        toggleGenerateButton();
        updateSelectionSummary();
    });
    
    // Handle attribute value checkbox changes
    $('.attribute-value-checkbox').change(function() {
        toggleGenerateButton();
        updateSelectionSummary();
    });
    
    // Generate new variations
    $('#generate-variations-btn').click(function() {
        generateNewVariations();
    });
});

// Image preview functions
function showImagePreview(input, previewContainerId) {
    const previewContainer = document.getElementById(previewContainerId);
    previewContainer.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'col-md-3 mb-2';
                previewDiv.innerHTML = `
                    <div class="card">
                        <img src="${e.target.result}" class="card-img-top" style="height: 120px; object-fit: cover;">
                        <div class="card-body p-2">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removeImagePreview(this)">
                                <i class="bi bi-x"></i> Remove
                            </button>
                        </div>
                    </div>
                `;
                previewContainer.appendChild(previewDiv);
            };
            reader.readAsDataURL(file);
        });
    }
}

function showVariationImagePreview(input, previewContainerId) {
    const previewContainer = document.getElementById(previewContainerId);
    previewContainer.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'col-md-3 mb-2';
                previewDiv.innerHTML = `
                    <div class="card">
                        <img src="${e.target.result}" class="card-img-top" style="height: 100px; object-fit: cover;">
                        <div class="card-body p-1">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removeImagePreview(this)">
                                <i class="bi bi-x"></i> Remove
                            </button>
                        </div>
                    </div>
                `;
                previewContainer.appendChild(previewDiv);
            };
            reader.readAsDataURL(file);
        });
    }
}

function removeImagePreview(button) {
    button.closest('.col-md-3').remove();
}

function deleteImage(imageId) {
    if (confirm('Are you sure you want to delete this image?')) {
        $.ajax({
            url: `/admin/products/images/${imageId}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                showToast(response.message || 'Image deleted successfully', 'success');
                location.reload();
            },
            error: function() {
                showToast('Failed to delete image', 'error');
            }
        });
    }
}

function toggleGenerateButton() {
    const hasCheckedAttributes = $('.attribute-checkbox:checked').length > 0;
    const hasCheckedValues = $('.attribute-value-checkbox:checked').length > 0;
    
    $('#generate-variations-btn').prop('disabled', !(hasCheckedAttributes && hasCheckedValues));
}

function updateSelectionSummary() {
    const selectedAttributes = [];
    let totalCombinations = 1;
    
    $('.attribute-checkbox:checked').each(function() {
        const attributeId = $(this).val();
        const attributeName = $(this).next('label').text();
        const selectedValues = [];
        
        $(`input[name="new_attribute_values[${attributeId}][]"]:checked`).each(function() {
            selectedValues.push($(this).next('label').text());
        });
        
        if (selectedValues.length > 0) {
            selectedAttributes.push({
                name: attributeName,
                values: selectedValues
            });
            totalCombinations *= selectedValues.length;
        }
    });
    
    if (selectedAttributes.length === 0) {
        $('#selection-summary').hide();
        return;
    }
    
    let summaryHtml = '';
    selectedAttributes.forEach(attr => {
        summaryHtml += `<div><strong>${attr.name}:</strong> ${attr.values.join(', ')}</div>`;
    });
    
    $('#summary-content').html(summaryHtml);
    $('#combo-count').text(totalCombinations);
    
    // Show warning if too many combinations
    if (totalCombinations > 10) {
        $('#selection-summary').removeClass('bg-info-subtle').addClass('bg-warning-subtle');
        $('#combo-count').parent().html(`<strong class="text-warning">⚠️ This will create ${totalCombinations} new variations - Are you sure?</strong>`);
    } else {
        $('#selection-summary').removeClass('bg-warning-subtle').addClass('bg-info-subtle');
        $('#combo-count').parent().html(`<strong>This will create <span id="combo-count">${totalCombinations}</span> new variations</strong>`);
    }
    
    $('#selection-summary').show();
}

function generateNewVariations() {
    const selectedAttributes = [];
    
    $('.attribute-checkbox:checked').each(function() {
        const attributeId = $(this).val();
        const attributeName = $(this).next('label').text();
        const selectedValues = [];
        
        $(`input[name="new_attribute_values[${attributeId}][]"]:checked`).each(function() {
            selectedValues.push({
                id: $(this).val(),
                value: $(this).next('label').text()
            });
        });
        
        if (selectedValues.length > 0) {
            selectedAttributes.push({
                id: attributeId,
                name: attributeName,
                values: selectedValues
            });
        }
    });
    
    if (selectedAttributes.length === 0) {
        showToast('Please select attributes and values', 'warning');
        return;
    }
    
    // Generate combinations
    const combinations = generateCombinations(selectedAttributes);
    
    // Warning for large number of combinations
    if (combinations.length > 10) {
        if (!confirm(`This will create ${combinations.length} new variations. Are you sure you want to continue?`)) {
            return;
        }
    }
    
    let html = '';
    combinations.forEach((combo, index) => {
        const variationName = combo.map(item => item.value).join(' / ');
        const hasColor = combo.some(item => item.attribute_name.toLowerCase().includes('color'));
        const colorValue = combo.find(item => item.attribute_name.toLowerCase().includes('color'));
        
        html += `
            <div class="row mb-4 p-3 border rounded bg-light">
                <div class="col-md-12 mb-3">
                    <strong>${variationName}</strong>
                    <br><small class="text-muted">New Variation ${index + 1}</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Price (₹)</label>
                    <input type="number" class="form-control form-control-sm" 
                           name="new_variations[${index}][price]" step="0.01" min="0" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Stock</label>
                    <input type="number" class="form-control form-control-sm" 
                           name="new_variations[${index}][stock]" min="0" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">SKU</label>
                    <input type="text" class="form-control form-control-sm" 
                           name="new_variations[${index}][sku]" placeholder="Auto">
                </div>`;
        
        // Add image upload for color variations
        if (hasColor && colorValue) {
            html += `
                <div class="col-md-12 mt-3">
                    <label class="form-label small">
                        <i class="bi bi-images me-1"></i>
                        Upload Images for ${colorValue.value} Color
                    </label>
                    <input type="file" class="form-control form-control-sm" 
                           name="new_variations[${index}][images][]" 
                           multiple accept="image/*"
                           onchange="showVariationImagePreview(this, 'new-variation-preview-${index}')">
                    <div class="form-text">Upload images specific to this color variation</div>
                    <div id="new-variation-preview-${index}" class="mt-2 row"></div>
                </div>`;
        }
        
        html += `
                ${combo.map((item, i) => `
                    <input type="hidden" name="new_variations[${index}][attributes][${i}][attribute_id]" value="${item.attribute_id}">
                    <input type="hidden" name="new_variations[${index}][attributes][${i}][attribute_value_id]" value="${item.id}">
                `).join('')}
            </div>
        `;
    });
    
    $('#new-variations-list').html(html);
    $('#new-variations-preview').show();
}

function generateCombinations(attributes) {
    if (attributes.length === 0) return [];
    
    let combinations = [[]];
    
    attributes.forEach(attribute => {
        const newCombinations = [];
        combinations.forEach(combo => {
            attribute.values.forEach(value => {
                newCombinations.push([...combo, {
                    ...value,
                    attribute_id: attribute.id,
                    attribute_name: attribute.name
                }]);
            });
        });
        combinations = newCombinations;
    });
    
    return combinations;
}

function deleteVariation(variationId) {
    if (confirm('Are you sure you want to delete this variation?')) {
        $.ajax({
            url: `/admin/products/variations/${variationId}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                showToast(response.message, 'success');
                location.reload();
            },
            error: function() {
                showToast('Failed to delete variation', 'error');
            }
        });
    }
}

function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this entire product? This action cannot be undone.')) {
        $.ajax({
            url: `/admin/products/${productId}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                showToast(response.message, 'success');
                window.location.href = '{{ route("admin.products.index") }}';
            },
            error: function() {
                showToast('Failed to delete product', 'error');
            }
        });
    }
}
</script>
@endpush
