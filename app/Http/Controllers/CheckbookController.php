<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JournalEntry;
use App\Models\Account;
use App\Models\AccountHead;
use Carbon\Carbon;

class CheckbookController extends Controller
{
    public function index(Request $request)
    {
        // 1. Get all Cash and Bank Accounts
        $cashAndBankHeads = AccountHead::whereIn('name', ['Cash', 'Bank'])->pluck('id');
        $validAccountIds = Account::whereIn('head_id', $cashAndBankHeads)->pluck('id');
        $accounts = Account::whereIn('id', $validAccountIds)->get();

        // 2. Build Query for Journal Entries
        $query = JournalEntry::with(['account', 'source'])->whereIn('account_id', $validAccountIds);

        // Date Filtering
        if ($request->filled('from_date')) {
            $query->whereDate('entry_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('entry_date', '<=', $request->to_date);
        }
        
        // Period filtering override
        if ($request->filled('period') && $request->period !== 'custom') {
            $now = Carbon::now();
            switch ($request->period) {
                case 'day':
                    $query->whereDate('entry_date', $now->toDateString());
                    break;
                case 'week':
                    $query->whereBetween('entry_date', [$now->startOfWeek()->toDateString(), $now->endOfWeek()->toDateString()]);
                    break;
                case 'month':
                    $query->whereMonth('entry_date', $now->month)->whereYear('entry_date', $now->year);
                    break;
                case 'year':
                    $query->whereYear('entry_date', $now->year);
                    break;
            }
        }

        // Account Filter
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        // Transaction Type Filter (In / Out)
        if ($request->filled('type')) {
            if ($request->type === 'in') {
                $query->where('debit', '>', 0);
            } elseif ($request->type === 'out') {
                $query->where('credit', '>', 0);
            }
        }

        // Order by date and ID
        $query->orderBy('entry_date', 'asc')->orderBy('id', 'asc');

        // Execute Query
        $transactions = $query->get();

        // Calculate Summaries and Running Balances
        $totalIn = 0;
        $totalOut = 0;
        $runningBalance = 0;

        foreach ($transactions as $transaction) {
            $totalIn += $transaction->debit;
            $totalOut += $transaction->credit;
            
            // Debit increases Cash/Bank (Asset), Credit decreases
            $runningBalance += ($transaction->debit - $transaction->credit);
            $transaction->running_balance = $runningBalance;
        }

        $netBalance = $totalIn - $totalOut;

        // Cash in Hand (Just accounts under "Cash" head)
        $cashHeadIds = AccountHead::where('name', 'Cash')->pluck('id');
        $cashAccountIds = Account::whereIn('head_id', $cashHeadIds)->pluck('id');
        $cashInHand = JournalEntry::whereIn('account_id', $cashAccountIds)
                        ->selectRaw('SUM(debit) - SUM(credit) as balance')
                        ->value('balance') ?? 0;

        // If it's an AJAX request, return the partial view
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin_panel.checkbook.partials.table_body', compact('transactions'))->render(),
                'summary' => [
                    'totalIn' => number_format($totalIn, 2),
                    'totalOut' => number_format($totalOut, 2),
                    'netBalance' => number_format($netBalance, 2),
                    'cashInHand' => number_format($cashInHand, 2),
                ]
            ]);
        }

        return view('admin_panel.checkbook.index', compact(
            'transactions', 
            'accounts', 
            'totalIn', 
            'totalOut', 
            'netBalance', 
            'cashInHand'
        ));
    }
}
