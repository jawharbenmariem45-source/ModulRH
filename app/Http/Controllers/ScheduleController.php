<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::paginate(10);
        return view('schedules.index', compact('schedules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'start_time'  => 'required',
            'end_time'    => 'required',
            'break_start' => 'nullable',
            'break_end'   => 'nullable',
        ]);

        Schedule::create($request->only('name', 'start_time', 'end_time', 'break_start', 'break_end'));

        return redirect()->route('schedules.index')
            ->with('success_message', 'Horaire ajouté avec succès.');
    }

    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'start_time'  => 'required',
            'end_time'    => 'required',
            'break_start' => 'nullable',
            'break_end'   => 'nullable',
        ]);

        $schedule->update($request->only('name', 'start_time', 'end_time', 'break_start', 'break_end'));

        return redirect()->route('schedules.index')
            ->with('success_message', 'Horaire mis à jour.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('schedules.index')
            ->with('success_message', 'Horaire supprimé.');
    }
}