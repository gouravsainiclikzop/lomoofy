<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    /**
     * Display contacts listing page
     */
    public function index()
    {
        return view('admin.contacts.index');
    }

    /**
     * Get contacts data for DataTables
     */
    public function getData(Request $request)
    {
        $query = Contact::query();

        // Search
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Filter by read status
        if ($request->filled('is_read')) {
            $query->where('is_read', $request->is_read === 'true');
        }

        $totalRecords = $query->count();
        
        $contacts = $query->orderBy('created_at', 'desc')
                          ->skip($request->start ?? 0)
                          ->take($request->length ?? 10)
                          ->get();

        $data = $contacts->map(function($contact) {
            return [
                'id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
                'subject' => $contact->subject ?? 'No Subject',
                'message' => Str::limit($contact->message, 100),
                'is_read' => $contact->is_read,
                'created_at' => $contact->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'draw' => intval($request->draw ?? 1),
            'recordsTotal' => Contact::count(),
            'recordsFiltered' => $totalRecords,
            'data' => $data,
        ]);
    }

    /**
     * Show a specific contact
     */
    public function show($id)
    {
        $contact = Contact::findOrFail($id);
        
        // Mark as read when viewing
        if (!$contact->is_read) {
            $contact->update(['is_read' => true]);
        }
        
        return response()->json([
            'success' => true,
            'data' => $contact
        ]);
    }

    /**
     * Toggle read status
     */
    public function toggleRead($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->update(['is_read' => !$contact->is_read]);
        
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'is_read' => $contact->is_read
        ]);
    }

    /**
     * Delete a contact
     */
    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Contact deleted successfully'
        ]);
    }
}
