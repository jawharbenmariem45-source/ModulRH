<?php

namespace App\Http\Controllers;

use App\Models\shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::paginate(10);
        return view('shifts.index', compact('shifts'));
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

        Shift::create($request->only('name', 'start_time', 'end_time', 'break_start', 'break_end'));

        return redirect()->route('shifts.index')
            ->with('success_message', 'Horaire ajouté avec succès.');
    }

    public function update(Request $request, Shift $schedule)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'start_time'  => 'required',
            'end_time'    => 'required',
            'break_start' => 'nullable',
            'break_end'   => 'nullable',
        ]);

        $schedule->update($request->only('name', 'start_time', 'end_time', 'break_start', 'break_end'));

        return redirect()->route('shifts.index')
            ->with('success_message', 'Horaire mis à jour.');
    }

    public function destroy(Shift $schedule)
    {
        $schedule->delete();
        return redirect()->route('shifts.index')
            ->with('success_message', 'Horaire supprimé.');
    }
}