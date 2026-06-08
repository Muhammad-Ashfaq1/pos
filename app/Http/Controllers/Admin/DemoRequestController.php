<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DemoRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\DemoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DemoRequestController extends Controller
{
    public function index(): View
    {
        $requests = DemoRequest::query()
            ->with('handler')
            ->latest()
            ->get();

        $stats = [
            'total' => $requests->count(),
            'new' => $requests->where('status', DemoRequestStatus::New)->count(),
            'scheduled' => $requests->where('status', DemoRequestStatus::Scheduled)->count(),
            'closed' => $requests->where('status', DemoRequestStatus::Closed)->count(),
        ];

        return view('admin.demo-requests.index', compact('requests', 'stats'));
    }

    public function updateStatus(Request $request, DemoRequest $demoRequest): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(DemoRequestStatus::class)],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $status = DemoRequestStatus::from($validated['status']);

        $demoRequest->update([
            'status' => $status,
            'admin_notes' => $validated['admin_notes'] ?? $demoRequest->admin_notes,
            'handled_by' => auth()->id(),
            'handled_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demo request updated.',
            'status' => $status->value,
            'status_label' => $status->label(),
            'badge_class' => 'bg-label-' . $status->badgeClass(),
        ]);
    }

    public function destroy(DemoRequest $demoRequest): JsonResponse
    {
        $demoRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Demo request removed.',
        ]);
    }
}
