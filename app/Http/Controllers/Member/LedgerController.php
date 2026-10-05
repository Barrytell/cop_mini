<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Enums\LedgerType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\LedgerStatementRequest;
use App\Modules\Units\Queries\LedgerStatement;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class LedgerController extends Controller
{
    public function index(LedgerStatementRequest $request, LedgerStatement $statement): View
    {
        $entries = $statement->query(
            $request->user(),
            $request->string('type')->toString(),
            $request->string('from')->toString(),
            $request->string('to')->toString(),
        )->paginate(15)->withQueryString();

        return view('member.ledger.index', [
            'entries' => $entries,
            'types' => LedgerType::cases(),
            'filters' => $request->only(['type', 'from', 'to']),
        ]);
    }

    public function export(LedgerStatementRequest $request, LedgerStatement $statement): StreamedResponse
    {
        $entries = $statement->query(
            $request->user(),
            $request->string('type')->toString(),
            $request->string('from')->toString(),
            $request->string('to')->toString(),
        )->cursor();

        return response()->streamDownload(function () use ($entries): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Date', 'Type', 'Units', 'Note']);

            foreach ($entries as $entry) {
                fputcsv($handle, [
                    $entry->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i'),
                    $entry->type->value,
                    (string) $entry->units,
                    $this->csvCell((string) $entry->note),
                ]);
            }

            fclose($handle);
        }, 'units-statement.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function csvCell(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) === 1 ? "'".$value : $value;
    }
}
