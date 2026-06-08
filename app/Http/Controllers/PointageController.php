<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;

class PointageController extends Controller
{
    public function index(Request $request)
    {
        $user         = auth()->user();
        $selectedDate = $request->get('date', Carbon::today()->toDateString());

        $entree = Attendance::where('user_id', $user->id)
            ->where('type', 'entree')
            ->whereDate('pointage_at', $selectedDate)
            ->first();

        $sortie = Attendance::where('user_id', $user->id)
            ->where('type', 'sortie')
            ->whereDate('pointage_at', $selectedDate)
            ->first();

        $historique = Attendance::where('user_id', $user->id)
            ->whereYear('pointage_at', Carbon::now()->year)
            ->orderBy('pointage_at', 'desc')
            ->get()
            ->groupBy(fn($a) => Carbon::parse($a->pointage_at)->toDateString());

        return view('employers.pointage', compact('entree', 'sortie', 'historique', 'selectedDate'));
    }

    public function adminIndex(Request $request)
    {
        $selectedDate         = $request->get('date', Carbon::today()->toDateString());
        $selectedEmployerName = $request->get('employer_name');

        $query = Attendance::with('user')
            ->whereDate('pointage_at', $selectedDate)
            ->whereHas('user', function ($q) use ($selectedEmployerName) {
                if ($selectedEmployerName) {
                    $q->where(function ($q2) use ($selectedEmployerName) {
                        $q2->where('last_name', 'like', "%$selectedEmployerName%")
                           ->orWhere('first_name', 'like', "%$selectedEmployerName%");
                    });
                }
            });

        $attendances = $query->orderBy('user_id')->get()
            ->groupBy('user_id')
            ->map(function ($records) {
                return [
                    'entree' => $records->where('type', 'entree')->first(),
                    'sortie' => $records->where('type', 'sortie')->first(),
                    'user'   => $records->first()->user,
                ];
            });

        $employers = User::role('employer')->orderBy('last_name')->get();

        return view('pointage.admin', compact(
            'attendances',
            'employers',
            'selectedDate',
            'selectedEmployerName'
        ));
    }

    public function checkInMatin()
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();

        $dejaPoinste = Attendance::where('user_id', $user->id)
            ->where('type', 'entree')
            ->whereDate('pointage_at', $today)
            ->exists();

        if ($dejaPoinste) {
            return back()->with('error', 'Entrée déjà enregistrée aujourd\'hui.');
        }

        Attendance::create([
            'user_id'           => $user->id,
            'type'              => 'entree',
            'pointage_at'       => Carbon::now(),
            'shift_user_id'     => null,
            'face_matched'      => false,
            'blockchain_statut' => 'pending',
        ]);

        return back()->with('status', 'Entrée enregistrée à ' . Carbon::now()->format('H:i:s'));
    }

    public function checkOutMatin()
    {
        $user  = auth()->user();
        $today = Carbon::today()->toDateString();

        $entree = Attendance::where('user_id', $user->id)
            ->where('type', 'entree')
            ->whereDate('pointage_at', $today)
            ->first();

        if (!$entree) {
            return back()->with('error', 'Vous devez d\'abord enregistrer votre entrée.');
        }

        $dejaSorti = Attendance::where('user_id', $user->id)
            ->where('type', 'sortie')
            ->whereDate('pointage_at', $today)
            ->exists();

        if ($dejaSorti) {
            return back()->with('error', 'Sortie déjà enregistrée aujourd\'hui.');
        }

        Attendance::create([
            'user_id'           => $user->id,
            'type'              => 'sortie',
            'pointage_at'       => Carbon::now(),
            'shift_user_id'     => null,
            'face_matched'      => false,
            'blockchain_statut' => 'pending',
        ]);

        return back()->with('status', 'Sortie enregistrée à ' . Carbon::now()->format('H:i:s'));
    }

    public function checkInApresMidi()
    {
        return $this->checkInMatin();
    }

    public function checkOutApresMidi()
    {
        return $this->checkOutMatin();
    }
}