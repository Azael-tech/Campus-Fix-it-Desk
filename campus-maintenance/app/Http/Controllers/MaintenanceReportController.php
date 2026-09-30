<?php

namespace App\Http\Controllers;

use App\Mail\ReportInProgress;
use App\Mail\ReportResolved;
use App\Models\MaintenanceReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaintenanceReportController extends Controller
{
    /** READ (list): with search and filters */
    public function index(Request $request): View
    {
        $reports = MaintenanceReport::query()
            ->search($request->query('search'))
            ->status($request->query('status'))
            ->priority($request->query('priority'))
            ->category($request->query('category'))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        $counts = MaintenanceReport::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'all'         => $counts->sum(),
            'pending'     => $counts['pending'] ?? 0,
            'in_progress' => $counts['in_progress'] ?? 0,
            'resolved'    => $counts['resolved'] ?? 0,
        ];

        return view('reports.index', compact('reports', 'stats'));
    }

    /** CREATE (form) */
    public function create(): View
    {
        return view('reports.create');
    }

    /** CREATE (save) */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['status'] = 'pending';
        $data['photo']  = $request->hasFile('photo')
            ? $request->file('photo')->store('report-photos', 'public')
            : null;

        $report = MaintenanceReport::create($data);

        return redirect()
            ->route('reports.show', $report)
            ->with('success', "Thanks! Your report {$report->reference} was sent to the maintenance team.");
    }

    /** READ (single) */
    public function show(MaintenanceReport $report): View
    {
        return view('reports.show', compact('report'));
    }

    /** UPDATE (form): staff only */
    public function edit(MaintenanceReport $report): View
    {
        return view('reports.edit', compact('report'));
    }

    /** UPDATE (save): staff only */
    public function update(Request $request, MaintenanceReport $report): RedirectResponse
    {
        $data = $request->validate($this->rules(true));
        $previousStatus = $report->status;

        $data['resolved_at'] = $data['status'] === 'resolved'
            ? ($report->resolved_at ?? now())
            : null;

        $removePhoto = $request->boolean('remove_photo');
        unset($data['remove_photo']);

        if ($request->hasFile('photo')) {
            $this->deletePhoto($report);
            $data['photo'] = $request->file('photo')->store('report-photos', 'public');
        } elseif ($removePhoto) {
            $this->deletePhoto($report);
            $data['photo'] = null;
        } else {
            unset($data['photo']);
        }

        $report->update($data);

        $message = "Report {$report->reference} was updated.";
        if ($this->notifyReporter($report, $previousStatus)) {
            $message .= ' The reporter was emailed.';
        }

        return redirect()->route('reports.show', $report)->with('success', $message);
    }

    /** UPDATE (quick status change): staff only */
    public function updateStatus(Request $request, MaintenanceReport $report): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(MaintenanceReport::STATUSES))],
        ]);

        $previousStatus = $report->status;

        $report->update([
            'status'      => $data['status'],
            'resolved_at' => $data['status'] === 'resolved' ? ($report->resolved_at ?? now()) : null,
        ]);

        $message = "Status changed to {$report->status_label}.";
        if ($this->notifyReporter($report, $previousStatus)) {
            $message .= ' The reporter was emailed.';
        }

        return back()->with('success', $message);
    }

    /** DELETE: staff only */
    public function destroy(MaintenanceReport $report): RedirectResponse
    {
        $reference = $report->reference;
        $this->deletePhoto($report);
        $report->delete();

        return redirect()
            ->route('reports.index')
            ->with('success', "Report {$reference} was deleted.");
    }

    /** Monthly summary for the school administrator: staff only */
    public function summary(Request $request): View
    {
        try {
            $start = Carbon::createFromFormat('!Y-m', (string) $request->query('month', now()->format('Y-m')))
                ->startOfMonth();
        } catch (\Throwable $e) {
            $start = now()->startOfMonth();
        }
        $end = $start->copy()->endOfMonth();

        $reports = MaintenanceReport::whereBetween('created_at', [$start, $end])
            ->orderBy('created_at')
            ->get();

        $fixedThisMonth = MaintenanceReport::whereBetween('resolved_at', [$start, $end])->get();

        $avgHours = $fixedThisMonth->avg(fn ($r) => abs($r->created_at->diffInHours($r->resolved_at)));

        $byStatus   = $reports->countBy('status');
        $byPriority = $reports->countBy('priority');

        $statusRows = collect(MaintenanceReport::STATUSES)
            ->mapWithKeys(fn ($label, $key) => [$label => $byStatus->get($key, 0)])
            ->all();

        $priorityRows = collect(MaintenanceReport::PRIORITIES)
            ->mapWithKeys(fn ($label, $key) => [$label => $byPriority->get($key, 0)])
            ->all();

        return view('reports.summary', [
            'month'        => $start->format('Y-m'),
            'monthLabel'   => $start->format('F Y'),
            'prevMonth'    => $start->copy()->subMonth()->format('Y-m'),
            'nextMonth'    => $start->copy()->addMonth()->format('Y-m'),
            'reports'      => $reports,
            'fixedCount'   => $fixedThisMonth->count(),
            'openNow'      => MaintenanceReport::where('status', '!=', 'resolved')->count(),
            'avgDays'      => $avgHours !== null ? round($avgHours / 24, 1) : null,
            'statusRows'   => $statusRows,
            'priorityRows' => $priorityRows,
            'categoryRows' => $reports->countBy('category')->sortDesc()->all(),
            'buildingRows' => $reports->countBy('building')->sortDesc()->all(),
        ]);
    }

    /* ---------- Helpers ---------- */

    /**
     * Email the reporter when the status changes to In progress or Resolved.
     * Returns true if an email was sent.
     */
    private function notifyReporter(MaintenanceReport $report, string $previousStatus): bool
    {
        // No email address, or the status did not actually change: send nothing.
        if (! $report->reporter_email || $report->status === $previousStatus) {
            return false;
        }

        $mail = match ($report->status) {
            'in_progress' => new ReportInProgress($report),
            'resolved'    => new ReportResolved($report),
            default       => null,
        };

        if ($mail === null) {
            return false;
        }

        try {
            Mail::to($report->reporter_email)->send($mail);
            return true;
        } catch (\Throwable $e) {
            Log::warning('Could not send the status email: ' . $e->getMessage());
            return false;
        }
    }

    private function deletePhoto(MaintenanceReport $report): void
    {
        if ($report->photo) {
            Storage::disk('public')->delete($report->photo);
        }
    }

    private function rules(bool $withStatus = false): array
    {
        $rules = [
            'reporter_name'  => ['required', 'string', 'max:100'],
            'reporter_role'  => ['required', Rule::in(MaintenanceReport::ROLES)],
            'reporter_email' => ['nullable', 'email', 'max:150'],
            'building'       => ['required', 'string', 'max:100'],
            'room'           => ['required', 'string', 'max:50'],
            'category'       => ['required', Rule::in(MaintenanceReport::CATEGORIES)],
            'title'          => ['required', 'string', 'max:120'],
            'description'    => ['required', 'string', 'min:10', 'max:1000'],
            'photo'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'priority'       => ['required', Rule::in(array_keys(MaintenanceReport::PRIORITIES))],
        ];

        if ($withStatus) {
            $rules['status']       = ['required', Rule::in(array_keys(MaintenanceReport::STATUSES))];
            $rules['assigned_to']  = ['nullable', 'string', 'max:100'];
            $rules['remove_photo'] = ['nullable', 'boolean'];
        }

        return $rules;
    }
}