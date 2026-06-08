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

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $selectedDate)
            ->first();

        $historique = Attendance::where('user_id', $user->id)
            ->whereYear('date', Carbon::now()->year)
            ->orderBy('date', 'desc')
            ->get();

        return view('employers.pointage', compact('attendance', 'historique', 'selectedDate'));
    }

    public function adminIndex(Request $request)
    {
        $selectedDate         = $request->get('date', Carbon::today()->toDateString());
        $selectedEmployerName = $request->get('employer_name');

        $query = Attendance::with('user')
            ->where('date', $selectedDate)
            ->whereHas('user', function ($q) use ($selectedEmployerName) {
                if ($selectedEmployerName) {
                    $q->where(function ($q2) use ($selectedEmployerName) {
                        $q2->where('last_name', 'like', "%$selectedEmployerName%")
                           ->orWhere('first_name', 'like', "%$selectedEmployerName%");
                    });
                }
            });

        $attendances = $query->orderBy('user_id')->get();
        $employers   = User::role('employer')->orderBy('last_name')->get();

        return view('pointage.admin', compact(
            'attendances',
            'employers',
            'selectedDate',
            'selectedEmployerName'
        ));
    }

    private function getTodayAttendance()
    {
        $user  = auth()->user();
        $today = Carbon::today()->toDateString();

        return Attendance::firstOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            ['status' => 'present']
        );
    }

    public function checkInMatin()
    {
        $now        = Carbon::now();
        $attendance = $this->getTodayAttendance();

        if ($attendance->morning_check_in) {
            return back()->with('error', 'Check-in matin déjà enregistré.');
        }

        $attendance->update(['morning_check_in' => $now]);
        return back()->with('status', 'Check-in matin enregistré à ' . $now->format('H:i:s'));
    }

    public function checkOutMatin()
    {
        $now        = Carbon::now();
        $attendance = $this->getTodayAttendance();

        if (!$attendance->morning_check_in) {
            return back()->with('error', 'Vous devez d\'abord faire le check-in matin.');
        }

        $attendance->update(['morning_check_out' => $now]);
        return back()->with('status', 'Check-out matin enregistré à ' . $now->format('H:i:s'));
    }

    public function checkInApresMidi()
    {
        $now        = Carbon::now();
        $attendance = $this->getTodayAttendance();

        if ($attendance->afternoon_check_in) {
            return back()->with('error', 'Check-in après-midi déjà enregistré.');
        }

        $attendance->update(['afternoon_check_in' => $now]);
        return back()->with('status', 'Check-in après-midi enregistré à ' . $now->format('H:i:s'));
    }

    public function checkOutApresMidi()
    {
        $now        = Carbon::now();
        $attendance = $this->getTodayAttendance();

        if (!$attendance->afternoon_check_in) {
            return back()->with('error', 'Vous devez d\'abord faire le check-in après-midi.');
        }

        $attendance->update(['afternoon_check_out' => $now]);
        return back()->with('status', 'Check-out après-midi enregistré à ' . $now->format('H:i:s'));
    }
}