<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\CashTransaction;
use App\Models\IuranPayment;
use App\Models\IuranType;
use App\Models\Announcement;
use App\Models\IotDevice;
use App\Models\IotRfidCard;
use App\Models\IotTelemetryLog;
use App\Models\IotEmergencyAlert;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // High level financial metrics
        $totalPemasukan = CashTransaction::where('type', 'pemasukan')->sum('amount');
        $totalPengeluaran = CashTransaction::where('type', 'pengeluaran')->sum('amount');
        $saldoKas = $totalPemasukan - $totalPengeluaran;

        $pemasukanBulanIni = CashTransaction::where('type', 'pemasukan')
            ->whereMonth('transaction_date', Carbon::now()->month)
            ->whereYear('transaction_date', Carbon::now()->year)
            ->sum('amount');

        $pengeluaranBulanIni = CashTransaction::where('type', 'pengeluaran')
            ->whereMonth('transaction_date', Carbon::now()->month)
            ->whereYear('transaction_date', Carbon::now()->year)
            ->sum('amount');

        $pendingPaymentsCount = IuranPayment::where('status', 'pending')->count();
        $recentAnnouncements = Announcement::where('is_active', true)->latest()->take(4)->get();
        $recentTransactions = CashTransaction::with('creator')->latest()->take(6)->get();

        $totalKas = $saldoKas;

        // IoT Common Metrics
        $iotDeviceCount = IotDevice::count();
        $iotOnlineCount = IotDevice::where('status', 'online')->count();
        $iotAlertCount = IotEmergencyAlert::where('status', 'active')->count();
        $recentIotLogs = IotTelemetryLog::with('user')->latest()->take(5)->get();

        // Role specific logic
        if ($user->isSuperadmin()) {
            $totalUsers = User::count();
            $userCountsByRole = [
                'superadmin' => User::where('role', 'superadmin')->count(),
                'bendahara' => User::where('role', 'bendahara')->count(),
                'rt' => User::where('role', 'rt')->count(),
                'warga' => User::where('role', 'warga')->count(),
            ];
            $iuranTypes = IuranType::all();
            $devices = IotDevice::all();

            return view('dashboard.superadmin', compact(
                'user', 'totalKas', 'totalPemasukan', 'totalPengeluaran',
                'pemasukanBulanIni', 'pengeluaranBulanIni', 'totalUsers', 'userCountsByRole',
                'iuranTypes', 'pendingPaymentsCount', 'recentAnnouncements', 'recentTransactions',
                'iotDeviceCount', 'iotOnlineCount', 'iotAlertCount', 'recentIotLogs', 'devices'
            ));
        }

        if ($user->isBendahara()) {
            $pendingPayments = IuranPayment::with(['user', 'iuranType'])->where('status', 'pending')->latest()->get();
            $activeIuranTypes = IuranType::where('is_active', true)->get();
            $iotKasMasuk = CashTransaction::where('category', 'like', '%IoT%')
                ->orWhere('category', 'like', '%Kotak Kas%')
                ->sum('amount');

            return view('dashboard.bendahara', compact(
                'user', 'totalKas', 'totalPemasukan', 'totalPengeluaran',
                'pemasukanBulanIni', 'pengeluaranBulanIni', 'pendingPayments', 'activeIuranTypes',
                'recentTransactions', 'recentAnnouncements',
                'iotDeviceCount', 'iotOnlineCount', 'iotAlertCount', 'recentIotLogs', 'iotKasMasuk'
            ));
        }

        if ($user->isRt()) {
            $wargaList = User::where('role', 'warga')->get();
            $totalWarga = $wargaList->count();
            
            // Payment status for current month
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;
            $paidUserIds = IuranPayment::where('period_month', $currentMonth)
                ->where('period_year', $currentYear)
                ->where('status', 'approved')
                ->pluck('user_id')
                ->toArray();

            $wargaStatusList = $wargaList->map(function ($w) use ($paidUserIds) {
                $w->has_paid = in_array($w->id, $paidUserIds);
                return $w;
            });

            return view('dashboard.rt', compact(
                'user', 'totalKas', 'totalPemasukan', 'totalPengeluaran',
                'pemasukanBulanIni', 'pengeluaranBulanIni', 'totalWarga', 'wargaStatusList',
                'recentTransactions', 'recentAnnouncements', 'pendingPaymentsCount',
                'iotDeviceCount', 'iotOnlineCount', 'iotAlertCount', 'recentIotLogs'
            ));
        }

        // Warga Dashboard
        $myPayments = IuranPayment::with('iuranType')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $activeIuranTypes = IuranType::where('is_active', true)->get();

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $hasPaidThisMonth = IuranPayment::where('user_id', $user->id)
            ->where('period_month', $currentMonth)
            ->where('period_year', $currentYear)
            ->where('status', 'approved')
            ->exists();

        $myRfidCard = IotRfidCard::where('user_id', $user->id)->first();
        $myRecentLogs = IotTelemetryLog::where('user_id', $user->id)->latest()->take(5)->get();

        return view('dashboard.warga', compact(
            'user', 'totalKas', 'pemasukanBulanIni', 'pengeluaranBulanIni',
            'myPayments', 'activeIuranTypes', 'hasPaidThisMonth', 'recentAnnouncements',
            'recentTransactions', 'myRfidCard', 'myRecentLogs'
        ));
    }
}
