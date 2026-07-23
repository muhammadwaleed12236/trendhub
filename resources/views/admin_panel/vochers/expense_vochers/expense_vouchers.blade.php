@extends('admin_panel.layout.app')
@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/css/bootstrap-icons.min.css') }}">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        :root {
            --primary: #0f172a;
            --primary-hover: #334155;
            --secondary: #f1f5f9;
            --secondary-hover: #e2e8f0;
            --accent: #2563eb;
            --accent-hover: #1d4ed8;
            --bg: #f8fafc;
            --surface: #ffffff;
            --border: #94a3b8;
            --border-hover: #64748b;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --danger: #ef4444;
            --danger-bg: #fef2f2;
            --success: #10b981;
            --success-bg: #ecfdf5;
            --radius-md: 8px;
            --radius-lg: 12px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
        }

        /* Layout & Cards */
        .erp-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
            padding: 32px 40px;
            margin-bottom: 24px;
        }

        .erp-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border);
        }

        .erp-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-main);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .erp-title i {
            color: var(--accent);
            background: #eff6ff;
            padding: 8px 12px;
            border-radius: var(--radius-md);
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-main);
            margin: 32px 0 20px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .section-title i {
            color: var(--text-muted);
            font-size: 1.2rem;
        }
        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
            margin-left: 8px;
        }

        /* Form Controls */
        .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 6px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .form-control, .form-select {
            background-color: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 10px 14px;
            font-size: 0.95rem;
            color: var(--text-main);
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.01);
            width: 100%;
            appearance: none;
        }
        
        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1em 1em;
            padding-right: 2.5rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            outline: none;
        }
        
        .form-control:hover:not(:focus):not([readonly]),
        .form-select:hover:not(:focus):not([readonly]) {
            border-color: var(--border-hover);
        }

        .form-control[readonly] {
            background-color: #f8fafc;
            color: var(--text-muted);
            border-color: var(--border);
            cursor: not-allowed;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        /* Status Card (Balance) */
        .balance-card {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
        }
        .balance-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        .balance-value {
            font-size: 1.1rem;
            font-weight: 700;
        }
        .balance-dr .balance-value { color: var(--danger); }
        .balance-cr .balance-value { color: var(--success); }
        .balance-dr { background: var(--danger-bg); border-color: #fecaca; }
        .balance-cr { background: var(--success-bg); border-color: #a7f3d0; }

        /* Buttons */
        .btn-primary {
            background-color: var(--accent);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            padding: 10px 24px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 1px 3px rgba(37,99,235,0.3);
            cursor: pointer;
        }
        .btn-primary:hover {
            background-color: var(--accent-hover);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: var(--surface);
            color: var(--text-main);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 10px 20px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-secondary:hover {
            background-color: var(--secondary);
            border-color: var(--border-hover);
            color: var(--text-main);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--accent);
            border: 1px dashed var(--border-hover);
            border-radius: var(--radius-md);
            padding: 12px 24px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            justify-content: center;
            cursor: pointer;
            margin-top: 16px;
        }
        .btn-outline:hover {
            background-color: #eff6ff;
            border-color: var(--accent);
        }
        
        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: var(--radius-md);
            background: var(--secondary);
            color: var(--text-muted);
            border: 1px solid transparent;
            transition: all 0.2s;
            cursor: pointer;
        }
        .btn-icon:hover {
            background: #e2e8f0;
            color: var(--text-main);
        }
        .btn-icon.danger {
            background: var(--danger-bg);
            color: var(--danger);
        }
        .btn-icon.danger:hover {
            background: #fee2e2;
        }

        /* Table Design */
        .table-wrapper {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            overflow: hidden;
        }
        
        .erp-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .erp-table th {
            background-color: var(--secondary);
            color: var(--text-muted);
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        .erp-table td {
            padding: 12px 16px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border);
        }
        
        .erp-table tr:last-child td {
            border-bottom: none;
        }
        
        .erp-table tr:hover td {
            background-color: #f8fafc;
        }

        /* Summary Box */
        .summary-box-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-top: 24px;
        }
        .summary-box {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 20px 24px;
            width: 350px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .summary-label {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-muted);
        }
        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
            border: none;
            background: transparent;
            text-align: right;
            width: 150px;
            padding: 0;
        }
        .summary-value:focus {
            outline: none;
        }
        
        /* Grid spacing */
        .row-gap-3 { row-gap: 24px; }
        
        .text-end { text-align: right !important; }
        .text-center { text-align: center !important; }
    </style>

    <div class="main-content">
        <div class="main-content-inner" style="padding: 10px;">
            <div class="container-fluid p-0" style="max-width: 1200px; margin: 0 auto;">

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" style="border-radius: 10px;">
                        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" style="border-radius: 10px;">
                        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('store_expense_vochers') }}" method="POST" id="expenseForm">
                    @csrf

                    <div class="erp-card">
                        <!-- Header -->
                        <div class="erp-header">
                            <h2 class="erp-title">
                                <i class="bi bi-wallet2"></i> Expense Voucher
                            </h2>
                            <div class="d-flex gap-3">
                                <a href="{{ route('all_expense_vochers') }}" class="btn-secondary">
                                    <i class="bi bi-list-ul"></i> All Expenses
                                </a>
                                <button type="submit" class="btn-primary">
                                    <i class="bi bi-check2"></i> Save Voucher
                                </button>
                            </div>
                        </div>

                        <!-- Row 1: Voucher Info -->
                        <div class="section-title">
                            <i class="bi bi-info-circle"></i> Voucher Details
                        </div>
                        <div class="row row-gap-3 mb-4">
                            <div class="col-md-3 col-lg-2">
                                <label class="form-label">Voucher No</label>
                                <input type="text" class="form-control" name="evid" value="{{ $nextRvid }}" readonly>
                            </div>
                            <div class="col-md-3 col-lg-2">
                                <label class="form-label">Entry Date</label>
                                <input type="date" name="entry_date" class="form-control" value="{{ now()->toDateString() }}">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">Reference / Cheque #</label>
                                <input type="text" name="ref_no_header" class="form-control" placeholder="e.g. Chq-848492">
                            </div>
                            <div class="col-md-12 col-lg-5">
                                <label class="form-label">Global Remarks <span class="text-lowercase text-muted fw-normal" style="text-transform:none;">(optional)</span></label>
                                <input type="text" name="remarks" class="form-control" id="remarks" placeholder="General description of payment...">
                            </div>
                        </div>

                        <!-- Row 2: Paid From -->
                        <div class="section-title">
                            <i class="bi bi-bank"></i> Paid From (Source)
                        </div>
                        <div class="row row-gap-3 mb-5 align-items-end">
                            <div class="col-md-4 col-lg-3">
                                <label class="form-label">Payment Source Type</label>
                                <select name="vendor_type" class="form-select" id="partyType">
                                    <option value="" disabled selected>Select Type</option>
                                    @foreach ($AccountHeads as $head)
                                        <option value="{{ $head->id }}">{{ $head->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5 col-lg-4">
                                <label class="form-label">Account / Party</label>
                                <select name="vendor_id" class="form-select" id="partyId" required>
                                    <option disabled selected>Select Account</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-lg-2">
                                <label class="form-label">Account Code / Phone</label>
                                <input type="text" name="tel" id="tel" class="form-control" readonly>
                            </div>
                            <div class="col-md-12 col-lg-3">
                                <div id="balanceContainer" class="balance-card">
                                    <span class="balance-label">Current Balance</span>
                                    <span id="balanceDisplay" class="balance-value">0.00 <span style="font-size: 0.8em">Dr</span></span>
                                </div>
                            </div>
                        </div>

                        <!-- Expense Allocation -->
                        <div class="section-title">
                            <i class="bi bi-list-columns-reverse"></i> Expense Allocation
                        </div>
                        <div class="table-wrapper">
                            <table class="erp-table" id="voucherTable">
                                <thead>
                                    <tr>
                                        <th style="width: 35%;">Expense Category</th>
                                        <th style="width: 40%;">Remarks / Description</th>
                                        <th style="width: 18%;" class="text-end">Amount (Rs.)</th>
                                        <th style="width: 7%;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <select name="row_account_id[]" class="form-select rowAccountCategory" required>
                                                    <option value="" disabled selected>Select Category</option>
                                                    @foreach ($expenseCategories as $cat)
                                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                    @endforeach
                                                </select>
                                                <a href="javascript:void(0)" data-toggle="modal" data-target="#newExpenseCategoryModal" class="btn-icon" title="Add New Category">
                                                    <i class="bi bi-plus-lg"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" name="narration_text[]" class="form-control" placeholder="e.g. Rent payment, office repair...">
                                            <input type="hidden" name="narration_id[]" value="">
                                        </td>
                                        <td>
                                            <input type="number" name="amount[]" step="0.01" class="form-control text-end fw-bold amount" placeholder="0.00" required>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn-icon danger removeRow" title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <button type="button" class="btn-outline" id="addNewRow">
                            <i class="bi bi-plus-circle"></i> Add Another Expense Category
                        </button>

                        <div class="summary-box-wrapper">
                            <div class="summary-box">
                                <span class="summary-label">Total Expense Amount</span>
                                <div class="d-flex align-items-center">
                                    <span class="text-muted" style="font-weight: 600; font-size: 1.2rem; margin-right: 4px;">Rs.</span>
                                    <input type="text" name="total_amount" class="summary-value" id="totalAmount" readonly value="0.00">
                                </div>
                            </div>
                        </div>

                        {{-- Hidden fields --}}
                        <input type="hidden" name="reference_no[]" value="">
                        <input type="hidden" name="discount_value[]" value="0">
                        <input type="hidden" name="rate[]" value="0">

                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

    <!-- New Expense Category Modal -->
    <div class="modal fade" id="newExpenseCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fs-6">Create New Expense Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="newExpenseCategoryForm">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="form-label text-secondary fw-bold" style="font-size: 12px;">CATEGORY NAME <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Utility Bills, Travel" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label text-secondary fw-bold" style="font-size: 12px;">CODE (Optional)</label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. UTIL-01">
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label text-secondary fw-bold" style="font-size: 12px;">DESCRIPTION</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveNewCategory" style="background:var(--accent); color:white; padding:8px 20px; border-radius:8px; border:none;">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@section('js')
    <script>
        $(document).ready(function() {

            // Header Party Type Selection
            $('#partyType').on('change', function() {
                let type = $(this).val();
                loadPartyList(type);
            });

            function loadPartyList(type) {
                let $select = $('#partyId');
                $select.html('<option disabled selected>Loading...</option>');
                $('#tel').val('');
                updateBalance(0);

                if (type === 'vendor' || type === 'customer') {
                    $.get('{{ route("party.list") }}?type=' + type, function(data) {
                        $select.empty().append('<option disabled selected>Select Party</option>');
                        data.forEach(function(item) {
                            $select.append(
                                `<option value="${item.id}" data-phone="${item.mobile || ''}" data-bal="${item.closing_balance}">${item.text}</option>`
                            );
                        });
                    });
                } else if (type) {
                    $.get('{{ url("get-accounts-by-head") }}/' + type, function(data) {
                        $select.empty().append('<option disabled selected>Select Account</option>');
                        data.forEach(function(acc) {
                            $select.append(
                                `<option value="${acc.id}" data-code="${acc.account_code}" data-bal="${acc.current_balance || acc.opening_balance || 0}">${acc.title}</option>`
                            );
                        });
                    });
                }
            }

            $('#partyId').on('change', function() {
                let $opt = $(this).find(':selected');
                let codeOrPhone = $opt.data('phone') || $opt.data('code') || '';
                $('#tel').val(codeOrPhone);
                let bal = parseFloat($opt.data('bal')) || 0;
                updateBalance(bal);

                // Auto-set global remarks
                let partyName = $opt.text().trim();
                if (!$('#remarks').val()) {
                    $('#remarks').val('Expense paid through ' + partyName);
                }
            });

            function updateBalance(bal) {
                let $container = $('#balanceContainer');
                let $badge = $('#balanceDisplay');
                let formatted = Math.abs(bal).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                if (bal >= 0) {
                    $container.removeClass('balance-cr').addClass('balance-dr');
                    $badge.html(formatted + ' <span style="font-size: 0.8em">Dr</span>');
                } else {
                    $container.removeClass('balance-dr').addClass('balance-cr');
                    $badge.html(formatted + ' <span style="font-size: 0.8em">Cr</span>');
                }
            }

            // Totals Calculation
            function calculateTotal() {
                let total = 0;
                $('.amount').each(function() {
                    total += parseFloat($(this).val()) || 0;
                });
                $('#totalAmount').val(total.toFixed(2));
            }

            $(document).on('input', '.amount', function() {
                calculateTotal();
            });

            // Add Row
            $('#addNewRow').on('click', function() {
                let optionsHtml = $('.rowAccountCategory').first().html();
                let newRow = `
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <select name="row_account_id[]" class="form-select rowAccountCategory" required>
                                ${optionsHtml}
                            </select>
                            <a href="javascript:void(0)" data-toggle="modal" data-target="#newExpenseCategoryModal" class="btn-icon" title="Add New Category">
                                <i class="bi bi-plus-lg"></i>
                            </a>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="narration_text[]" class="form-control" placeholder="e.g. Rent payment, office repair...">
                        <input type="hidden" name="narration_id[]" value="">
                    </td>
                    <td>
                        <input type="number" name="amount[]" step="0.01" class="form-control text-end fw-bold amount" placeholder="0.00" required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn-icon danger removeRow" title="Remove"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `;
                $('#voucherTable tbody').append(newRow);
                $('#voucherTable tbody tr:last-child .rowAccountCategory').val('');
            });

            // Remove Row
            $(document).on('click', '.removeRow', function() {
                if ($('#voucherTable tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                    calculateTotal();
                }
            });

            // Enter key adds new row
            $(document).on('keypress', '.amount', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#addNewRow').click();
                }
            });

            // Handle New Expense Category AJAX Submission
            $('#newExpenseCategoryForm').on('submit', function(e) {
                e.preventDefault();
                let btn = $('#btnSaveNewCategory');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                
                $.ajax({
                    url: "{{ route('expense_categories.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        btn.prop('disabled', false).text('Save Category');
                        if(response.success && response.category) {
                            Swal.fire({ toast:true, position:'top-end', icon:'success', title:'Category Created', showConfirmButton:false, timer:1500 });
                            $('#newExpenseCategoryModal').modal('hide');
                            $('#newExpenseCategoryForm')[0].reset();
                            
                            // Append new option to all existing dropdowns and select it on the last one or empty ones
                            let newCat = response.category;
                            let newOptionHtml = `<option value="${newCat.id}">${newCat.name}</option>`;
                            $('.rowAccountCategory').each(function() {
                                $(this).append(newOptionHtml);
                                if (!$(this).val()) {
                                    $(this).val(newCat.id).trigger('change');
                                }
                            });
                        } else {
                            Swal.fire('Error', response.message || 'Failed to create Category.', 'error');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).text('Save Category');
                        let msg = 'Failed to create Category.';
                        if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });
        });
    </script>
@endsection
