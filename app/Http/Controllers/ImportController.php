<?php

namespace App\Http\Controllers;

use App\Imports\LeadsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    public function index()
    {
        return view('import.index');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $file = $request->file('file');
        $data = Excel::toArray([], $file);
        $rows = $data[0] ?? [];

        // Cari header row
        $headerRowIndex = null;
        foreach ($rows as $i => $row) {
            foreach ($row as $cell) {
                if ($cell && (
                    stripos((string)$cell, 'nama_user') !== false ||
                    stripos((string)$cell, 'nama user') !== false ||
                    stripos((string)$cell, 'nama_customer') !== false ||
                    stripos((string)$cell, 'nama customer') !== false ||
                    stripos((string)$cell, 'nama marketing') !== false
                )) {
                    $headerRowIndex = $i;
                    break 2;
                }
            }
        }

        $headers = $headerRowIndex !== null ? $rows[$headerRowIndex] : $rows[0];
        $preview = array_slice($rows, ($headerRowIndex ?? 0) + 1, 10);

        session(['import_path' => $file->store('imports')]);

        return view('import.preview', compact('headers', 'preview'));
    }

    public function import(Request $request)
    {
        $path = session('import_path');
        if (!$path) {
            return redirect()->route('import.index')
                ->with('error', 'File tidak ditemukan. Upload ulang.');
        }

        $import = new LeadsImport();
        // Storage::path() tidak mengasumsikan struktur folder tertentu
        Excel::import($import, Storage::path($path));
        Storage::delete($path);      // jangan biarkan file menumpuk di disk
        session()->forget('import_path');

        $message = "Import selesai! {$import->imported} leads berhasil";
        if ($import->duplicates > 0) {
            $message .= ", {$import->duplicates} duplikat dilewati";
        }
        if ($import->skipped > 0) {
            $message .= ", {$import->skipped} dilewati (data tidak lengkap)";
        }
        $message .= ".";
        if (!empty($import->errors)) {
            $message .= " Errors: " . implode(', ', array_slice($import->errors, 0, 3));
        }

        return redirect()->route('leads.index')
            ->with('success', $message)
            ->with('duplicateDetails', $import->duplicateDetails);
    }
}
