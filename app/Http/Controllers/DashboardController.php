<?php
namespace App\Http\Controllers;

use App\Models\Prediksi;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $total   = Prediksi::count();
        $today   = Prediksi::whereDate('diprediksi_pada', Carbon::today())->count();

        $ya      = Prediksi::where('label_prediksi', 'ya')->count();
        $tidak   = Prediksi::where('label_prediksi', 'tidak')->count();
        $pYa     = $total ? round($ya / $total * 100, 1) : 0.0;

        $proxyOk = Prediksi::where('sesuai_permenkes_proxy', true)->count();
        $pProxy  = $total ? round($proxyOk / $total * 100, 1) : 0.0;

        $latest  = Prediksi::orderByDesc('diprediksi_pada')->take(5)->get();

        return view('dashboard', compact('total','today','ya','tidak','pYa','proxyOk','pProxy','latest'));
    }
}
