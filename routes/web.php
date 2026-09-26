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

    Route::get('/dashboard', \App\Http\Controllers\DashboardController::class)
        ->name('dashboard');

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
    // Papan konten
    Route::get('konten', [\App\Http\Controllers\ContentController::class, 'index'])->name('konten.index');
    Route::get('konten/export', [\App\Http\Controllers\ContentController::class, 'export'])->name('konten.export');
    Route::get('konten/buat', [\App\Http\Controllers\ContentController::class, 'create'])->name('konten.create');
    Route::post('konten', [\App\Http\Controllers\ContentController::class, 'store'])->name('konten.store');
    Route::get('konten/{konten}/edit', [\App\Http\Controllers\ContentController::class, 'edit'])->name('konten.edit');
    Route::put('konten/{konten}', [\App\Http\Controllers\ContentController::class, 'update'])->name('konten.update');
    Route::delete('konten/{konten}', [\App\Http\Controllers\ContentController::class, 'destroy'])->name('konten.destroy');

    // Kunjungan lapangan (dari aplikasi sales)
    Route::get('kunjungan', [\App\Http\Controllers\KunjunganController::class, 'index'])
        ->name('kunjungan.index');

    // Reaktivasi cold lead
    Route::get('reaktivasi', [\App\Http\Controllers\ReaktivasiController::class, 'index'])
        ->name('reaktivasi.index');
    Route::post('reaktivasi/{lead}/contacted', [\App\Http\Controllers\ReaktivasiController::class, 'contacted'])
        ->name('reaktivasi.contacted');

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
