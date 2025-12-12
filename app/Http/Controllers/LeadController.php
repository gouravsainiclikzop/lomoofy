<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Http\Requests\StoreLeadActivityRequest;
use App\Http\Requests\StoreLeadReminderRequest;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadReminder;
use App\Models\LeadTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    /**
     * Display the leads management page or return JSON data for AJAX requests.
     */
    public function index(Request $request)
    {
        // If AJAX request, return JSON data
        if ($request->ajax()) {
            $query = Lead::withRelations();

            // Search
            if ($request->has('search') && !empty($request->search)) {
                $query->search($request->search);
            }

            // Filters
            if ($request->has('status_id') && $request->status_id) {
                $query->byStatus($request->status_id);
            }

            if ($request->has('source_id') && $request->source_id) {
                $query->bySource($request->source_id);
            }

            if ($request->has('priority') && $request->priority) {
                $query->byPriority($request->priority);
            }

            if ($request->has('assigned_to') && $request->assigned_to) {
                $query->byAssignedTo($request->assigned_to);
            }

            // Date range filter
            if ($request->has('date_from') || $request->has('date_to')) {
                $query->dateRange($request->date_from, $request->date_to);
            }

            // Pagination
            $perPage = min((int) ($request->per_page ?? 20), 100);
            $leads = $query->orderBy('created_at', 'desc')->paginate($perPage);

            $data = $leads->map(function ($lead) {
                return [
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'company' => $lead->company,
                    'value' => $lead->value,
                    'status' => $lead->status ? [
                        'id' => $lead->status->id,
                        'name' => $lead->status->name,
                        'color' => $lead->status->color,
                    ] : null,
                    'source' => $lead->source ? [
                        'id' => $lead->source->id,
                        'name' => $lead->source->name,
                    ] : null,
                    'priority' => $lead->priority,
                    'assigned_to' => $lead->assignedUser ? [
                        'id' => $lead->assignedUser->id,
                        'name' => $lead->assignedUser->name,
                        'email' => $lead->assignedUser->email,
                    ] : null,
                    'tags' => $lead->tags->map(function ($tag) {
                        return [
                            'id' => $tag->id,
                            'name' => $tag->name,
                            'color' => $tag->color,
                        ];
                    }),
                    'description' => $lead->description,
                    'created_at' => $lead->created_at,
                    'updated_at' => $lead->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $leads->currentPage(),
                    'last_page' => $leads->lastPage(),
                    'per_page' => $leads->perPage(),
                    'total' => $leads->total(),
                    'has_more_pages' => $leads->hasMorePages(),
                ]
            ]);
        }

        // Otherwise return the view
        return view('admin.leads.index');
    }

    /**
     * Get master data (statuses, sources, users, tags) for dropdowns.
     */
    public function getMasterData()
    {
        $statuses = DB::table('lead_statuses')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'color']);
        
        $sources = DB::table('lead_sources')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug']);
        
        $users = \App\Models\User::select('id', 'name', 'email')
            ->get();
        
        $tags = DB::table('lead_tags')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'color']);

        return response()->json([
            'success' => true,
            'data' => [
                'statuses' => $statuses,
                'sources' => $sources,
                'users' => $users,
                'tags' => $tags
            ]
        ]);
    }

    /**
     * Store a new lead.
     */
    public function store(StoreLeadRequest $request)
    {
        try {
            DB::beginTransaction();

            $lead = Lead::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'company' => $request->company,
                'value' => $request->value,
                'status_id' => $request->status_id,
                'source_id' => $request->source_id,
                'priority' => $request->priority,
                'assigned_to' => $request->assigned_to,
                'description' => $request->description,
            ]);

            // Sync tags
            if ($request->has('tags') && is_array($request->tags)) {
                $lead->tags()->sync($request->tags);
            }

            DB::commit();

            $lead->load(['status', 'source', 'assignedUser', 'tags']);

            return response()->json([
                'success' => true,
                'message' => 'Lead created successfully',
                'data' => $lead
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create lead: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single lead.
     */
    public function show($id)
    {
        $lead = Lead::withRelations()->find($id);

        if (!$lead) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $lead->id,
                'name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'company' => $lead->company,
                'value' => $lead->value,
                'status' => $lead->status,
                'source' => $lead->source,
                'priority' => $lead->priority,
                'assigned_to' => $lead->assignedUser,
                'tags' => $lead->tags,
                'description' => $lead->description,
                'created_at' => $lead->created_at,
                'updated_at' => $lead->updated_at,
            ]
        ]);
    }

    /**
     * Update a lead.
     */
    public function update(UpdateLeadRequest $request, $id)
    {
        try {
            $lead = Lead::find($id);

            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead not found'
                ], 404);
            }

            DB::beginTransaction();

            $lead->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'company' => $request->company,
                'value' => $request->value,
                'status_id' => $request->status_id,
                'source_id' => $request->source_id,
                'priority' => $request->priority,
                'assigned_to' => $request->assigned_to,
                'description' => $request->description,
            ]);

            // Sync tags
            if ($request->has('tags') && is_array($request->tags)) {
                $lead->tags()->sync($request->tags);
            }

            DB::commit();

            $lead->load(['status', 'source', 'assignedUser', 'tags']);

            return response()->json([
                'success' => true,
                'message' => 'Lead updated successfully',
                'data' => $lead
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update lead: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a lead.
     */
    public function destroy($id)
    {
        try {
            $lead = Lead::find($id);

            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead not found'
                ], 404);
            }

            $lead->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lead deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete lead: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update lead status.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|exists:lead_statuses,id'
        ]);

        try {
            $lead = Lead::find($id);

            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead not found'
                ], 404);
            }

            $lead->update(['status_id' => $request->status_id]);
            $lead->load('status');

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => $lead
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign lead to user.
     */
    public function assign(Request $request, $id)
    {
        $request->validate([
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        try {
            $lead = Lead::find($id);

            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead not found'
                ], 404);
            }

            $lead->update(['assigned_to' => $request->assigned_to]);
            $lead->load('assignedUser');

            return response()->json([
                'success' => true,
                'message' => 'Lead assigned successfully',
                'data' => $lead
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign lead: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update lead priority.
     */
    public function updatePriority(Request $request, $id)
    {
        $request->validate([
            'priority' => 'required|in:low,medium,high'
        ]);

        try {
            $lead = Lead::find($id);

            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead not found'
                ], 404);
            }

            $lead->update(['priority' => $request->priority]);

            return response()->json([
                'success' => true,
                'message' => 'Priority updated successfully',
                'data' => $lead
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update priority: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get activities for a lead.
     */
    public function getActivities($id)
    {
        $lead = Lead::find($id);

        if (!$lead) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found'
            ], 404);
        }

        $activities = $lead->activities()
            ->with(['nextActionOwner', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $activities->map(function ($activity) {
            return [
                'id' => $activity->id,
                'type' => $activity->type,
                'description' => $activity->description,
                'follow_up_date' => $activity->follow_up_date,
                'next_action_owner' => $activity->nextActionOwner ? [
                    'id' => $activity->nextActionOwner->id,
                    'name' => $activity->nextActionOwner->name,
                ] : null,
                'file_path' => $activity->file_path,
                'created_by' => $activity->createdBy ? [
                    'id' => $activity->createdBy->id,
                    'name' => $activity->createdBy->name,
                ] : null,
                'created_at' => $activity->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Store a new activity for a lead.
     */
    public function storeActivity(StoreLeadActivityRequest $request, $id)
    {
        try {
            $lead = Lead::find($id);

            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead not found'
                ], 404);
            }

            $filePath = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filePath = $file->store('lead-activities', 'public');
            }

            $activity = LeadActivity::create([
                'lead_id' => $lead->id,
                'type' => $request->type,
                'description' => $request->description,
                'follow_up_date' => $request->follow_up_date,
                'next_action_owner' => $request->next_action_owner,
                'file_path' => $filePath,
                'created_by' => Auth::id(),
            ]);

            $activity->load(['nextActionOwner', 'createdBy']);

            return response()->json([
                'success' => true,
                'message' => 'Activity added successfully',
                'data' => $activity
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add activity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new reminder for a lead.
     */
    public function storeFollowup(StoreLeadReminderRequest $request, $id)
    {
        try {
            $lead = Lead::find($id);

            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead not found'
                ], 404);
            }

            $reminder = LeadReminder::create([
                'lead_id' => $lead->id,
                'reminder_note' => $request->reminder_note,
                'reminder_date' => $request->reminder_date,
                'assigned_to' => $request->assigned_to ?? Auth::id(),
                'created_by' => Auth::id(),
            ]);

            $reminder->load(['assignedTo', 'createdBy']);

            return response()->json([
                'success' => true,
                'message' => 'Reminder created successfully',
                'data' => $reminder
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create reminder: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete leads.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:leads,id'
        ]);

        try {
            Lead::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' => count($request->ids) . ' lead(s) deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete leads: ' . $e->getMessage()
            ], 500);
        }
    }
}
