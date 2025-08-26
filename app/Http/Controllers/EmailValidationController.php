<?php

namespace App\Http\Controllers;

use App\Events\ValidationProgressUpdated;
use App\Jobs\ValidateEmail;
use App\Models\EmailValidationBatch;
use App\Models\EmailValidationResult;
use App\Models\Recipient;
use App\Models\RecipientList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmailValidationController extends Controller
{
    public function create()
    {
        return view('validation.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt'],
            'list_name' => ['nullable', 'string', 'max:100'],
        ]);

        $file = $request->file('csv');
        $path = $file->store('email-validation');

        $batch = EmailValidationBatch::create([
            'user_id'       => $request->user()->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_path'   => $path,
            'status'        => 'processing',
        ]);

        // Parse CSV quickly (headers: email,name). You can swap to League\CSV if you like.
        $rows = array_map('str_getcsv', file(Storage::path($path)));
        $header = array_map('trim', array_shift($rows) ?? []);
        $emailIndex = array_search('email', array_map('strtolower', $header));
        $nameIndex  = array_search('name', array_map('strtolower', $header));

        $total = 0;
        foreach ($rows as $r) {
            $email = $emailIndex !== false ? trim($r[$emailIndex] ?? '') : '';
            if (!$email) continue;
            $name  = $nameIndex  !== false ? trim($r[$nameIndex]  ?? '') : null;
            $total++;
            dispatch(new ValidateEmail($batch->id, $email, $name))->onQueue('default');
        }

        $batch->update(['total' => $total]);

        // initial broadcast
        event(new ValidationProgressUpdated($batch->id, [
            'valid'   => 0,
            'invalid' => 0,
            'total'   => $total,
            'status'  => 'processing',
        ], '[' . now()->format('H:i:s') . '] Validation started'));

        return redirect()->route('validation.show', $batch);
    }

    public function show(EmailValidationBatch $batch)
    {
        // $this->authorize('view', $batch); // optional, or ensure user owns it

        $valid   = $batch->results()->where('is_valid', true)->latest()->limit(50)->get();
        $invalid = $batch->results()->where('is_valid', false)->latest()->limit(50)->get();

        return view('validation.show', compact('batch', 'valid', 'invalid'));
    }

    // Finalize: create or update a Recipient List with valid emails
    public function storeList(Request $request, EmailValidationBatch $batch)
    {
        $request->validate(['name' => ['required', 'string', 'max:100']]);

        // $this->authorize('view', $batch);

        $list = RecipientList::firstOrCreate([
            'user_id' => $request->user()->id,
            'name'    => $request->name,
        ]);

        // Upsert recipients, then attach to list
        $valids = $batch->results()->where('is_valid', true)->cursor();

        $recipientIds = [];
        foreach ($valids as $row) {
            $recipient = Recipient::firstOrCreate(
                ['email' => $row->email],
                ['name'  => $row->name]
            );
            $recipientIds[] = $recipient->id;
        }

        if ($recipientIds) {
            $list->recipients()->syncWithoutDetaching($recipientIds);
        }

        // Mark completed if not already
        if ($batch->status !== 'completed') $batch->update(['status' => 'completed']);

        return back()->with('status', 'Recipient list saved!');
    }
}