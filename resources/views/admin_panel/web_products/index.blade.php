@extends('admin_panel.layout.app')

@section('content')
<link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendors/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet">
{{-- Toastr for ajax notifications --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<style>
    .page-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 15px;
    }
    .form-switch .form-check-input {
        width: 2.5em;
        height: 1.25em;
        cursor: pointer;
    }
    .quick-input {
        width: 100px;
        padding: 4px 8px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 13px;
    }
    .quick-select {
        width: 130px;
        padding: 4px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 13px;
    }
    .product-img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #ddd;
    }
</style>

<div class="page-container">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Web Products Management</h4>
            <small class="text-muted">Quickly toggle visibility and promotional settings for the website.</small>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-4 rounded">
        <div class="card-body">
            <form action="{{ route('web_products.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Filter by Category</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Website Visibility</label>
                    <select name="visibility" class="form-select form-select-sm">
                        <option value="">All Products</option>
                        <option value="1" {{ request('visibility') == '1' ? 'selected' : '' }}>Visible on Website</option>
                        <option value="0" {{ request('visibility') == '0' ? 'selected' : '' }}>Hidden from Website</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter"></i> Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('web_products.index') }}" class="btn btn-light btn-sm w-100 border"><i class="fas fa-sync"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Products Table --}}
    <div class="card shadow-sm border-0 rounded">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="productsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th class="text-center">Web Status</th>
                            <th class="text-center">Homepage</th>
                            <th>Promo Tag</th>
                            <th>Web Price</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td class="ps-3">
                                @if($product->image)
                                    <img src="{{ asset('uploads/products/'.$product->image) }}" class="product-img">
                                @else
                                    <div class="product-img bg-light d-flex align-items-center justify-content-center text-muted">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <a href="javascript:void(0)" class="fw-bold text-primary text-decoration-none open-web-settings" data-id="{{ $product->id }}">{{ $product->item_name }}</a>
                                <div class="small text-muted">SKU: {{ $product->item_code }}</div>
                            </td>
                            <td>{{ $product->category_relation->name ?? 'N/A' }}</td>
                            
                            {{-- Web Visible --}}
                            <td class="text-center">
                                @if($product->is_web_visible)
                                    <span class="badge bg-success"><i class="fas fa-check-circle"></i> Visible</span>
                                @else
                                    <span class="badge bg-secondary"><i class="fas fa-eye-slash"></i> Hidden</span>
                                @endif
                            </td>

                            {{-- Show on Home --}}
                            <td class="text-center">
                                @if($product->show_on_homepage)
                                    <span class="badge bg-primary">Yes</span>
                                @else
                                    <span class="badge bg-light text-muted">No</span>
                                @endif
                            </td>

                            {{-- Promo Tag --}}
                            <td>
                                @if($product->promo_tag)
                                    <span class="badge bg-info text-dark">{{ $product->promo_tag }}</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>

                            {{-- Web Sale Price --}}
                            <td class="fw-bold">
                                @if($product->web_sale_price)
                                    Rs. {{ number_format($product->web_sale_price, 2) }}
                                @else
                                    <span class="text-muted small">Default (Rs. {{ number_format($product->sale_price_per_piece ?? 0, 2) }})</span>
                                @endif
                            </td>
                            
                            {{-- Action --}}
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-primary open-web-settings" data-id="{{ $product->id }}">
                                    <i class="fas fa-cog"></i> Settings
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $products->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Web Settings Modal --}}
<div class="modal fade" id="webSettingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="webSettingsForm" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="fas fa-globe text-primary me-2"></i> Website Settings: <span id="modalProductName"></span></h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="modal_is_web_visible" name="is_web_visible" value="1">
                                <label class="form-check-label fw-bold" for="modal_is_web_visible">Enable for Website</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="modal_show_on_homepage" name="show_on_homepage" value="1">
                                <label class="form-check-label fw-bold" for="modal_show_on_homepage">Show on Homepage</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="modal_auto_hide" name="auto_hide_out_of_stock" value="1">
                                <label class="form-check-label fw-bold" for="modal_auto_hide">Auto Hide Out of Stock</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Promotional Tag</label>
                            <select class="form-select" name="promo_tag" id="modal_promo_tag">
                                <option value="">None</option>
                                <option value="Featured">Featured</option>
                                <option value="New Arrival">New Arrival</option>
                                <option value="Best Seller">Best Seller</option>
                                <option value="Trending">Trending</option>
                                <option value="Flash Sale">Flash Sale</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Website Sale Price</label>
                            <input type="number" step="0.01" class="form-control" name="web_sale_price" id="modal_web_sale_price" placeholder="Leave empty to use default">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Meta Title (SEO)</label>
                            <input type="text" class="form-control" name="meta_title" id="modal_meta_title" placeholder="SEO Title">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Meta Description (SEO)</label>
                            <input type="text" class="form-control" name="meta_description" id="modal_meta_description" placeholder="SEO Description">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-primary">Main Website Image (Primary)</label>
                            <div id="modalExistingMainImage" class="mb-2"></div>
                            <input type="file" class="form-control" name="web_main_image" accept="image/*">
                            <small class="text-muted">This image will show as the primary thumbnail on the website.</small>
                        </div>
                        
                        <div class="col-md-12 mt-2">
                            <label class="form-label fw-bold small text-info mb-1">Website Gallery Images (Max 4)</label>
                            <div id="modalExistingImages" class="d-flex flex-wrap gap-2 mb-2"></div>
                            
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label class="small text-muted mb-1">Image 1</label>
                                    <input type="file" class="form-control form-control-sm" name="web_images[]" accept="image/*">
                                </div>
                                <div class="col-md-3">
                                    <label class="small text-muted mb-1">Image 2</label>
                                    <input type="file" class="form-control form-control-sm" name="web_images[]" accept="image/*">
                                </div>
                                <div class="col-md-3">
                                    <label class="small text-muted mb-1">Image 3</label>
                                    <input type="file" class="form-control form-control-sm" name="web_images[]" accept="image/*">
                                </div>
                                <div class="col-md-3">
                                    <label class="small text-muted mb-1">Image 4</label>
                                    <input type="file" class="form-control form-control-sm" name="web_images[]" accept="image/*">
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">Upload new images in any of the slots above to replace the existing gallery.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Settings</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('assets/vendors/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    $(document).ready(function() {
        // CSRF Token setup for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        // Datatables setup
        $('#productsTable').DataTable({
            "paging": false,
            "info": false,
            "searching": true,
            "ordering": true,
            "columnDefs": [
                { "orderable": false, "targets": [0, 7] }
            ]
        });

        // Toastr options
        toastr.options = {
            "positionClass": "toast-bottom-right",
            "timeOut": "2000",
        };

        // Open Web Settings Modal
        $('.open-web-settings').on('click', function(e) {
            e.preventDefault();
            let productId = $(this).data('id');
            let url = "{{ url('/web-products') }}/" + productId + "/settings";

            $.get(url, function(response) {
                if(response.status === 'success') {
                    let p = response.product;
                    
                    // Set Form Action
                    $('#webSettingsForm').attr('action', url);
                    
                    $('#modalProductName').text(p.item_name);
                    $('#modal_is_web_visible').prop('checked', p.is_web_visible == 1);
                    $('#modal_show_on_homepage').prop('checked', p.show_on_homepage == 1);
                    $('#modal_auto_hide').prop('checked', p.auto_hide_out_of_stock == 1);
                    $('#modal_promo_tag').val(p.promo_tag);
                    $('#modal_web_sale_price').val(p.web_sale_price);
                    $('#modal_meta_title').val(p.meta_title);
                    $('#modal_meta_description').val(p.meta_description);

                    // Existing Main Image
                    if (p.web_main_image) {
                        let mainImgUrl = "{{ asset('uploads/products') }}/" + p.web_main_image;
                        $('#modalExistingMainImage').html(`<img src="${mainImgUrl}" style="height: 60px; width: 60px; object-fit: cover; border: 2px solid #0d6efd; border-radius: 4px; padding: 2px;">`);
                    } else if (p.image) {
                        let defaultImgUrl = "{{ asset('uploads/products') }}/" + p.image;
                        $('#modalExistingMainImage').html(`<img src="${defaultImgUrl}" style="height: 60px; width: 60px; object-fit: cover; border: 1px solid #ccc; border-radius: 4px;"> <small class="text-muted d-block mt-1">Default POS Image Used</small>`);
                    } else {
                        $('#modalExistingMainImage').html('');
                    }

                    // Existing Gallery Images
                    let imagesHtml = '';
                    if(p.web_images && p.web_images.length > 0) {
                        p.web_images.forEach(function(img) {
                            let imgUrl = "{{ asset('uploads/products') }}/" + img.image_path;
                            imagesHtml += `<img src="${imgUrl}" style="height: 50px; width: 50px; object-fit: cover; border: 1px solid #ccc; border-radius: 4px;">`;
                        });
                    }
                    $('#modalExistingImages').html(imagesHtml);

                    // Show Modal (Bootstrap 4 fallback)
                    $('#webSettingsModal').modal('show');
                }
            });
        });
    });
</script>
@endsection
