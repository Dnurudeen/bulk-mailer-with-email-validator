<?php

namespace App\Http\Controllers;

use App\Models\ValidationBatch;
use App\Models\ValidationResult;
use App\Models\Recipient; // your existing recipient model
use App\Models\RecipientList; // if created
use App\Jobs\ValidateEmailJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ValidationController extends Controller
{
    public function index()
    {
        $batches = ValidationBatch::where('user_id', Auth::id())->latest()->paginate(15);
        return view('validation.index', compact('batches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'csv' => 'required|file|mimes:csv,txt',
            'list_name' => 'nullable|string|max:255',
        ]);

        $file = $request->file('csv');
        $filename = time() . '_' . Str::slug($file->getClientOriginalName());
        $path = $file->storeAs('validations', $filename);

        $batch = ValidationBatch::create([
            'user_id' => Auth::id(),
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'status' => 'queued',
        ]);

        // parse CSV - minimal robust parser
        $handle = fopen(Storage::path($path), 'r');
        $rows = [];
        if ($handle !== false) {
            // If file has header, attempt to detect header row
            $header = null;
            $firstRow = fgetcsv($handle);
            if ($firstRow === false) {
                fclose($handle);
                return back()->withErrors(['csv' => 'Empty CSV']);
            }

            // If first row looks like ['email','name'] treat as header
            if ($this->looksLikeHeader($firstRow)) {
                $header = array_map('strtolower', $firstRow);
            } else {
                // treat first row as data
                $rows[] = $firstRow;
            }

            while (($data = fgetcsv($handle)) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }

        // Insert validation_results
        $inserted = [];
        foreach ($rows as $row) {
            // Basic mapping: if header exists, map by column names
            if ($header) {
                $mapped = array_combine($header, $row + array_fill(0, max(0, count($header) - count($row)), null));
                $email = $mapped['email'] ?? null;
                $name = $mapped['name'] ?? null;
            } else {
                $email = $row[0] ?? null;
                $name = $row[1] ?? null;
            }

            if (! $email) continue;

            $res = ValidationResult::create([
                'validation_batch_id' => $batch->id,
                'email' => trim($email),
                'name' => $name ? trim($name) : null,
            ]);
            $inserted[] = $res;
        }

        $batch->update(['total' => count($inserted), 'status' => 'queued']);

        // Dispatch jobs (chunk to avoid enqueuing millions at once)
        $chunkSize = 100;
        collect($inserted)->chunk($chunkSize)->each(function ($chunk) {
            foreach ($chunk as $r) {
                ValidateEmailJob::dispatch($r->validation_batch_id, $r->id);
            }
        });

        return redirect()->route('validation.show', $batch)->with('success', 'CSV uploaded and validation jobs dispatched.');
    }

    protected function looksLikeHeader(array $row): bool
    {
        $lower = array_map('strtolower', $row);
        return in_array('email', $lower, true) || in_array('name', $lower, true);
    }

    public function show(ValidationBatch $batch)
    {
        // $this->authorize('view', $batch); // implement policy or owner-check
        if ($batch->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
        $initialResults = $batch->results()->limit(200)->get()->map(function ($r) {
            return [
                'id' => $r->id,
                'email' => $r->email,
                'name' => $r->name,
                'is_valid' => $r->is_valid,
                'score' => $r->score,
                'state' => $r->state,
                'reason' => $r->reason,
                'checked_at' => $r->checked_at?->toDateTimeString(),
            ];
        });

        $initialStats = [
            'total' => $batch->total,
            'valid' => $batch->valid_count,
            'invalid' => $batch->invalid_count,
            'status' => $batch->status,
        ];

        return view('validation.show', [
            'batch' => $batch,
            'initialResults' => $initialResults,
            'initialStats' => $initialStats,
        ]);
    }

    // Save valid emails as a recipient list (reusable)
    public function saveValidToList(Request $request, ValidationBatch $batch)
    {
        $request->validate(['list_name' => 'required|string|max:255']);

        $validRows = $batch->results()->where('is_valid', true)->get();

        if ($validRows->isEmpty()) {
            return back()->with('error', 'No valid addresses to save.');
        }

        $list = \App\Models\RecipientList::create([
            'user_id' => Auth::id(),
            'name' => $request->input('list_name'),
        ]);

        foreach ($validRows as $r) {
            // create recipient if not exist
            $recipient = \App\Models\Recipient::firstOrCreate(['email' => $r->email], ['name' => $r->name]);
            $list->recipients()->syncWithoutDetaching($recipient->id);
        }

        return back()->with('success', 'Saved valid emails to list: ' . $list->name);
    }
}
