<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TeamViewController; 
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        $user = auth()->user();

        $leadsQuery    = \App\Models\Lead::query();
        $pipelineQuery = \App\Models\Pipeline::query();
        $projectQuery  = \App\Models\Project::query();

        if ($user->isStaff()) {
            $leadsQuery->where('assigned_to', $user->id);
            $pipelineQuery->where('assigned_to', $user->id);
            $projectQuery->where('assigned_to', $user->id);
        } elseif ($user->isManajer()) {
            $staffIds   = $user->staffMembers()->pluck('id')->toArray();
            $staffIds[] = $user->id;
            $leadsQuery->whereIn('assigned_to', $staffIds);
            $pipelineQuery->whereIn('assigned_to', $staffIds);
            $projectQuery->whereIn('assigned_to', $staffIds);
        }

        $totalLeads         = $leadsQuery->count();
        $newLeads           = (clone $leadsQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $activePipelines    = (clone $pipelineQuery)->whereNotIn('stage', ['won','lost'])->count();
        $pipelineValue      = (clone $pipelineQuery)->whereNotIn('stage', ['won','lost'])->sum('value');
        $activeProjects     = (clone $projectQuery)->where('status', 'in_progress')->count();
        $completedProjects  = (clone $projectQuery)->where('status', 'completed')->count();
        $wonDeals           = (clone $pipelineQuery)->where('stage', 'won')->count();
        $wonValue           = (clone $pipelineQuery)->where('stage', 'won')->sum('value');
        $recentLeads        = (clone $leadsQuery)->latest()->take(5)->get();
        $upcomingActivities = \App\Models\Activity::where('status', 'planned')
            ->where('scheduled_at', '>=', now())
            ->when($user->isStaff(), fn($q) => $q->where('created_by', $user->id))
            ->when($user->isManajer(), function ($q) use ($user) {
                $ids   = $user->staffMembers()->pluck('id')->toArray();
                $ids[] = $user->id;
                $q->whereIn('created_by', $ids);
            })
            ->orderBy('scheduled_at')
            ->take(5)->get();

        $leadsPerMonth = (clone $leadsQuery)
            ->selectRaw("TO_CHAR(created_at, 'Mon YY') as month, COUNT(*) as total")
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupByRaw("TO_CHAR(created_at, 'Mon YY')")
            ->orderByRaw("MIN(created_at)")
            ->get();

        $leadsPerSource = (clone $leadsQuery)
            ->selectRaw('source, COUNT(*) as total')
            ->groupBy('source')
            ->orderByRaw('COUNT(*) DESC')
            ->take(7)->get();

        // Lead yang perlu di-follow up — diurutkan dari yang paling lama diam
        $followUpLeads = (clone $leadsQuery)
            ->needsFollowUp()
            ->with('assignedTo', 'product')
            ->orderByRaw('follow_up_date ASC NULLS FIRST')
            ->take(8)->get();
        $followUpCount = (clone $leadsQuery)->needsFollowUp()->count();

        // Lead dari website yang belum ditugaskan ke sales mana pun.
        // Hanya relevan untuk direktur & manajer.
        $webLeads      = collect();
        $webLeadsCount = 0;
        $salesOptions  = collect();

        if ($user->isDirektur() || $user->isManajer()) {
            $webQuery = \App\Models\Lead::where('source', 'website')
                ->whereNull('assigned_to');

            $webLeadsCount = (clone $webQuery)->count();
            $webLeads      = (clone $webQuery)->latest()->take(8)->get();

            $salesOptions = $user->isDirektur()
                ? \App\Models\User::where('is_active', true)
                    ->where('role', 'staff')->orderBy('name')->get()
                : $user->staffMembers()->where('is_active', true)
                    ->where('role', 'staff')->orderBy('name')->get();
        }

        return view('dashboard', compact(
            'webLeads', 'webLeadsCount', 'salesOptions',
            'totalLeads', 'newLeads', 'activePipelines', 'pipelineValue',
            'activeProjects', 'completedProjects', 'wonDeals', 'wonValue',
            'recentLeads', 'upcomingActivities', 'leadsPerMonth', 'leadsPerSource',
            'followUpLeads', 'followUpCount'
        ));
    })->name('dashboard');

    // Halaman lead bentrok (harus SEBELUM resource agar tidak bentrok dgn leads/{lead})
    Route::get('leads/conflicting', [\App\Http\Controllers\ConflictingLeadController::class, 'index'])
        ->name('leads.conflicting');
    Route::get('leads/archived', [LeadController::class, 'archived'])
        ->name('leads.archived');
    Route::post('leads/{id}/restore', [LeadController::class, 'restore'])
        ->name('leads.restore');
    Route::delete('leads/{id}/force-delete', [LeadController::class, 'forceDelete'])
        ->name('leads.force-delete');
    Route::post('leads/{lead}/followed-up', [LeadController::class, 'markFollowedUp'])
        ->name('leads.followed-up');
    Route::post('leads/{lead}/assign', [LeadController::class, 'assign'])
        ->name('leads.assign');
    Route::resource('leads', LeadController::class);
    Route::resource('projects', ProjectController::class);

    Route::get('/pipeline', [PipelineController::class, 'index'])->name('pipeline.index');
    Route::post('/pipeline', [PipelineController::class, 'store'])->name('pipeline.store');
    Route::patch('/pipeline/{pipeline}/stage', [PipelineController::class, 'updateStage'])->name('pipeline.updateStage');
    Route::delete('/pipeline/{pipeline}', [PipelineController::class, 'destroy'])->name('pipeline.destroy');

    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
    Route::get('/activities/create', [ActivityController::class, 'create'])->name('activities.create');
    Route::post('/activities', [ActivityController::class, 'store'])->name('activities.store');
    Route::delete('/activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');
    Route::patch('/activities/{activity}/done', [ActivityController::class, 'markDone'])->name('activities.done');

    Route::get('/analytics', function () {
        $user = auth()->user();

        // Semua query difilter berdasarkan role lewat scope visibleTo()
        $leadQuery     = \App\Models\Lead::visibleTo($user);
        $pipelineQuery = \App\Models\Pipeline::visibleTo($user);
        $projectQuery  = \App\Models\Project::visibleTo($user);

        $totalLeads         = (clone $leadQuery)->count();
        $totalPipelineValue = (clone $pipelineQuery)->sum('value');
        $wonLeads           = (clone $leadQuery)->where('status', 'closing')->count();
        $conversionRate     = $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100) : 0;
        $avgDealValue       = (clone $pipelineQuery)->where('stage', 'won')->avg('value') ?? 0;
        $leadsPerStatus     = (clone $leadQuery)->selectRaw('status, COUNT(*) as total')->groupBy('status')->orderByRaw('COUNT(*) DESC')->get();
        $leadsPerSource     = (clone $leadQuery)->selectRaw('source, COUNT(*) as total')->groupBy('source')->orderByRaw('COUNT(*) DESC')->take(6)->get();
        $pipelinePerStage   = (clone $pipelineQuery)->selectRaw('stage, SUM(value) as total')->groupBy('stage')->orderByRaw('SUM(value) DESC')->get();
        $projectsPerStatus  = (clone $projectQuery)->selectRaw('status, COUNT(*) as total')->groupBy('status')->get();

        return view('analytics', compact(
            'totalLeads', 'totalPipelineValue', 'conversionRate', 'avgDealValue',
            'leadsPerStatus', 'leadsPerSource', 'pipelinePerStage', 'projectsPerStatus'
        ));
    })->name('analytics');

    Route::get('/reports', function () {
        $user    = auth()->user();
        $bulan = request('bulan', now()->format('Y-m'));
        // Tolak input ngawur (?bulan=xyz) — kalau tidak, halaman error 500
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $bulan)) {
            $bulan = now()->format('Y-m');
        }
        $periode = \Carbon\Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();

        // Semua query difilter berdasarkan role lewat scope visibleTo()
        $totalLeads         = \App\Models\Lead::visibleTo($user)->count();
        $newLeads           = \App\Models\Lead::visibleTo($user)->whereMonth('created_at', $periode->month)->whereYear('created_at', $periode->year)->count();
        $wonLeads           = \App\Models\Lead::visibleTo($user)->where('status', 'closing')->count();
        $lostLeads          = \App\Models\Lead::visibleTo($user)->where('status', 'batal')->count();
        $totalPipelineValue = \App\Models\Pipeline::visibleTo($user)->sum('value');
        $wonValue           = \App\Models\Pipeline::visibleTo($user)->where('stage', 'won')->sum('value');
        $activeProjects     = \App\Models\Project::visibleTo($user)->whereIn('status', ['planning','in_progress'])->count();
        $completedProjects  = \App\Models\Project::visibleTo($user)->where('status', 'completed')->count();
        $conversionRate     = $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100) : 0;
        $leadsByStatus      = \App\Models\Lead::visibleTo($user)->selectRaw('status, COUNT(*) as total, SUM(value) as nilai')->groupBy('status')->orderByRaw('COUNT(*) DESC')->get();
        $leadsBySource      = \App\Models\Lead::visibleTo($user)->selectRaw('source, COUNT(*) as total')->groupBy('source')->orderByRaw('COUNT(*) DESC')->get();
        $activePipelines    = \App\Models\Pipeline::visibleTo($user)->with('lead')->whereNotIn('stage', ['won','lost'])->orderBy('value', 'desc')->take(10)->get();
        $recentActivities   = \App\Models\Activity::with('createdBy')
            ->when($user->isStaff(), fn($q) => $q->where('created_by', $user->id))
            ->when($user->isManajer(), function ($q) use ($user) {
                $ids = $user->staffMembers()->pluck('id')->toArray();
                $ids[] = $user->id;
                $q->whereIn('created_by', $ids);
            })
            ->whereMonth('created_at', $periode->month)->whereYear('created_at', $periode->year)
            ->orderBy('created_at', 'desc')->take(10)->get();
        $projects           = \App\Models\Project::visibleTo($user)->with('lead')->orderBy('created_at', 'desc')->take(8)->get();
        $leadsPerMonth      = \App\Models\Lead::visibleTo($user)->selectRaw("TO_CHAR(created_at, 'Mon YY') as month, COUNT(*) as total")->where('created_at', '>=', now()->subMonths(6))->groupByRaw("TO_CHAR(created_at, 'Mon YY')")->orderByRaw("MIN(created_at)")->get();

        return view('reports', compact(
            'bulan', 'periode', 'totalLeads', 'newLeads', 'wonLeads', 'lostLeads',
            'totalPipelineValue', 'wonValue', 'activeProjects', 'completedProjects',
            'conversionRate', 'leadsByStatus', 'leadsBySource', 'activePipelines',
            'recentActivities', 'projects', 'leadsPerMonth'
        ));
    })->name('reports');

    Route::get('/reports/print', function () {
        $user    = auth()->user();
        $bulan = request('bulan', now()->format('Y-m'));
        // Tolak input ngawur (?bulan=xyz) — kalau tidak, halaman error 500
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $bulan)) {
            $bulan = now()->format('Y-m');
        }
        $periode = \Carbon\Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();

        // Semua query difilter berdasarkan role lewat scope visibleTo()
        $totalLeads         = \App\Models\Lead::visibleTo($user)->count();
        $newLeads           = \App\Models\Lead::visibleTo($user)->whereMonth('created_at', $periode->month)->whereYear('created_at', $periode->year)->count();
        $wonLeads           = \App\Models\Lead::visibleTo($user)->where('status', 'closing')->count();
        $lostLeads          = \App\Models\Lead::visibleTo($user)->where('status', 'batal')->count();
        $totalPipelineValue = \App\Models\Pipeline::visibleTo($user)->sum('value');
        $wonValue           = \App\Models\Pipeline::visibleTo($user)->where('stage', 'won')->sum('value');
        $activeProjects     = \App\Models\Project::visibleTo($user)->whereIn('status', ['planning','in_progress'])->count();
        $completedProjects  = \App\Models\Project::visibleTo($user)->where('status', 'completed')->count();
        $conversionRate     = $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100) : 0;
        $leadsByStatus      = \App\Models\Lead::visibleTo($user)->selectRaw('status, COUNT(*) as total, SUM(value) as nilai')->groupBy('status')->orderByRaw('COUNT(*) DESC')->get();
        $leadsBySource      = \App\Models\Lead::visibleTo($user)->selectRaw('source, COUNT(*) as total')->groupBy('source')->orderByRaw('COUNT(*) DESC')->get();
        $activePipelines    = \App\Models\Pipeline::visibleTo($user)->with('lead')->whereNotIn('stage', ['won','lost'])->orderBy('value', 'desc')->take(10)->get();
        $projects           = \App\Models\Project::visibleTo($user)->with('lead')->orderBy('created_at', 'desc')->take(10)->get();

        return view('reports-print', compact(
            'bulan', 'periode', 'totalLeads', 'newLeads', 'wonLeads', 'lostLeads',
            'totalPipelineValue', 'wonValue', 'activeProjects', 'completedProjects',
            'conversionRate', 'leadsByStatus', 'leadsBySource', 'activePipelines', 'projects'
        ));
    })->name('reports.print');

    Route::get('/reports/export', function () {
        $user  = auth()->user();
        $bulan = request('bulan', now()->format('Y-m'));
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $bulan)) {
            $bulan = now()->format('Y-m');
        }

        $filename = 'laporan-gak-crm-' . $bulan . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ReportExport($bulan, $user),
            $filename
        );
    })->name('reports.export');
    // Import massal — hanya direktur & manajer.
    // (Kolom "nama sales" di Excel menentukan assigned_to, jadi staff
    //  tidak boleh bisa menyuntik lead ke akun sales lain.)
    Route::middleware(['role:direktur,manajer'])->group(function () {
        Route::get('/import', [ImportController::class, 'index'])->name('import.index');
        Route::post('/import/preview', [ImportController::class, 'preview'])->name('import.preview');
        Route::post('/import/process', [ImportController::class, 'import'])->name('import.process');
    });

    // Semua role boleh MELIHAT daftar produk (dipakai di form lead)
    Route::get('/products-list', [ProductController::class, 'index'])->name('products.index');

    // Tapi hanya direktur & manajer yang boleh mengubahnya
    Route::middleware(['role:direktur,manajer'])->group(function () {
        Route::post('/products-list', [ProductController::class, 'store'])->name('products.store');
        Route::put('/products-list/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products-list/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    // User Management - hanya direktur
    Route::middleware(['role:direktur'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Team View - hanya direktur dan manajer
    Route::middleware(['role:direktur,manajer'])->group(function () {
        Route::get('/team', [TeamViewController::class, 'index'])->name('team.index');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

});

require __DIR__.'/auth.php';
