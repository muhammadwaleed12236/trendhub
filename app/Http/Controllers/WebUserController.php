<?php

namespace App\Http\Controllers;

use App\Models\WebCustomer;
use App\Models\Customer;
use Illuminate\Http\Request;

class WebUserController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasAnyPermission(['web_users.view', 'web_users.read'])) {
            abort(403, 'Unauthorized action. You do not have permission to view Web Users.');
        }

        $webCustomers = WebCustomer::latest()->get();

        return view('admin_panel.web_users.index', compact('webCustomers'));
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasPermissionTo('web_users.delete')) {
            return redirect()->back()->with('error', 'Unauthorized action. You do not have permission to delete Web Users.');
        }

        $webCustomer = WebCustomer::findOrFail($id);
        
        // Remove link or keep it depending on requirements. Let's just delete the web user.
        $webCustomer->delete();

        return redirect()->route('web_users.index')->with('success', 'Web User deleted successfully.');
    }
}
