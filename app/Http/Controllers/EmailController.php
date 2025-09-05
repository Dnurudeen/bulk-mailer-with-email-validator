<?php

namespace App\Http\Controllers;

use App\Jobs\ValidateEmail;
use App\Models\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmailController extends Controller
{
    public function index()
    {
        $emails = Email::latest()->paginate(20);
        return view('emails.index', compact('emails'));
    }

    // upload CSV
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        if ($handle === false) return back()->with('error', 'Cannot read file.');

        while (($line = fgetcsv($handle, 0, ',')) !== false) {
            $candidate = trim($line[0] ?? '');
            if (!$candidate) continue;

            // insert or ignore duplicates
            $validator = Validator::make(['email' => $candidate], ['email' => 'required|email|max:255']);
            if ($validator->fails()) continue;

            $email = Email::firstOrCreate(['email' => $candidate], ['status' => 'pending']);
            // dispatch job only when newly created or still pending
            if ($email->status === 'pending') {
                ValidateEmail::dispatch($email)->onQueue('default');
            }
        }
        fclose($handle);

        return back()->with('success', 'File queued for validation.');
    }

    // poll statuses (AJAX)
    public function statuses()
    {
        $counts = Email::selectRaw("status, count(*) as total")->groupBy('status')->get();
        return response()->json($counts);
    }

    // export CSV results
    public function export()
    {
        $filename = 'emails_export_' . date('Ymd_His') . '.csv';
        $emails = Email::orderBy('id')->get();

        $response = new StreamedResponse(function () use ($emails) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['email', 'status', 'reason', 'is_disposable', 'created_at']);
            foreach ($emails as $e) {
                fputcsv($handle, [$e->email, $e->status, $e->reason, $e->is_disposable ? 'yes' : 'no', $e->created_at]);
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename={$filename}");
        return $response;
    }

    public function clear()
    {
        \DB::table('emails')->truncate(); // deletes all rows & resets IDs
        // Or if you prefer to keep IDs continuing:
        // \App\Models\Email::query()->delete();

        return redirect()->route('emails.index')->with('success', 'All emails have been deleted successfully.');
    }
}
