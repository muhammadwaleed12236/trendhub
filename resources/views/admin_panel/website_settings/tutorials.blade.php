@extends('admin_panel.layout.app')

@section('content')
<div class="main-content-inner">
    <div class="container-fluid py-4">

        <!-- Top Header & Banner -->
        <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 14px;">
            <div class="card-body p-4 text-white">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary px-3 py-1.5" style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; font-weight: 700;">
                                <i class="fas fa-graduation-cap me-1"></i> Client Training & Video Center
                            </span>
                            <span class="badge bg-success px-2.5 py-1" style="font-size: 11px;">Official Guide v2.0</span>
                        </div>
                        <h2 class="h3 font-weight-bold mb-1 text-white">Website Operations & Management Training</h2>
                        <p class="text-white-50 mb-0" style="font-size: 14px;">
                            Complete step-by-step video tutorials and visual documentation on how to manage products, coupons, orders and storefront branding.
                        </p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button onclick="window.print()" class="btn btn-outline-light btn-sm px-3 shadow-none">
                            <i class="fas fa-print me-1"></i> Print Client SOP
                        </button>
                        <a href="{{ env('FRONTEND_URL') ?: (\App\Models\Setting::get('website_link') ?: 'https://trendhubstore.pk') }}" target="_blank" class="btn btn-primary btn-sm px-3 shadow-sm font-weight-bold">
                            <i class="fas fa-external-link-alt me-1"></i> Open Storefront
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-radius: 10px;">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Quick Topic Navigation Pills -->
        <ul class="nav nav-pills mb-4 nav-fill p-1 bg-white rounded shadow-sm border" id="tutorialTabs" role="tablist" style="border-radius: 12px !important;">
            <li class="nav-item">
                <a class="nav-link active py-3 font-weight-bold text-dark d-flex align-items-center justify-content-center gap-2" id="tab-products-btn" data-toggle="pill" href="#tab-products" role="tab">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background: #e0f2fe; color: #0284c7;">
                        <i class="fas fa-box"></i>
                    </div>
                    <span>1. Products & Variants (Sizes)</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 font-weight-bold text-dark d-flex align-items-center justify-content-center gap-2" id="tab-coupons-btn" data-toggle="pill" href="#tab-coupons" role="tab">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background: #dcfce7; color: #16a34a;">
                        <i class="fas fa-tags"></i>
                    </div>
                    <span>2. Discount Coupons</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 font-weight-bold text-dark d-flex align-items-center justify-content-center gap-2" id="tab-orders-btn" data-toggle="pill" href="#tab-orders" role="tab">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background: #f3e8ff; color: #9333ea;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <span>3. Web Orders & Stock Cut</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 font-weight-bold text-dark d-flex align-items-center justify-content-center gap-2" id="tab-branding-btn" data-toggle="pill" href="#tab-branding" role="tab">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background: #fef3c7; color: #d97706;">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                    <span>4. Logo, Banners & WhatsApp</span>
                </a>
            </li>
        </ul>

        <!-- Tab Contents -->
        <div class="tab-content" id="tutorialTabsContent">

            <!-- ========================================== -->
            <!-- TAB 1: PRODUCTS & VARIANTS (SIZES/COLORS) -->
            <!-- ========================================== -->
            <div class="tab-pane fade show active" id="tab-products" role="tabpanel">
                <div class="row">
                    <!-- Left: Video Player Card -->
                    <div class="col-lg-7 mb-4">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                                <h5 class="mb-0 font-weight-bold text-dark">
                                    <i class="fas fa-video text-primary me-2"></i> Video: Product Uploading & Size-Wise Setup
                                </h5>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#modalVideo-products">
                                    <i class="fas fa-edit me-1"></i> Set Video URL
                                </button>
                            </div>
                            <div class="card-body p-0 bg-dark d-flex align-items-center justify-content-center" style="min-height: 380px;">
                                @php $vidProducts = $tutorials['products']['video_url']; @endphp
                                @if(!empty($vidProducts))
                                    @if(strpos($vidProducts, 'youtube.com') !== false || strpos($vidProducts, 'youtu.be') !== false)
                                        @php
                                            $ytId = '';
                                            if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $vidProducts, $matches)) {
                                                $ytId = $matches[1];
                                            }
                                        @endphp
                                        <iframe class="w-100" style="height: 420px; border: 0;" src="https://www.youtube.com/embed/{{ $ytId }}" allowfullscreen></iframe>
                                    @elseif(strpos($vidProducts, 'loom.com') !== false)
                                        <iframe class="w-100" style="height: 420px; border: 0;" src="{{ str_replace('share', 'embed', $vidProducts) }}" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                                    @else
                                        <video controls class="w-100" style="max-height: 420px;">
                                            <source src="{{ $vidProducts }}" type="video/mp4">
                                            Your browser does not support HTML5 video.
                                        </video>
                                    @endif
                                @else
                                    <div class="p-5 text-center text-white">
                                        <div class="mb-3">
                                            <i class="fas fa-play-circle text-primary" style="font-size: 64px; opacity: 0.85;"></i>
                                        </div>
                                        <h5 class="text-white mb-2">Video Tutorial Coming Soon</h5>
                                        <p class="text-white-50 max-w-md mx-auto mb-3" style="font-size: 13px;">
                                            Aap apni custom screen recording (YouTube, Loom, ya MP4 video link) yahan embed kar sakte hain taake client video dekh sake.
                                        </p>
                                        <button type="button" class="btn btn-primary btn-sm px-3" data-toggle="modal" data-target="#modalVideo-products">
                                            <i class="fas fa-link me-1"></i> Attach YouTube/Video Link
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer bg-light py-2.5 px-4 text-muted" style="font-size: 12px;">
                                <i class="fas fa-info-circle me-1 text-primary"></i> <strong>Pro Tip:</strong> Har product ka Kam az Kam 1 Main Image zaroor lagayein taake storefront par high-resolution preview aaye.
                            </div>
                        </div>
                    </div>

                    <!-- Right: Step-by-Step Guide Card -->
                    <div class="col-lg-5 mb-4">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 font-weight-bold text-dark">
                                    <i class="fas fa-list-ol text-primary me-2"></i> Step-by-Step Procedure
                                </h5>
                                <a href="{{ route('web_products.index') }}" class="btn btn-sm btn-outline-primary font-weight-bold">
                                    Go to Web Products &rarr;
                                </a>
                            </div>
                            <div class="card-body p-4" style="max-height: 520px; overflow-y: auto;">
                                <div class="timeline-step mb-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="badge rounded-circle bg-primary text-white p-2" style="width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700;">1</div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">Product Create Karein (POS / Catalog)</h6>
                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                Sub se pehle <strong>Management ➔ Products</strong> mein ja kar naya product banayein ya existing product edit karein.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-step mb-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="badge rounded-circle bg-primary text-white p-2" style="width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700;">2</div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">Size & Color Variants Daalein</h6>
                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                Product form mein <strong>Size Mode: By Pieces</strong> select karein. Wahan Size (S, M, L, XL, XXL) aur Color (Black, Navy, Olive) aur unka initial stock enter karein.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-step mb-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="badge rounded-circle bg-primary text-white p-2" style="width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700;">3</div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">"Show on Website" Toggle ON Karein</h6>
                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                <strong>Website Control ➔ Web Products</strong> par jayein. Product ke samne <strong>"Live Toggle"</strong> ko green (Active) karein taake product website par visible ho jaye.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-step mb-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="badge rounded-circle bg-primary text-white p-2" style="width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700;">4</div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">Main Image & Gallery Upload Karein</h6>
                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                Web Products mein product ke <strong>"Settings" (Gear icon)</strong> par click karein:
                                                <ul class="mt-1 ps-3 text-muted mb-0" style="font-size: 12px;">
                                                    <li><strong>Web Main Image:</strong> Front cover picture jo catalog par dikhegi.</li>
                                                    <li><strong>Gallery Images:</strong> 2 se 4 extra angle photos jo customer product page par zoom kar ke dekhega.</li>
                                                    <li><strong>Web Sale Price (Optional):</strong> Discounted website rate (e.g. Regular 3,500 ➔ Web Price 2,990).</li>
                                                </ul>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-step">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="badge rounded-circle bg-success text-white p-2" style="width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700;">5</div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">Save Karein & Website Refresh Karein</h6>
                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                Save Settings dabayein. Product foran website par apnay sizes, colors aur images ke sath live ho jayega!
                                            </p>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 2: DISCOUNT COUPONS & OFFERS -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="tab-coupons" role="tabpanel">
                <div class="row">
                    <!-- Left: Video Player Card -->
                    <div class="col-lg-7 mb-4">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                                <h5 class="mb-0 font-weight-bold text-dark">
                                    <i class="fas fa-video text-success me-2"></i> Video: Creating & Applying Discount Coupons
                                </h5>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#modalVideo-coupons">
                                    <i class="fas fa-edit me-1"></i> Set Video URL
                                </button>
                            </div>
                            <div class="card-body p-0 bg-dark d-flex align-items-center justify-content-center" style="min-height: 380px;">
                                @php $vidCoupons = $tutorials['coupons']['video_url']; @endphp
                                @if(!empty($vidCoupons))
                                    @if(strpos($vidCoupons, 'youtube.com') !== false || strpos($vidCoupons, 'youtu.be') !== false)
                                        @php
                                            $ytId = '';
                                            if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $vidCoupons, $matches)) {
                                                $ytId = $matches[1];
                                            }
                                        @endphp
                                        <iframe class="w-100" style="height: 420px; border: 0;" src="https://www.youtube.com/embed/{{ $ytId }}" allowfullscreen></iframe>
                                    @elseif(strpos($vidCoupons, 'loom.com') !== false)
                                        <iframe class="w-100" style="height: 420px; border: 0;" src="{{ str_replace('share', 'embed', $vidCoupons) }}" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                                    @else
                                        <video controls class="w-100" style="max-height: 420px;">
                                            <source src="{{ $vidCoupons }}" type="video/mp4">
                                        </video>
                                    @endif
                                @else
                                    <div class="p-5 text-center text-white">
                                        <div class="mb-3">
                                            <i class="fas fa-play-circle text-success" style="font-size: 64px; opacity: 0.85;"></i>
                                        </div>
                                        <h5 class="text-white mb-2">Video Tutorial Coming Soon</h5>
                                        <p class="text-white-50 max-w-md mx-auto mb-3" style="font-size: 13px;">
                                            Coupon create karne aur checkout par apply karne ki video recording attach karein.
                                        </p>
                                        <button type="button" class="btn btn-success btn-sm px-3" data-toggle="modal" data-target="#modalVideo-coupons">
                                            <i class="fas fa-link me-1"></i> Attach YouTube/Video Link
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer bg-light py-2.5 px-4 text-muted" style="font-size: 12px;">
                                <i class="fas fa-info-circle me-1 text-success"></i> <strong>Live Tested:</strong> Website checkout par coupon apply hote hi foran discount minus karta hai aur recalculated total show karta hai.
                            </div>
                        </div>
                    </div>

                    <!-- Right: Step-by-Step Guide Card -->
                    <div class="col-lg-5 mb-4">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 font-weight-bold text-dark">
                                    <i class="fas fa-list-ol text-success me-2"></i> How Coupons Work
                                </h5>
                                <a href="{{ route('admin.coupons.index') }}" class="btn btn-sm btn-outline-success font-weight-bold">
                                    Manage Coupons &rarr;
                                </a>
                            </div>
                            <div class="card-body p-4" style="max-height: 520px; overflow-y: auto;">
                                <div class="timeline-step mb-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="badge rounded-circle bg-success text-white p-2" style="width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700;">1</div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">Website Control ➔ Coupons Kholein</h6>
                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                Admin menu mein <strong>Website Control ➔ Coupons</strong> par jayein aur <strong>"Add New Coupon"</strong> button dabayein.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-step mb-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="badge rounded-circle bg-success text-white p-2" style="width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700;">2</div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">Coupon Code & Type Choose Karein</h6>
                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                <strong>Code:</strong> E.g. <code>LAUNCH10</code> ya <code>SUMMER500</code><br>
                                                <strong>Type:</strong> 
                                                <ul class="mb-0 ps-3 mt-1" style="font-size: 12px;">
                                                    <li><strong>Percentage:</strong> E.g. Value <code>10</code> daalne se 10% discount milega.</li>
                                                    <li><strong>Fixed / Flat PKR:</strong> E.g. Value <code>500</code> daalne se Flat Rs. 500 off hoga.</li>
                                                </ul>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-step mb-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="badge rounded-circle bg-success text-white p-2" style="width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700;">3</div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">Rules & Expiry Date Set Karein</h6>
                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                - <strong>Minimum Spend:</strong> (E.g. Kam az kam Rs. 3,000 ka cart ho tabhi coupon lage).<br>
                                                - <strong>Usage Limit:</strong> Kitne customer use kar sakein (e.g. First 100 customers).<br>
                                                - <strong>Expiry Date:</strong> Coupon kab expire hoga.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-step mb-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="badge rounded-circle bg-success text-white p-2" style="width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700;">4</div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">Customer Checkout par Kaise Use Karega?</h6>
                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                Customer cart mein items add kar ke Checkout page par aayega, wahan <strong>"Have a Coupon Code?"</strong> box mein code type kar ke <strong>Apply</strong> dabayega. Foran bill mein se discount minus ho jayega!
                                            </p>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 3: WEB ORDERS & STOCK AUTO-DEDUCTION -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="tab-orders" role="tabpanel">
                <div class="row">
                    <!-- Left: Video Player Card -->
                    <div class="col-lg-7 mb-4">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                                <h5 class="mb-0 font-weight-bold text-dark">
                                    <i class="fas fa-video text-purple me-2" style="color: #8b5cf6;"></i> Video: Web Orders & Auto Stock Cut
                                </h5>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#modalVideo-orders">
                                    <i class="fas fa-edit me-1"></i> Set Video URL
                                </button>
                            </div>
                            <div class="card-body p-0 bg-dark d-flex align-items-center justify-content-center" style="min-height: 380px;">
                                @php $vidOrders = $tutorials['orders']['video_url']; @endphp
                                @if(!empty($vidOrders))
                                    @if(strpos($vidOrders, 'youtube.com') !== false || strpos($vidOrders, 'youtu.be') !== false)
                                        @php
                                            $ytId = '';
                                            if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $vidOrders, $matches)) {
                                                $ytId = $matches[1];
                                            }
                                        @endphp
                                        <iframe class="w-100" style="height: 420px; border: 0;" src="https://www.youtube.com/embed/{{ $ytId }}" allowfullscreen></iframe>
                                    @elseif(strpos($vidOrders, 'loom.com') !== false)
                                        <iframe class="w-100" style="height: 420px; border: 0;" src="{{ str_replace('share', 'embed', $vidOrders) }}" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                                    @else
                                        <video controls class="w-100" style="max-height: 420px;">
                                            <source src="{{ $vidOrders }}" type="video/mp4">
                                        </video>
                                    @endif
                                @else
                                    <div class="p-5 text-center text-white">
                                        <div class="mb-3">
                                            <i class="fas fa-play-circle" style="font-size: 64px; color: #8b5cf6; opacity: 0.85;"></i>
                                        </div>
                                        <h5 class="text-white mb-2">Video Tutorial Coming Soon</h5>
                                        <p class="text-white-50 max-w-md mx-auto mb-3" style="font-size: 13px;">
                                            Order confirmation aur Warehouse stock auto-deduction ki video recording yahan attach karein.
                                        </p>
                                        <button type="button" class="btn btn-sm px-3 text-white" style="background: #8b5cf6;" data-toggle="modal" data-target="#modalVideo-orders">
                                            <i class="fas fa-link me-1"></i> Attach YouTube/Video Link
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer bg-light py-2.5 px-4 text-muted" style="font-size: 12px;">
                                <i class="fas fa-shield-alt me-1 text-primary"></i> <strong>Safe Automation:</strong> Order jab <code>Processing</code> ya <code>Completed</code> hota hai, system warehouse se pieces deduct kar ke stock movement log bana deta hai.
                            </div>
                        </div>
                    </div>

                    <!-- Right: Step-by-Step Guide Card -->
                    <div class="col-lg-5 mb-4">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 font-weight-bold text-dark">
                                    <i class="fas fa-list-ol text-purple me-2" style="color: #8b5cf6;"></i> Order Processing Lifecycle
                                </h5>
                                <a href="{{ route('web_orders.index') }}" class="btn btn-sm btn-outline-secondary font-weight-bold" style="color: #8b5cf6; border-color: #8b5cf6;">
                                    View Web Orders &rarr;
                                </a>
                            </div>
                            <div class="card-body p-4" style="max-height: 520px; overflow-y: auto;">
                                <div class="timeline-step mb-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="badge rounded-circle text-white p-2" style="background: #8b5cf6; width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700;">1</div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">Customer Places Order (Status: Pending)</h6>
                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                Customer website par name, address, phone number aur payment method (Cash on Delivery ya Easypaisa) choose kar ke order submit karta hai. Order ka tracking number ban jata hai (e.g. <code>TH-QVWR-1793604174</code>).
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-step mb-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="badge rounded-circle text-white p-2" style="background: #8b5cf6; width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700;">2</div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">Website Control ➔ Web Orders</h6>
                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                Admin panel mein <strong>Website Control ➔ Web Orders</strong> kholein. Wahan naye orders yellow badge <code>pending</code> ke sath show hotay hain.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-step mb-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="badge rounded-circle text-white p-2" style="background: #8b5cf6; width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700;">3</div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">Order View Karein & Customer Details Check Karein</h6>
                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                Order par <strong>"View" (Eye icon)</strong> dabayein. Wahan customer ka delivery address, order items (size, color, quantity), coupon discount aur Easypaisa payment receipt (agar attach hui ho) nazar aayegi.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-step mb-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="badge rounded-circle bg-success text-white p-2" style="width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700;">4</div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">Confirm / Processing ➔ Warehouse Stock Cut!</h6>
                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                Status dropdown se <strong>"Processing"</strong> ya <strong>"Completed"</strong> select kar ke update karein. System foran:
                                                <ul class="mb-0 ps-3 mt-1 text-muted" style="font-size: 12px;">
                                                    <li>Warehouse inventory se order ke pieces deduct kar deta hai.</li>
                                                    <li><code>Stock Movements</code> ledger mein <code>ref_type = 'web_order'</code> record create karta hai.</li>
                                                </ul>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-step">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="badge rounded-circle bg-danger text-white p-2" style="width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700;">5</div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">Agar Order Cancel Ho Jaye?</h6>
                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                Agar status <strong>"Cancelled"</strong> kiya jaye, to system kata hua stock wapis warehouse inventory mein restock/restore kar deta hai!
                                            </p>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 4: BRANDING, BANNERS & WHATSAPP -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="tab-branding" role="tabpanel">
                <div class="row">
                    <!-- Left: Video Player Card -->
                    <div class="col-lg-7 mb-4">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                                <h5 class="mb-0 font-weight-bold text-dark">
                                    <i class="fas fa-video text-warning me-2"></i> Video: Banners, Logos & Policies Setup
                                </h5>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#modalVideo-branding">
                                    <i class="fas fa-edit me-1"></i> Set Video URL
                                </button>
                            </div>
                            <div class="card-body p-0 bg-dark d-flex align-items-center justify-content-center" style="min-height: 380px;">
                                @php $vidBranding = $tutorials['branding']['video_url']; @endphp
                                @if(!empty($vidBranding))
                                    @if(strpos($vidBranding, 'youtube.com') !== false || strpos($vidBranding, 'youtu.be') !== false)
                                        @php
                                            $ytId = '';
                                            if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $vidBranding, $matches)) {
                                                $ytId = $matches[1];
                                            }
                                        @endphp
                                        <iframe class="w-100" style="height: 420px; border: 0;" src="https://www.youtube.com/embed/{{ $ytId }}" allowfullscreen></iframe>
                                    @elseif(strpos($vidBranding, 'loom.com') !== false)
                                        <iframe class="w-100" style="height: 420px; border: 0;" src="{{ str_replace('share', 'embed', $vidBranding) }}" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                                    @else
                                        <video controls class="w-100" style="max-height: 420px;">
                                            <source src="{{ $vidBranding }}" type="video/mp4">
                                        </video>
                                    @endif
                                @else
                                    <div class="p-5 text-center text-white">
                                        <div class="mb-3">
                                            <i class="fas fa-play-circle text-warning" style="font-size: 64px; opacity: 0.85;"></i>
                                        </div>
                                        <h5 class="text-white mb-2">Video Tutorial Coming Soon</h5>
                                        <p class="text-white-50 max-w-md mx-auto mb-3" style="font-size: 13px;">
                                            Website Settings, Store Logo aur WhatsApp button number change karne ki video recording attach karein.
                                        </p>
                                        <button type="button" class="btn btn-warning btn-sm px-3 text-dark font-weight-bold" data-toggle="modal" data-target="#modalVideo-branding">
                                            <i class="fas fa-link me-1"></i> Attach YouTube/Video Link
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer bg-light py-2.5 px-4 text-muted" style="font-size: 12px;">
                                <i class="fas fa-palette me-1 text-warning"></i> <strong>Real-time Update:</strong> Logo ya WhatsApp number change karne ke baad foran live storefront par update ho jata hai.
                            </div>
                        </div>
                    </div>

                    <!-- Right: Step-by-Step Guide Card -->
                    <div class="col-lg-5 mb-4">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 font-weight-bold text-dark">
                                    <i class="fas fa-list-ol text-warning me-2"></i> Branding Quick Checklist
                                </h5>
                                <a href="{{ route('website_settings.index') }}" class="btn btn-sm btn-outline-warning font-weight-bold text-dark">
                                    Website Settings &rarr;
                                </a>
                            </div>
                            <div class="card-body p-4" style="max-height: 520px; overflow-y: auto;">
                                <div class="timeline-step mb-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="badge rounded-circle bg-warning text-dark p-2" style="width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700;">1</div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">Company Logo Change Karein</h6>
                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                <strong>Website Control ➔ Website Settings</strong> kholein. "Brand Identity" section mein transparent PNG logo upload karein.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-step mb-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="badge rounded-circle bg-warning text-dark p-2" style="width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700;">2</div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">WhatsApp Floating Button</h6>
                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                Website par neechay right side par green WhatsApp chat button hai. Wahan apna contact number international format mein daalein (e.g. <code>+923173836223</code>). Customer click karte hi direct WhatsApp chat khul jayegi.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-step mb-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="badge rounded-circle bg-warning text-dark p-2" style="width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700;">3</div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">Homepage Hero Video / Image Banner</h6>
                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                Homepage ke top par modern luxury video ya picture banner lagayein. Aap MP4 luxury fashion video upload kar sakte hain jo loop mein smooth play hoti hai.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-step">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="badge rounded-circle bg-warning text-dark p-2" style="width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700;">4</div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1 text-dark">Shipping & Return Policies</h6>
                                            <p class="text-muted mb-0" style="font-size: 13px;">
                                                Footer aur Checkout par display hone wali "Shipping Policy" aur "7-Days Exchange & Return Policy" ko customize karein.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- Video URL Modals for Admin -->
@foreach($tutorials as $tKey => $tData)
<div class="modal fade" id="modalVideo-{{ $tKey }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('website.tutorials.save_video') }}" class="modal-content border-0 shadow">
            @csrf
            <input type="hidden" name="tutorial_key" value="{{ $tKey }}">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark">
                    <i class="fas fa-video text-primary me-2"></i> Update Video Link: {{ $tData['title'] }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark" style="font-size: 13px;">Video URL (YouTube / Loom / MP4 Link):</label>
                    <input type="text" name="video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=... or https://www.loom.com/share/..." value="{{ $tData['video_url'] }}">
                    <small class="form-text text-muted">
                        Aap YouTube video link, Loom screen recording link, ya direct video MP4 URL paste kar sakte hain.
                    </small>
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm px-3 font-weight-bold">
                    <i class="fas fa-save me-1"></i> Save Video Link
                </button>
            </div>
        </form>
    </div>
</div>
@endforeach

<style>
.nav-pills .nav-link {
    transition: all 0.25s ease-in-out;
    border-radius: 8px !important;
}
.nav-pills .nav-link.active {
    background-color: #0f172a !important;
    color: #ffffff !important;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
}
.nav-pills .nav-link.active div {
    background: #3b82f6 !important;
    color: #ffffff !important;
}
.timeline-step {
    position: relative;
}
.timeline-step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 32px;
    left: 14px;
    width: 2px;
    height: calc(100% - 16px);
    background: #e2e8f0;
}
</style>
@endsection
