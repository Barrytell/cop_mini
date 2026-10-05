<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\MeetingStatus;
use App\Http\Controllers\Admin\Concerns\AuthorizesAdminPermission;
use App\Http\Controllers\Controller;
use App\Modules\Meetings\Models\Meeting;
use App\Services\AuditLogService;
use App\Support\AdminPermission;
use App\Support\CsvExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MeetingController extends Controller
{
    use AuthorizesAdminPermission;

    public function index(): View
    {
        $this->requirePermission(AdminPermission::COMMUNICATIONS);

        return view('admin.meetings.index', [
            'meetings' => Meeting::query()->latest('starts_at')->paginate(20),
        ]);
    }

    public function create(): View
    {
        $this->requirePermission(AdminPermission::COMMUNICATIONS);

        return view('admin.meetings.form', ['meeting' => new Meeting(['status' => MeetingStatus::Scheduled])]);
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::COMMUNICATIONS);
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;
        $meeting = Meeting::query()->create($data);
        $audit->record($request->user(), 'admin.meeting.created', $meeting, null, $meeting->only(['title', 'starts_at']), $request);

        return redirect()->route('admin.meetings.index')->with('status', 'Meeting created.');
    }

    public function edit(Meeting $meeting): View
    {
        $this->requirePermission(AdminPermission::COMMUNICATIONS);

        return view('admin.meetings.form', ['meeting' => $meeting]);
    }

    public function update(Request $request, Meeting $meeting, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::COMMUNICATIONS);
        $previous = $meeting->only(['title', 'starts_at', 'status', 'meeting_url']);
        $meeting->fill($this->validated($request))->save();
        $audit->record($request->user(), 'admin.meeting.updated', $meeting, $previous, $meeting->only(['title', 'starts_at', 'status', 'meeting_url']), $request);

        return redirect()->route('admin.meetings.index')->with('status', 'Meeting updated.');
    }

    public function rsvps(Meeting $meeting): View|StreamedResponse
    {
        $this->requirePermission(AdminPermission::COMMUNICATIONS);
        $meeting->load(['rsvps.user']);

        if (request('export') === 'csv') {
            $rows = $meeting->rsvps->map(fn ($rsvp) => [
                $rsvp->user?->member_no,
                $rsvp->user?->name,
                $rsvp->user?->email,
                $rsvp->attending ? 'yes' : 'no',
                $rsvp->created_at?->toDateTimeString(),
            ]);

            return CsvExporter::download('meeting-'.$meeting->id.'-rsvp.csv', [
                'Member no', 'Name', 'Email', 'RSVP', 'At',
            ], $rows);
        }

        return view('admin.meetings.rsvps', ['meeting' => $meeting]);
    }

    public function destroy(Request $request, Meeting $meeting, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::COMMUNICATIONS);
        $meeting->delete();
        $audit->record($request->user(), 'admin.meeting.deleted', $meeting, null, ['id' => $meeting->id], $request);

        return back()->with('status', 'Meeting deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:160'],
            'meeting_url' => ['nullable', 'string', 'max:500', 'url:http,https'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'status' => ['required', 'in:scheduled,completed,cancelled'],
        ]);
    }
}
