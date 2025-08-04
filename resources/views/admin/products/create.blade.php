@extends('admin.layout')

@section('title', 'Add New Product')
@section('page-title', 'Add New Product')

@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="product-form">
            @csrf
            
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
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="4">{{ old('description') }}</textarea>
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
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                                {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
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
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- General Product Images -->
            <div class="card admin-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-images me-2"></i>General Product Images
                    </h5>
                    <small class="text-muted">These images will be shown when no specific variation is selected</small>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="general_images" class="form-label">Upload Images</label>
                        <input type="file" class="form-control @error('general_images') is-invalid @enderror" 
                               id="general_images" name="general_images[]" accept="image/*" multiple>
                        @error('general_images')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Max size: 2MB per image. Formats: JPG, PNG, WebP. You can select multiple images.</div>
                    </div>
                    
                    <!-- Image Preview -->
                    <div id="general-images-preview" class="row g-3 mt-2" style="display: none;">
                        <!-- Previews will be added here -->
                    </div>
                </div>
            </div>

            <!-- Attributes Selection -->
            <div class="card admin-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-tags me-2"></i>Product Attributes
                    </h5>
                    <small class="text-muted">Select attributes that will create product variations</small>
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
                                                   name="attributes[]" 
                                                   {{ in_array($attribute->id, old('attributes', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="attr_{{ $attribute->id }}">
                                                {{ $attribute->name }}
                                            </label>
                                        </div>
                                        
                                        <div class="attribute-values" id="values_{{ $attribute->id }}" 
                                             style="display: {{ in_array($attribute->id, old('attributes', [])) ? 'block' : 'none' }}">
                                            <small class="text-muted d-block mb-2">Select values:</small>
                                            @foreach($attribute->attributeValues as $value)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input attribute-value-checkbox" 
                                                           type="checkbox" 
                                                           id="value_{{ $value->id }}" 
                                                           name="attribute_values[{{ $attribute->id }}][]" 
                                                           value="{{ $value->id }}"
                                                           {{ in_array($value->id, old("attribute_values.{$attribute->id}", [])) ? 'checked' : '' }}>
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
                    
                    @if($attributes->isEmpty())
                        <div class="text-center py-4">
                            <i class="bi bi-tags display-4 text-muted"></i>
                            <p class="text-muted mt-2">No attributes found. <a href="#" class="text-decoration-none">Create attributes first</a></p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Variations Preview -->
            <div class="card admin-card mb-4" id="variations-preview" style="display: none;">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-grid me-2"></i>Product Variations Preview
                    </h5>
                    <small class="text-muted">This will create <span id="variations-count">0</span> product variations</small>
                </div>
                <div class="card-body">
                    <div id="variations-list"></div>
                </div>
            </div>

            <!-- Default Pricing (if no attributes selected) -->
            <div class="card admin-card mb-4" id="default-pricing">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-currency-rupee me-2"></i>Pricing & Inventory
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="default_price" class="form-label">Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control @error('default_price') is-invalid @enderror" 
                                           id="default_price" name="default_price" value="{{ old('default_price') }}" 
                                           step="0.01" min="0" required>
                                </div>
                                @error('default_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="default_stock" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('default_stock') is-invalid @enderror" 
                                       id="default_stock" name="default_stock" value="{{ old('default_stock') }}" 
                                       min="0" required>
                                @error('default_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="default_sku" class="form-label">SKU</label>
                                <input type="text" class="form-control @error('default_sku') is-invalid @enderror" 
                                       id="default_sku" name="default_sku" value="{{ old('default_sku') }}" 
                                       placeholder="Auto-generated if empty">
                                @error('default_sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
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
                            <button type="button" class="btn btn-outline-primary me-2" id="preview-btn">
                                <i class="bi bi-eye me-2"></i>Preview Variations
                            </button>
                            <button type="submit" class="btn btn-gradient">
                                <i class="bi bi-check-circle me-2"></i>Create Product
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
    // Handle attribute checkbox changes
    $('.attribute-checkbox').change(function() {
        const attributeId = $(this).val();
        const valuesDiv = $('#values_' + attributeId);
        
        if ($(this).is(':checked')) {
            valuesDiv.show();
        } else {
            valuesDiv.hide();
            // Uncheck all values for this attribute
            valuesDiv.find('.attribute-value-checkbox').prop('checked', false);
        }
        
        updateVariationsPreview();
        togglePricingSection();
    });
    
    // Handle attribute value checkbox changes
    $('.attribute-value-checkbox').change(function() {
        updateVariationsPreview();
    });
    
    // Preview button
    $('#preview-btn').click(function() {
        updateVariationsPreview();
        if ($('#variations-preview').is(':visible')) {
            $('html, body').animate({
                scrollTop: $('#variations-preview').offset().top - 100
            }, 500);
        }
    });
    
    // Form validation
    $('#product-form').submit(function(e) {
        const hasAttributes = $('.attribute-checkbox:checked').length > 0;
        
        if (hasAttributes) {
            // Check if at least one value is selected for each checked attribute
            let valid = true;
            $('.attribute-checkbox:checked').each(function() {
                const attributeId = $(this).val();
                const checkedValues = $(`input[name="attribute_values[${attributeId}][]"]:checked`).length;
                if (checkedValues === 0) {
                    valid = false;
                    showToast(`Please select at least one value for ${$(this).next('label').text()}`, 'warning');
                    return false;
                }
            });
            
            if (!valid) {
                e.preventDefault();
                return false;
            }
        } else {
            // Validate default pricing fields
            if (!$('#default_price').val() || !$('#default_stock').val()) {
                e.preventDefault();
                showToast('Please fill in price and stock for the product', 'warning');
                return false;
            }
        }
    });
    
    // Initialize
    togglePricingSection();
});

function updateVariationsPreview() {
    const selectedAttributes = [];
    
    $('.attribute-checkbox:checked').each(function() {
        const attributeId = $(this).val();
        const attributeName = $(this).next('label').text();
        const selectedValues = [];
        
        $(`input[name="attribute_values[${attributeId}][]"]:checked`).each(function() {
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
        $('#variations-preview').hide();
        return;
    }
    
    // Generate combinations
    const combinations = generateCombinations(selectedAttributes);
    
    $('#variations-count').text(combinations.length);
    
    let html = '';
    combinations.forEach((combo, index) => {
        const variationName = combo.map(item => item.value).join(' / ');
        
        // Check if this variation has a color attribute
        const colorAttribute = combo.find(item => item.attribute_name && item.attribute_name.toLowerCase() === 'color');
        const showImageUpload = colorAttribute || combo.length === 1; // Show images for color variations or single attribute variations
        
        html += `
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <strong>${variationName}</strong>
                        <small class="text-muted ms-2">Variation ${index + 1}</small>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label small">Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm" 
                                   name="variations[${index}][price]" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Stock <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm" 
                                   name="variations[${index}][stock]" min="0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">SKU</label>
                            <input type="text" class="form-control form-control-sm" 
                                   name="variations[${index}][sku]" placeholder="Auto-generated">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Weight (grams)</label>
                            <input type="number" class="form-control form-control-sm" 
                                   name="variations[${index}][weight]" min="0" step="0.1">
                        </div>
                    </div>
                    
                    ${showImageUpload ? `
                    <div class="row mt-3">
                        <div class="col-12">
                            <label class="form-label small">
                                <i class="bi bi-images me-1"></i>Variation Images
                                ${colorAttribute ? `<span class="badge bg-info ms-1">${colorAttribute.value}</span>` : ''}
                            </label>
                            <input type="file" class="form-control form-control-sm variation-images" 
                                   name="variations[${index}][images][]" accept="image/*" multiple
                                   data-variation-index="${index}">
                            <div class="form-text small">Upload images specific to this variation. Max 2MB per image.</div>
                            
                            <!-- Image Preview Container -->
                            <div class="variation-images-preview mt-2" id="preview-${index}" style="display: none;">
                                <div class="row g-2"></div>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    
                    <!-- Hidden fields for attribute mapping -->
                    ${combo.map((item, i) => `
                        <input type="hidden" name="variations[${index}][attributes][${i}][attribute_id]" value="${item.attribute_id}">
                        <input type="hidden" name="variations[${index}][attributes][${i}][attribute_value_id]" value="${item.id}">
                    `).join('')}
                </div>
            </div>
        `;
    });
    
    $('#variations-list').html(html);
    $('#variations-preview').show();
    
    // Bind image preview events
    bindImagePreviewEvents();
}

function generateCombinations(attributes) {
    if (attributes.length === 0) return [];
    if (attributes.length === 1) {
        return attributes[0].values.map(value => [{
            id: value.id,
            value: value.value,
            attribute_id: attributes[0].id,
            attribute_name: attributes[0].name
        }]);
    }
    
    const result = [];
    const first = attributes[0];
    const rest = attributes.slice(1);
    const restCombinations = generateCombinations(rest);
    
    first.values.forEach(value => {
        restCombinations.forEach(combination => {
            result.push([{
                id: value.id,
                value: value.value,
                attribute_id: first.id,
                attribute_name: first.name
            }].concat(combination));
        });
    });
    
    return result;
}

function bindImagePreviewEvents() {
    // General images preview
    $('#general_images').on('change', function() {
        previewImages(this, '#general-images-preview');
    });
    
    // Variation images preview
    $('.variation-images').on('change', function() {
        const variationIndex = $(this).data('variation-index');
        previewImages(this, `#preview-${variationIndex}`);
    });
}

function previewImages(input, containerSelector) {
    const container = $(containerSelector);
    const row = container.find('.row');
    
    if (input.files && input.files.length > 0) {
        row.empty();
        
        Array.from(input.files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imageHtml = `
                        <div class="col-3 col-md-2">
                            <div class="position-relative">
                                <img src="${e.target.result}" class="img-fluid rounded border" 
                                     style="height: 80px; width: 100%; object-fit: cover;">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 remove-image-preview" 
                                        style="padding: 2px 6px; font-size: 10px;" data-index="${index}">×</button>
                            </div>
                        </div>
                    `;
                    row.append(imageHtml);
                };
                reader.readAsDataURL(file);
            }
        });
        
        container.show();
    } else {
        container.hide();
    }
}

function togglePricingSection() {
    const hasAttributes = $('.attribute-checkbox:checked').length > 0;
    
    if (hasAttributes) {
        $('#default-pricing').hide();
        $('#default_price, #default_stock, #default_sku').prop('required', false);
    } else {
        $('#default-pricing').show();
        $('#default_price, #default_stock').prop('required', true);
        $('#variations-preview').hide();
    }
}

// Initialize image preview on page load
$(document).ready(function() {
    bindImagePreviewEvents();
    
    // Remove image preview functionality
    $(document).on('click', '.remove-image-preview', function() {
        $(this).closest('.col-3, .col-md-2').remove();
    });
});

</script>
@endpush

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

function togglePricingSection() {
    const hasAttributes = $('.attribute-checkbox:checked').length > 0;
    
    if (hasAttributes) {
        $('#default-pricing').hide();
        $('#default_price, #default_stock').removeAttr('required');
    } else {
        $('#default-pricing').show();
        $('#default_price, #default_stock').attr('required', 'required');
        $('#variations-preview').hide();
    }
}
</script>
@endpush
