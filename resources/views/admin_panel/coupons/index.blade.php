@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Coupons</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCouponModal">
            Add New Coupon
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Min Spend</th>
                            <th>Max Uses</th>
                            <th>Uses</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($coupons as $coupon)
                        <tr>
                            <td>{{ $coupon->id }}</td>
                            <td>{{ $coupon->code }}</td>
                            <td>{{ ucfirst($coupon->type) }}</td>
                            <td>{{ $coupon->value }}</td>
                            <td>{{ $coupon->min_spend ?? 'N/A' }}</td>
                            <td>{{ $coupon->max_uses ?? 'Unlimited' }}</td>
                            <td>{{ $coupon->uses }}</td>
                            <td>
                                @if($coupon->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editCouponModal{{ $coupon->id }}">Edit</button>
                                <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editCouponModal{{ $coupon->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Coupon</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Code</label>
                                                <input type="text" name="code" class="form-control" value="{{ $coupon->code }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Type</label>
                                                <select name="type" class="form-control" required>
                                                    <option value="fixed" {{ $coupon->type == 'fixed' ? 'selected' : '' }}>Fixed</option>
                                                    <option value="percent" {{ $coupon->type == 'percent' ? 'selected' : '' }}>Percent</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Value</label>
                                                <input type="number" step="0.01" name="value" class="form-control" value="{{ $coupon->value }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Min Spend</label>
                                                <input type="number" step="0.01" name="min_spend" class="form-control" value="{{ $coupon->min_spend }}">
                                            </div>
                                            <div class="form-group">
                                                <label>Max Uses</label>
                                                <input type="number" name="max_uses" class="form-control" value="{{ $coupon->max_uses }}">
                                            </div>
                                            <div class="form-group">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="customSwitch{{ $coupon->id }}" name="is_active" value="1" {{ $coupon->is_active ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="customSwitch{{ $coupon->id }}">Active</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save changes</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addCouponModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('admin.coupons.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Coupon</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Code</label>
                        <input type="text" name="code" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Type</label>
                        <select name="type" class="form-control" required>
                            <option value="fixed">Fixed</option>
                            <option value="percent">Percent</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Value</label>
                        <input type="number" step="0.01" name="value" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Min Spend</label>
                        <input type="number" step="0.01" name="min_spend" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Max Uses</label>
                        <input type="number" name="max_uses" class="form-control">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="customSwitchAdd" name="is_active" value="1" checked>
                            <label class="custom-control-label" for="customSwitchAdd">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Coupon</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
