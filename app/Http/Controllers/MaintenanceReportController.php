<?php

namespace App\Http\Controllers;

use App\Mail\ReportInProgress;
use App\Mail\ReportResolved;
use App\Models\MaintenanceReport;
use App\Models\User;
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
            ->when($request->boolean('mine') && auth()->check(), fn ($q) => $q->where('user_id', auth()->id()))
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
        $data['user_id'] = auth()->id(); // null when a guest sends the report
        $data['photo']  = $request->hasFile('photo')
            ? $request->file('photo')->store('report-photos', 'public')
            : null;

        // Give the report to the staff member who handles this type of problem
        $staff = $this->findStaffFor($data['category']);
        $data['assigned_user_id'] = $staff?->id;
        $data['assigned_to']      = $staff?->name;

        $report = MaintenanceReport::create($data);

        $message = "Thanks! Your report {$report->reference} was sent to the maintenance team.";
        if ($staff) {
            $message .= " {$staff->name} will handle it.";
        }

        return redirect()->route('reports.show', $report)->with('success', $message);
    }

    /** READ (single) */
    public function show(MaintenanceReport $report): View
    {
        return view('reports.show', compact('report'));
    }

    /** UPDATE (form): staff only */
    public function edit(MaintenanceReport $report): View
    {
        $staffMembers = User::where('role', 'staff')->orderBy('name')->get(['id', 'name', 'specialty']);

        return view('reports.edit', compact('report', 'staffMembers'));
    }

    /** UPDATE (save): staff only */
    public function update(Request $request, MaintenanceReport $report): RedirectResponse
    {
        $data = $request->validate($this->rules(true));
        $previousStatus = $report->status;

        
        // "Assigned to" is a staff member chosen from the list
        $assignee = filled($data['assigned_user_id'] ?? null) ? User::find($data['assigned_user_id']) : null;
        $data['assigned_user_id'] = $assignee?->id;
        $data['assigned_to']      = $assignee?->name;

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

    /** The staff member who handles this type of problem (the one with the fewest open reports). */
    private function findStaffFor(string $category): ?User
    {
        return User::where('role', 'staff')
            ->where('specialty', $category)
            ->orderByRaw(
                '(select count(*) from maintenance_reports where maintenance_reports.assigned_user_id = users.id and maintenance_reports.status <> ?)',
                ['resolved']
            )
            ->orderBy('id')
            ->first();
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
            $rules['status'] = ['required', Rule::in(array_keys(MaintenanceReport::STATUSES))];
            $rules['assigned_user_id'] = ['nullable', Rule::exists('users', 'id')->where('role', 'staff')];
            $rules['remove_photo'] = ['nullable', 'boolean'];
        }

        return $rules;
    }
}