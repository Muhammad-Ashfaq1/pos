<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreDemoRequestRequest;
use App\Models\DemoRequest;
use Illuminate\Http\RedirectResponse;

class DemoRequestController extends Controller
{
    public function store(StoreDemoRequestRequest $request): RedirectResponse
    {
        DemoRequest::create([
            ...$request->validated(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->to(url()->previous() . '#contact')
            ->with('demo_success', __('home.demo_modal.success'));
    }
}
