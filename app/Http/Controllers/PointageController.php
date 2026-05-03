<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employer;

class PointageController extends Controller
{
    // =============================================
    // VUE EMPLOYÉ — son propre pointage
    // =============================================
    public function index(Request $request)
    {
        $employer = auth('employer')->user();
        $selectedDate = $request->get('date', Carbon::today()->toDateString());

        $attendance = Attendance::where('employer_id', $employer->id)
            ->where('date', $selectedDate)
            ->first();

        $historique = Attendance::where('employer_id', $employer->id)
            ->whereMonth('date', Carbon::now()->month)
            ->orderBy('date', 'desc')
            ->get();

        return view('employers.pointage', compact('attendance', 'historique', 'selectedDate'));
    }

    // =============================================
    // VUE RH/ADMIN — tous les pointages
    // =============================================
    public function adminIndex(Request $request)
    {
        $user                 = auth()->user();
        $selectedDate         = $request->get('date', Carbon::today()->toDateString());
        $selectedEmployerName = $request->get('employer_name');

        $query = Attendance::with('employer')
            ->where('date', $selectedDate)
            ->whereHas('employer', function($q) use ($user, $selectedEmployerName) {
                $q->where('company_id', $user->company_id);
                if ($selectedEmployerName) {
                    $q->where(function($q2) use ($selectedEmployerName) {
                        $q2->where('nom', 'like', "%$selectedEmployerName%")
                           ->orWhere('prenom', 'like', "%$selectedEmployerName%");
                    });
                }
            });

        $attendances = $query->orderBy('employer_id')->get();

        $employers = Employer::where('company_id', $user->company_id)
                             ->orderBy('nom')
                             ->get();

        return view('pointage.admin', compact(
            'attendances',
            'employers',
            'selectedDate',
            'selectedEmployerName'
        ));
    }

    // =============================================
    // HELPERS
    // =============================================
    private function getTodayAttendance()
    {
        $employer = auth('employer')->user();
        $today    = Carbon::today()->toDateString();

        return Attendance::firstOrCreate(
            ['employer_id' => $employer->id, 'date' => $today],
            ['status' => 'present']
        );
    }

    // =============================================
    // ACTIONS POINTAGE
    // =============================================
    public function checkInMatin()
    {
        $now        = Carbon::now();
        $attendance = $this->getTodayAttendance();

        if ($attendance->check_in_morning_time) {
            return back()->with('error', 'Check-in matin déjà enregistré.');
        }

        $attendance->update(['check_in_morning_time' => $now]);
        return back()->with('status', 'Check-in matin enregistré à ' . $now->format('H:i:s'));
    }

    public function checkOutMatin()
    {
        $now        = Carbon::now();
        $attendance = $this->getTodayAttendance();

        if (!$attendance->check_in_morning_time) {
            return back()->with('error', 'Vous devez d\'abord faire le check-in matin.');
        }

        $attendance->update(['check_out_morning_time' => $now]);
        return back()->with('status', 'Check-out matin enregistré à ' . $now->format('H:i:s'));
    }

    public function checkInApresMidi()
    {
        $now        = Carbon::now();
        $attendance = $this->getTodayAttendance();

        if ($attendance->check_in_afternoon_time) {
            return back()->with('error', 'Check-in après-midi déjà enregistré.');
        }

        $attendance->update(['check_in_afternoon_time' => $now]);
        return back()->with('status', 'Check-in après-midi enregistré à ' . $now->format('H:i:s'));
    }

    public function checkOutApresMidi()
    {
        $now        = Carbon::now();
        $attendance = $this->getTodayAttendance();

        if (!$attendance->check_in_afternoon_time) {
            return back()->with('error', 'Vous devez d\'abord faire le check-in après-midi.');
        }

        $attendance->update(['check_out_afternoon_time' => $now]);
        return back()->with('status', 'Check-out après-midi enregistré à ' . $now->format('H:i:s'));
    }
}