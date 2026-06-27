<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedController extends Controller
{
    public function index(): View
    {
        return view('admin.feeds.index', [
            'feeds' => Feed::orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'url'  => ['required', 'url', 'max:500'],
        ]);

        Feed::create($data + ['ativo' => true]);

        return back()->with('success', 'Feed adicionado.');
    }

    public function update(Request $request, Feed $feed): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'url'  => ['required', 'url', 'max:500'],
        ]);

        $feed->update($data);

        return back()->with('success', 'Feed atualizado.');
    }

    public function destroy(Feed $feed): RedirectResponse
    {
        $feed->delete();
        return back()->with('success', 'Feed removido.');
    }

    public function toggle(Feed $feed): RedirectResponse
    {
        $feed->update(['ativo' => ! $feed->ativo]);
        return back();
    }
}
