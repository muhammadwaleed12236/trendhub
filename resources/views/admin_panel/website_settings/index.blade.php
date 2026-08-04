@extends('admin_panel.layout.app')

@section('content')
<link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
<style>
    .page-container {
        max-width: 1250px;
        margin: 0 auto;
        padding: 24px;
        background-color: #f8fafc;
        border-radius: 16px;
    }
    .page-header h4 {
        color: #0f172a;
        font-weight: 700;
        letter-spacing: -0.02em;
    }
    .section-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        margin-bottom: 24px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .section-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
    }
    .card-header-pro {
        padding: 18px 24px;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        font-weight: 600;
        font-size: 1.15rem;
        display: flex;
        align-items: center;
    }
    .card-body-pro {
        padding: 24px;
    }
    .form-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 8px;
    }
    .form-control, .form-select {
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 10px 16px;
        font-size: 0.95rem;
        color: #1e293b;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .form-control:focus, .form-select:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
        outline: 0;
    }
    .btn-primary {
        background-color: #4f46e5;
        border-color: #4f46e5;
        padding: 12px 32px;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.2s;
    }
    .btn-primary:hover, .btn-primary:focus {
        background-color: #4338ca;
        border-color: #4338ca;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.2);
    }
    .image-preview-container {
        display: flex;
        align-items: center;
        gap: 16px;
        background: #f8fafc;
        padding: 12px;
        border-radius: 12px;
        border: 1px dashed #e2e8f0;
        margin-bottom: 12px;
    }
    .image-preview-container img {
        border-radius: 8px;
        max-height: 60px;
        border: 1px solid #cbd5e1;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .alert-success {
        background-color: #d1fae5;
        border-color: #a7f3d0;
        color: #065f46;
        border-radius: 12px;
        padding: 16px 20px;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(6, 95, 70, 0.05);
    }
    .table {
        border-collapse: separate;
        border-spacing: 0;
    }
    .table th {
        background-color: #f8fafc !important;
        color: #475569;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 14px 16px;
    }
    .table td {
        padding: 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #334155;
    }
    .table-responsive {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .form-switch .form-check-input {
        width: 2.75em;
        height: 1.4em;
        background-color: #e2e8f0;
        border-color: #cbd5e1;
        transition: background-position .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }
    .form-switch .form-check-input:checked {
        background-color: #4f46e5;
        border-color: #4f46e5;
    }
</style>

<div class="page-container mt-3">
    <div class="d-flex align-items-center justify-content-between mb-4 page-header">
        <h4 class="fw-bold mb-0">Website Settings</h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center mb-4 border" role="alert">
            <i class="fas fa-check-circle me-3 fs-4"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <form action="{{ route('website_settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- General Details --}}
        <div class="section-card">
            <div class="card-header-pro text-primary"><i class="fas fa-info-circle me-2 text-primary"></i>General Information</div>
            <div class="card-body-pro">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Site Name</label>
                        <input type="text" name="site_name" class="form-control" value="{{ $settings['web_site_name'] ?? '' }}" placeholder="Enter Site Name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Email</label>
                        <input type="email" name="contact_email" class="form-control" value="{{ $settings['web_contact_email'] ?? '' }}" placeholder="Enter Contact Email">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Phone</label>
                        <input type="text" name="contact_phone" class="form-control" value="{{ $settings['web_contact_phone'] ?? '' }}" placeholder="Enter Contact Phone">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" class="form-control" value="{{ $settings['web_whatsapp_number'] ?? '' }}" placeholder="Enter WhatsApp Number">
                    </div>
                </div>
            </div>
        </div>

        {{-- Social Links --}}
        <div class="section-card">
            <div class="card-header-pro text-info"><i class="fas fa-share-alt me-2 text-info"></i>Social Links</div>
            <div class="card-body-pro">
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label"><i class="fab fa-facebook text-primary me-2"></i> Facebook Link</label>
                        <input type="url" name="facebook_link" class="form-control" value="{{ $settings['web_facebook_link'] ?? '' }}" placeholder="https://facebook.com/yourpage">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="fab fa-instagram text-danger me-2"></i> Instagram Link</label>
                        <input type="url" name="instagram_link" class="form-control" value="{{ $settings['web_instagram_link'] ?? '' }}" placeholder="https://instagram.com/yourpage">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="fab fa-tiktok text-dark me-2"></i> TikTok Link</label>
                        <input type="url" name="tiktok_link" class="form-control" value="{{ $settings['web_tiktok_link'] ?? '' }}" placeholder="https://tiktok.com/@yourpage">
                    </div>
                </div>
            </div>
        </div>

        {{-- Banners & Images --}}
        <div class="section-card">
            <div class="card-header-pro text-success"><i class="fas fa-images me-2 text-success"></i>Banners & Images</div>
            <div class="card-body-pro">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Site Logo</label>
                        @if(isset($settings['web_site_logo']))
                            <div class="image-preview-container">
                                <img src="{{ asset($settings['web_site_logo']) }}" class="border" alt="Site Logo">
                                <span class="small text-muted">Current Logo</span>
                            </div>
                        @endif
                        <input type="file" name="site_logo" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Home Banner Image</label>
                        @if(isset($settings['web_home_banner_image']))
                            <div class="image-preview-container">
                                <img src="{{ asset($settings['web_home_banner_image']) }}" class="border" alt="Home Banner Image">
                                <span class="small text-muted">Current Banner</span>
                            </div>
                        @endif
                        <input type="file" name="home_banner_image" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Home Banner Text</label>
                        <textarea name="home_banner_text" class="form-control" rows="2" placeholder="Write banner slogan or text here...">{{ $settings['web_home_banner_text'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Policies & Content --}}
        <div class="section-card">
            <div class="card-header-pro text-warning"><i class="fas fa-file-contract me-2 text-warning"></i>Policies & Content</div>
            <div class="card-body-pro">
                <div class="row g-4">
                    <div class="col-md-12">
                        <label class="form-label">About Us</label>
                        <textarea name="about_us" class="form-control" rows="4" placeholder="Brief history or description of your company...">{{ $settings['web_about_us'] ?? '' }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Shipping Policy</label>
                        <textarea name="shipping_policy" class="form-control" rows="4" placeholder="Delivery rules, charges, and timelines...">{{ $settings['web_shipping_policy'] ?? '' }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Return & Refund Policy</label>
                        <textarea name="return_policy" class="form-control" rows="4" placeholder="Return timelines and guidelines...">{{ $settings['web_return_policy'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mb-5">
            <button type="submit" class="btn btn-primary px-5 py-3 shadow"><i class="fas fa-save me-2"></i> Save Settings</button>
        </div>
    </form>
</div>

<div class="page-container mt-4 mb-5">
    <form action="{{ route('website_settings.categories.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="section-card">
            <div class="card-header-pro text-primary"><i class="fas fa-tags me-2 text-primary"></i>Category Website Settings</div>
            <div class="card-body-pro p-0">
                <div class="table-responsive border-0 rounded-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Category Name</th>
                                <th>Show on Website</th>
                                <th>Web Image</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                            <tr>
                                <td class="fw-semibold text-slate-700">{{ $category->name }}</td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="categories[{{ $category->id }}][show_on_website]" value="1" {{ $category->show_on_website ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    @if($category->web_image)
                                        <div class="image-preview-container d-inline-flex mb-2">
                                            <img src="{{ asset($category->web_image) }}" class="border" alt="{{ $category->name }}">
                                        </div>
                                    @endif
                                    <input type="file" name="categories[{{ $category->id }}][web_image]" class="form-control form-control-sm" accept="image/*">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-end p-4 border-top">
                    <button type="submit" class="btn btn-primary px-5 py-3 shadow"><i class="fas fa-save me-2"></i> Save Category Settings</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
