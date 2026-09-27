<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class EmployeeDocumentController extends Controller
{
    /**
     * Store an employee document.
     */
    public function store(Request $request, $employeeId)
    {
        $employee = Employee::findOrFail($employeeId);

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'document_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,webp,zip|max:10240',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:issue_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('document_file');
        $ext = $file->getClientOriginalExtension();
        $sizeBytes = $file->getSize();
        $filename = time() . '_' . uniqid() . '.' . $ext;

        $targetDir = public_path('uploads/documents/' . $employee->id);
        if (!File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true, true);
        }

        $file->move($targetDir, $filename);
        $relativePath = 'uploads/documents/' . $employee->id . '/' . $filename;

        $doc = EmployeeDocument::create([
            'employee_id' => $employee->id,
            'title' => $request->title,
            'category' => $request->category,
            'file_path' => $relativePath,
            'file_extension' => strtolower($ext),
            'file_size_bytes' => $sizeBytes,
            'issue_date' => $request->issue_date,
            'expiry_date' => $request->expiry_date,
            'notes' => $request->notes,
            'status' => 'active',
            'uploaded_by' => auth()->check() ? auth()->user()->name : 'Admin',
        ]);

        EmployeeHistory::log(
            $employee->id,
            'status_change',
            'Document Uploaded',
            "Uploaded document: \"{$doc->title}\" ({$doc->category})."
        );

        return back()->with('success', "Document \"{$doc->title}\" uploaded successfully.");
    }

    /**
     * Download document file.
     */
    public function download($id)
    {
        $doc = EmployeeDocument::findOrFail($id);
        $fullPath = public_path($doc->file_path);

        if (!File::exists($fullPath)) {
            return back()->with('error', 'Document file does not exist on disk.');
        }

        return response()->download($fullPath, $doc->title . '.' . $doc->file_extension);
    }

    /**
     * Delete document.
     */
    public function destroy($id)
    {
        $doc = EmployeeDocument::findOrFail($id);
        $employeeId = $doc->employee_id;
        $title = $doc->title;

        $fullPath = public_path($doc->file_path);
        if (File::exists($fullPath)) {
            @unlink($fullPath);
        }

        $doc->delete();

        EmployeeHistory::log(
            $employeeId,
            'status_change',
            'Document Removed',
            "Deleted document: \"{$title}\"."
        );

        return back()->with('success', "Document \"{$title}\" deleted successfully.");
    }
}
