<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Departement;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('departement')->paginate(10);
        $departements = Departement::all();
        return view('posts.index', compact('posts', 'departements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departements,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string|max:500',
        ]);

        Post::create($request->only('department_id', 'name', 'description'));

        return redirect()->route('posts.index')
            ->with('success_message', 'Poste ajouté avec succès.');
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'department_id' => 'required|exists:departements,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string|max:500',
        ]);

        $post->update($request->only('department_id', 'name', 'description'));

        return redirect()->route('posts.index')
            ->with('success_message', 'Poste mis à jour.');
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('posts.index')
            ->with('success_message', 'Poste supprimé.');
    }
}