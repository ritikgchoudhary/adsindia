<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AccountLedgerController extends Controller
{
    public function getSummary(Request $request)
    {
        $gs = gs();
        $ledgerClearedAt = $gs->admin_ledger_cleared_at;
        $hiddenDates = json_decode($gs->admin_ledger_hidden_dates ?? '[]', true);
        $todayStr = Carbon::today()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Helper to apply global clear and hidden dates filter to queries
        $applyFilters = function($query, $dateColumn = 'created_at') use ($ledgerClearedAt, $hiddenDates) {
            if ($ledgerClearedAt) {
                $query->where('created_at', '>', $ledgerClearedAt);
            }
            if (!empty($hiddenDates)) {
                if ($dateColumn === 'date') {
                    $query->whereNotIn('date', $hiddenDates);
                } else {
                    $query->whereNotIn(DB::raw("DATE($dateColumn)"), $hiddenDates);
                }
            }
            return $query;
        };

        // 1. Calculations for Summary Cards (Respecting Filters)
        $todayProfit = 0;
        if (!in_array($todayStr, $hiddenDates)) {
            $todayRecharge = $applyFilters(Deposit::where('status', 1)->whereDate('created_at', $todayStr))->sum('amount');
            $todayWithdraw = $applyFilters(Withdrawal::where('status', 1)->whereDate('created_at', $todayStr))->sum('amount');
            $todayExpense = $applyFilters(Expense::whereDate('date', $todayStr), 'date')->sum('amount');
            $todayProfit = $todayRecharge - $todayWithdraw - $todayExpense;
        }

        $monthRecharge = $applyFilters(Deposit::where('status', 1)->where('created_at', '>=', $startOfMonth))->sum('amount');
        $monthWithdraw = $applyFilters(Withdrawal::where('status', 1)->where('created_at', '>=', $startOfMonth))->sum('amount');
        $monthExpense = $applyFilters(Expense::where('date', '>=', $startOfMonth), 'date')->sum('amount');
        $monthProfit = $monthRecharge - $monthWithdraw - $monthExpense;

        $totalRecharge = $applyFilters(Deposit::where('status', 1))->sum('amount');
        $totalWithdraw = $applyFilters(Withdrawal::where('status', 1))->sum('amount');
        $totalExpense = $applyFilters(Expense::query(), 'date')->sum('amount');
        $totalProfit = $totalRecharge - $totalWithdraw - $totalExpense;

        // 2. Get All Unique Dates (Respecting Global Clear and individual hidden filter)
        $getDates = function($model, $isExpense = false) use ($ledgerClearedAt, $hiddenDates) {
            $q = $model->query();
            if ($isExpense) {
                $q->select('date as d');
            } else {
                $q->where('status', 1)->select(DB::raw('DATE(created_at) as d'));
            }
            
            if ($ledgerClearedAt) $q->where('created_at', '>', $ledgerClearedAt);
            if (!empty($hiddenDates)) {
                if ($isExpense) $q->whereNotIn('date', $hiddenDates);
                else $q->whereNotIn(DB::raw('DATE(created_at)'), $hiddenDates);
            }

            return $q->distinct()->pluck('d')->toArray();
        };

        $allDates = array_unique(array_merge(
            $getDates(new Deposit),
            $getDates(new Withdrawal),
            $getDates(new Expense, true)
        ));
        rsort($allDates);

        // 3. Manual Pagination
        $page = $request->get('page', 1);
        $perPage = 10;
        $total = count($allDates);
        $paginatedDates = array_slice($allDates, ($page - 1) * $perPage, $perPage);

        $dailyData = [];
        foreach ($paginatedDates as $date) {
            $recharge = Deposit::where('status', 1)->whereDate('created_at', $date)->sum('amount');
            $withdraw = Withdrawal::where('status', 1)->whereDate('created_at', $date)->sum('amount');
            $expenses = Expense::whereDate('date', $date)->get();
            $expenseSum = $expenses->sum('amount');
            
            $dailyData[] = [
                'date' => $date,
                'human_date' => Carbon::parse($date)->format('d M Y'),
                'recharge' => $recharge,
                'withdraw' => $withdraw,
                'expenses_sum' => $expenseSum,
                'expenses_list' => $expenses,
                'net_profit' => $recharge - $withdraw - $expenseSum,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'summary' => [
                    'today_profit' => $todayProfit,
                    'month_profit' => $monthProfit,
                    'total_profit' => $totalProfit,
                ],
                'daily_records' => $dailyData,
                'hidden_dates' => $hiddenDates,
                'pagination' => [
                    'current_page' => (int)$page,
                    'last_page' => (int)ceil($total / $perPage),
                    'total' => $total,
                    'per_page' => $perPage,
                ]
            ]
        ]);
    }

    public function addExpense(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'details' => 'nullable|string'
        ]);

        Expense::create([
            'date' => $request->date,
            'title' => $request->title,
            'amount' => $request->amount,
            'details' => $request->details,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Expense added successfully'
        ]);
    }

    public function deleteExpense($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Expense deleted successfully'
        ]);
    }

    public function hideDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $gs = gs();
        $hiddenDates = json_decode($gs->admin_ledger_hidden_dates ?? '[]', true);
        
        if (!in_array($request->date, $hiddenDates)) {
            $hiddenDates[] = $request->date;
            $gs->admin_ledger_hidden_dates = json_encode($hiddenDates);
            $gs->save();
            \Cache::forget('GeneralSetting');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Date hidden from ledger view'
        ]);
    }
    public function restoreDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $gs = gs();
        $hiddenDates = json_decode($gs->admin_ledger_hidden_dates ?? '[]', true);
        
        if (in_array($request->date, $hiddenDates)) {
            $hiddenDates = array_values(array_filter($hiddenDates, function($d) use ($request) {
                return $d != $request->date;
            }));
            $gs->admin_ledger_hidden_dates = json_encode($hiddenDates);
            $gs->save();
            \Cache::forget('GeneralSetting');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Date restored to ledger view'
        ]);
    }
}
