@extends('admin_panel.layout.app')

@section('content')
<link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
<style>
    .page-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 15px;
    }
    .section-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
        margin-bottom: 20px;
    }
    .card-header-pro {
        padding: 15px 20px;
        border-bottom: 1px solid #e2e8f0;
        font-weight: bold;
        font-size: 1.1rem;
    }
    .card-body-pro {
        padding: 20px;
    }
</style>

<div class="page-container">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">Website Settings</h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('website_settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- General Details --}}
        <div class="section-card">
            <div class="card-header-pro text-primary"><i class="fas fa-info-circle me-2"></i>General Information</div>
            <div class="card-body-pro">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Site Name</label>
                        <input type="text" name="site_name" class="form-control" value="{{ $settings['web_site_name'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Email</label>
                        <input type="email" name="contact_email" class="form-control" value="{{ $settings['web_contact_email'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Phone</label>
                        <input type="text" name="contact_phone" class="form-control" value="{{ $settings['web_contact_phone'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" class="form-control" value="{{ $settings['web_whatsapp_number'] ?? '' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Social Links --}}
        <div class="section-card">
            <div class="card-header-pro text-info"><i class="fas fa-share-alt me-2"></i>Social Links</div>
            <div class="card-body-pro">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label"><i class="fab fa-facebook text-primary"></i> Facebook Link</label>
                        <input type="url" name="facebook_link" class="form-control" value="{{ $settings['web_facebook_link'] ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="fab fa-instagram text-danger"></i> Instagram Link</label>
                        <input type="url" name="instagram_link" class="form-control" value="{{ $settings['web_instagram_link'] ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="fab fa-tiktok text-dark"></i> TikTok Link</label>
                        <input type="url" name="tiktok_link" class="form-control" value="{{ $settings['web_tiktok_link'] ?? '' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Banners & Images --}}
        <div class="section-card">
            <div class="card-header-pro text-success"><i class="fas fa-images me-2"></i>Banners & Images</div>
            <div class="card-body-pro">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Site Logo</label>
                        @if(isset($settings['web_site_logo']))
                            <div class="mb-2"><img src="{{ asset($settings['web_site_logo']) }}" style="height:50px;" class="border"></div>
                        @endif
                        <input type="file" name="site_logo" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Home Banner Image</label>
                        @if(isset($settings['web_home_banner_image']))
                            <div class="mb-2"><img src="{{ asset($settings['web_home_banner_image']) }}" style="height:50px;" class="border"></div>
                        @endif
                        <input type="file" name="home_banner_image" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Home Banner Text</label>
                        <textarea name="home_banner_text" class="form-control" rows="2">{{ $settings['web_home_banner_text'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Policies & Content --}}
        <div class="section-card">
            <div class="card-header-pro text-warning"><i class="fas fa-file-contract me-2"></i>Policies & Content</div>
            <div class="card-body-pro">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">About Us</label>
                        <textarea name="about_us" class="form-control" rows="4">{{ $settings['web_about_us'] ?? '' }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Shipping Policy</label>
                        <textarea name="shipping_policy" class="form-control" rows="4">{{ $settings['web_shipping_policy'] ?? '' }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Return & Refund Policy</label>
                        <textarea name="return_policy" class="form-control" rows="4">{{ $settings['web_return_policy'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mb-5">
            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold"><i class="fas fa-save me-2"></i> Save Settings</button>
        </div>
    </form>
</div>

<div class="page-container mt-4">
    <form action="{{ route('website_settings.categories.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="section-card">
            <div class="card-header-pro text-primary"><i class="fas fa-tags me-2"></i>Category Website Settings</div>
            <div class="card-body-pro">
                <div class="table-responsive">
                    <table class="table table-bordered">
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
                                <td>{{ $category->name }}</td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="categories[{{ $category->id }}][show_on_website]" value="1" {{ $category->show_on_website ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    @if($category->web_image)
                                        <div class="mb-2">
                                            <img src="{{ asset($category->web_image) }}" style="height: 50px;" class="border">
                                        </div>
                                    @endif
                                    <input type="file" name="categories[{{ $category->id }}][web_image]" class="form-control form-control-sm" accept="image/*">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary px-5 py-2 fw-bold"><i class="fas fa-save me-2"></i> Save Category Settings</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
