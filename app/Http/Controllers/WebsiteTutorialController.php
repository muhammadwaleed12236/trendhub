<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class WebsiteTutorialController extends Controller
{
    public function index()
    {
        $tutorials = [
            'products' => [
                'id' => 'products',
                'title' => 'Product Uploading & Variants (Size/Color)',
                'badge' => 'Catalog Management',
                'icon' => 'fa-box-open',
                'color' => '#3b82f6',
                'video_url' => Setting::get('tutorial_video_products', ''),
                'description' => 'Website par naya product kaise add karein, size aur color variants kaise banayein, aur images kaise upload karein.',
            ],
            'coupons' => [
                'id' => 'coupons',
                'title' => 'Discount Coupons & Promotional Offers',
                'badge' => 'Marketing & Sales',
                'icon' => 'fa-tags',
                'color' => '#10b981',
                'video_url' => Setting::get('tutorial_video_coupons', ''),
                'description' => 'Discount coupon codes kaise banayein (Percentage ya Flat PKR), expiry date aur limits kaise set karein.',
            ],
            'orders' => [
                'id' => 'orders',
                'title' => 'Web Orders & Warehouse Stock Auto-Deduction',
                'badge' => 'Order Fulfillment',
                'icon' => 'fa-truck-fast',
                'color' => '#8b5cf6',
                'video_url' => Setting::get('tutorial_video_orders', ''),
                'description' => 'Website se anay walay orders kaise check karein, verify karein, aur status change karne par warehouse stock kaise deduct hoga.',
            ],
            'branding' => [
                'id' => 'branding',
                'title' => 'Website Branding, Banners & WhatsApp Button',
                'badge' => 'Store Appearance',
                'icon' => 'fa-sliders',
                'color' => '#f59e0b',
                'video_url' => Setting::get('tutorial_video_branding', ''),
                'description' => 'Store logo, top announcement bar, homepage hero banner/video, WhatsApp button number aur policies kaise badlein.',
            ],
        ];

        return view('admin_panel.website_settings.tutorials', compact('tutorials'));
    }

    public function saveVideo(Request $request)
    {
        $request->validate([
            'tutorial_key' => 'required|string|in:products,coupons,orders,branding',
            'video_url' => 'nullable|string',
        ]);

        $key = 'tutorial_video_' . $request->tutorial_key;
        Setting::set($key, $request->video_url, 'website', 'string', 'Tutorial Video URL');

        return redirect()->back()->with('success', 'Video URL successfully updated for this tutorial!');
    }
}
