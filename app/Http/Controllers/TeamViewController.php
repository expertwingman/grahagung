<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\Project;
use App\Models\Activity;
use Illuminate\Http\Request;

class TeamViewController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Ambil daftar staff yang bisa dilihat
        if ($user->isDirektur()) {
            $staffList = User::where('role', 'staff')
                ->with('manager')
                ->orderBy('name')
                ->get();
        } else {
            // Manajer hanya lihat staff di bawahnya
            $staffList = $user->staffMembers()
                ->where('role', 'staff')
                ->orderBy('name')
                ->get();
        }

        // Staff yang dipilih
        $selectedStaffId = $request->get('staff_id', $staffList->first()?->id);
        $selectedStaff   = $staffList->find($selectedStaffId);

        $staffLeads      = collect();
        $staffPipelines  = collect();
        $staffProjects   = collect();
        $staffActivities = collect();
        $staffStats      = [];

        if ($selectedStaff) {
            $staffLeads = Lead::where('assigned_to', $selectedStaff->id)
                ->with('product')
                ->orderBy('created_at', 'desc')
                ->get();

            $staffPipelines = Pipeline::where('assigned_to', $selectedStaff->id)
                ->with('lead')
                ->orderBy('value', 'desc')
                ->get();

            $staffProjects = Project::where('assigned_to', $selectedStaff->id)
                ->with('lead')
                ->orderBy('created_at', 'desc')
                ->get();

            $staffActivities = Activity::where('created_by', $selectedStaff->id)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            $staffStats = [
                'total_leads'    => $staffLeads->count(),
                'won_leads'      => $staffLeads->where('status', 'closing')->count(),
                'lost_leads'     => $staffLeads->where('status', 'batal')->count(),
                'pipeline_value' => $staffPipelines->whereNotIn('stage', ['won','lost'])->sum('value'),
                'won_value'      => $staffPipelines->where('stage', 'won')->sum('value'),
                'active_projects'=> $staffProjects->whereIn('status', ['planning','in_progress'])->count(),
                'conversion'     => $staffLeads->count() > 0
                    ? round(($staffLeads->where('status','closing')->count() / $staffLeads->count()) * 100)
                    : 0,
            ];
        }

        // Overview semua staff (tab IDE 2)
        $allStaffData = collect();
        foreach ($staffList as $staff) {
            $leads = Lead::where('assigned_to', $staff->id)->get();
            $allStaffData->push([
                'staff'          => $staff,
                'total_leads'    => $leads->count(),
                'new_leads'      => $leads->where('status', 'no_respon')->count(),
                'won_leads'      => $leads->where('status', 'closing')->count(),
                'pipeline_value' => Pipeline::where('assigned_to', $staff->id)->whereNotIn('stage',['won','lost'])->sum('value'),
                'won_value'      => Pipeline::where('assigned_to', $staff->id)->where('stage','won')->sum('value'),
                'conversion'     => $leads->count() > 0
                    ? round(($leads->where('status','closing')->count() / $leads->count()) * 100)
                    : 0,
            ]);
        }

        return view('team.index', compact(
            'staffList', 'selectedStaff', 'selectedStaffId',
            'staffLeads', 'staffPipelines', 'staffProjects',
            'staffActivities', 'staffStats', 'allStaffData'
        ));
    }
}
