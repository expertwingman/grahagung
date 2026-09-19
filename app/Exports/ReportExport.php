<?php

namespace App\Exports;

use App\Models\Lead;
use App\Models\Pipeline;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ReportExport implements WithMultipleSheets
{
    protected string $bulan;
    protected $user;

    public function __construct(string $bulan, $user)
    {
        $this->bulan = $bulan;
        $this->user  = $user;
    }

    public function sheets(): array
    {
        return [
            new RingkasanSheet($this->bulan, $this->user),
            new LeadsStatusSheet($this->user),
            new DaftarLeadsSheet($this->user),
        ];
    }
}

// ============================================================
// Helper styling terpusat
// ============================================================
class ExcelStyle
{
    const HEADER_BG   = '1F2937';  // dark gray
    const HEADER_FG   = 'FFFFFF';
    const ACCENT_BG   = 'EFF6FF';  // light blue
    const TOTAL_BG    = 'F1F5F9';
    const BORDER_CLR  = 'CBD5E1';
    const KPI_BLUE    = 'DBEAFE';
    const KPI_GREEN   = 'DCFCE7';
    const KPI_YELLOW  = 'FEF3C7';
    const KPI_PURPLE  = 'EDE9FE';

    public static function header(): array
    {
        return [
            'font' => ['bold' => true, 'color' => ['rgb' => self::HEADER_FG], 'size' => 10, 'name' => 'Arial'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::HEADER_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
    }

    public static function cell(): array
    {
        return [
            'font' => ['size' => 10, 'name' => 'Arial'],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];
    }

    public static function total(): array
    {
        return [
            'font' => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::TOTAL_BG]],
        ];
    }

    public static function thinBorder(): array
    {
        $b = ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::BORDER_CLR]];
        return ['borders' => ['allBorders' => $b]];
    }
}

// ============================================================
// Sheet 1: Ringkasan KPI
// ============================================================
class RingkasanSheet implements WithTitle, WithEvents, WithColumnWidths
{
    protected $bulan;
    protected $user;

    public function __construct($bulan, $user)
    {
        $this->bulan = $bulan;
        $this->user  = $user;
    }

    public function title(): string { return 'Ringkasan'; }

    public function columnWidths(): array
    {
        return ['A' => 32, 'B' => 8, 'C' => 32, 'D' => 8, 'E' => 24];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $periode = Carbon::createFromFormat('Y-m', $this->bulan);
                $q = Lead::visibleTo($this->user);

                $total    = (clone $q)->count();
                $closing  = (clone $q)->where('status', 'closing')->count();
                $batal    = (clone $q)->where('status', 'batal')->count();
                $newLeads = (clone $q)->whereMonth('created_at', $periode->month)
                                      ->whereYear('created_at', $periode->year)->count();
                $conv     = $total > 0 ? round(($closing / $total) * 100, 1) : 0;
                $pipeline = Pipeline::visibleTo($this->user)->sum('value');
                $wonVal   = Pipeline::visibleTo($this->user)->where('stage', 'won')->sum('value');
                $fu       = (clone $q)->needsFollowUp()->count();
                $bentrok  = count(Lead::conflictingPhones());

                // ---- JUDUL ----
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'GAK CRM — Laporan Bulanan ' . $periode->locale('id')->isoFormat('MMMM Y'));
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'name' => 'Arial', 'color' => ['rgb' => ExcelStyle::HEADER_BG]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(32);

                $sheet->mergeCells('A2:E2');
                $sheet->setCellValue('A2', 'Dicetak: ' . now()->format('d/m/Y H:i') . ' WIB  |  Role: ' . auth()->user()->role);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '6B7280'], 'name' => 'Arial'],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(12);

                // ---- KPI CARDS (2x2 grid) ----
                $kpis = [
                    // [row, col, label, value, bg, format]
                    [4, 'A', 'TOTAL LEADS',     $total,    ExcelStyle::KPI_BLUE,   '@'],
                    [4, 'C', 'LEADS BARU',       $newLeads, ExcelStyle::KPI_BLUE,   '@'],
                    [8, 'A', 'CLOSING',          $closing,  ExcelStyle::KPI_GREEN,  '@'],
                    [8, 'C', 'CONVERSION RATE',  $conv.'%', ExcelStyle::KPI_GREEN,  '@'],
                    [12,'A', 'PIPELINE VALUE',   $pipeline, ExcelStyle::KPI_YELLOW, '#,##0'],
                    [12,'C', 'NILAI CLOSING',    $wonVal,   ExcelStyle::KPI_GREEN,  '#,##0'],
                    [16,'A', 'PERLU FOLLOW UP',  $fu,       ExcelStyle::KPI_YELLOW, '@'],
                    [16,'C', 'LEAD BENTROK',     $bentrok,  ExcelStyle::KPI_PURPLE, '@'],
                ];

                foreach ($kpis as [$row, $col, $label, $value, $bg, $fmt]) {
                    $colEnd = chr(ord($col) + 1);
                    $sheet->mergeCells("{$col}{$row}:{$colEnd}" . ($row));
                    $sheet->mergeCells("{$col}".($row+1).":{$colEnd}".($row+1));
                    $sheet->mergeCells("{$col}".($row+2).":{$colEnd}".($row+2));

                    $sheet->setCellValue("{$col}{$row}", $label);
                    $sheet->setCellValue("{$col}".($row+1), $value);

                    // Label style
                    $sheet->getStyle("{$col}{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 8, 'name' => 'Arial', 'color' => ['rgb' => '374151']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1],
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(18);

                    // Value style
                    $sheet->getStyle("{$col}".($row+1))->applyFromArray([
                        'font' => ['bold' => true, 'size' => 22, 'name' => 'Arial', 'color' => ['rgb' => ExcelStyle::HEADER_BG]],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1],
                    ]);
                    if ($fmt === '#,##0') {
                        $sheet->getStyle("{$col}".($row+1))->getNumberFormat()->setFormatCode('Rp #,##0');
                    }
                    $sheet->getRowDimension($row+1)->setRowHeight(36);

                    // Bottom padding
                    $sheet->getStyle("{$col}".($row+2))->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                    ]);
                    $sheet->getRowDimension($row+2)->setRowHeight(8);
                }

                // Kolom spacer (B & D)
                $sheet->getColumnDimension('B')->setWidth(2);
                $sheet->getColumnDimension('D')->setWidth(2);
            },
        ];
    }
}

// ============================================================
// Sheet 2: Leads per Status
// ============================================================
class LeadsStatusSheet implements WithTitle, FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $user;
    public function __construct($user) { $this->user = $user; }
    public function title(): string { return 'Leads per Status'; }

    public function headings(): array
    {
        return ['Status', 'Jumlah Lead', 'Persentase', 'Total Nilai (Rp)'];
    }

    public function collection(): Collection
    {
        $total    = Lead::visibleTo($this->user)->count();
        $rows     = Lead::visibleTo($this->user)
            ->selectRaw('status, COUNT(*) as total, SUM(value) as nilai')
            ->groupBy('status')->orderByRaw('COUNT(*) DESC')->get();
        $statuses = \App\Models\Lead::STATUSES;

        $result = $rows->map(function ($r) use ($total, $statuses) {
            return [
                $statuses[$r->status] ?? $r->status,
                $r->total,
                $total > 0 ? round(($r->total / $total) * 100, 1) : 0,
                (int)($r->nilai ?? 0),
            ];
        });

        $result->push([
            'TOTAL',
            $total,
            100,
            (int)Lead::visibleTo($this->user)->sum('value'),
        ]);

        return $result;
    }

    public function styles(Worksheet $sheet): array
    {
        $last = $sheet->getHighestRow();
        return [
            1     => ExcelStyle::header(),
            $last => ExcelStyle::total(),
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 24, 'B' => 14, 'C' => 14, 'D' => 24];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $last  = $sheet->getHighestRow();

                // Format angka & persentase
                $sheet->getStyle('B2:B'.$last)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('C2:C'.$last)->getNumberFormat()->setFormatCode('0.0"%"');
                $sheet->getStyle('D2:D'.$last)->getNumberFormat()->setFormatCode('Rp #,##0');

                // Border seluruh tabel
                $sheet->getStyle('A1:D'.$last)->applyFromArray(ExcelStyle::thinBorder());

                // Center header & angka
                $sheet->getStyle('A1:D1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B2:D'.$last)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Freeze header row
                $sheet->freezePane('A2');

                // Row height
                $sheet->getRowDimension(1)->setRowHeight(24);
                for ($r = 2; $r <= $last; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(20);
                }

                // Zebra striping
                for ($r = 2; $r < $last; $r++) {
                    if ($r % 2 === 0) {
                        $sheet->getStyle('A'.$r.':D'.$r)->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                        ]);
                    }
                }
            },
        ];
    }
}

// ============================================================
// Sheet 3: Daftar Leads Lengkap
// ============================================================
class DaftarLeadsSheet implements WithTitle, FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $user;
    public function __construct($user) { $this->user = $user; }
    public function title(): string { return 'Daftar Leads'; }

    public function headings(): array
    {
        return ['No', 'Nama Customer', 'Nomor WA', 'Sumber', 'Produk', 'Status',
                'Sales', 'Follow Up Terakhir', 'Budget', 'Tanggal Masuk'];
    }

    public function collection(): Collection
    {
        $statuses = \App\Models\Lead::STATUSES;
        return Lead::visibleTo($this->user)
            ->with('assignedTo', 'product')
            ->orderBy('assigned_to')->orderBy('name')
            ->get()->values()
            ->map(function ($lead, $i) use ($statuses) {
                return [
                    $i + 1,
                    $lead->name,
                    "'" . $lead->wa_phone,   // prefix ' agar tidak jadi angka di Excel
                    $lead->source ?? '-',
                    $lead->product ? $lead->product->name : '-',
                    $statuses[$lead->status] ?? $lead->status,
                    $lead->assignedTo ? $lead->assignedTo->name : '-',
                    $lead->follow_up_date ? $lead->follow_up_date->format('d/m/Y') : 'Belum',
                    $lead->budget_range ?? '-',
                    $lead->input_date ? $lead->input_date->format('d/m/Y') : '-',
                ];
            });
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ExcelStyle::header()];
    }

    public function columnWidths(): array
    {
        return ['A'=>5,'B'=>26,'C'=>18,'D'=>16,'E'=>24,'F'=>22,'G'=>14,'H'=>18,'I'=>16,'J'=>14];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $last  = $sheet->getHighestRow();

                // Border
                $sheet->getStyle('A1:J'.$last)->applyFromArray(ExcelStyle::thinBorder());

                // Freeze header
                $sheet->freezePane('A2');

                // Row heights
                $sheet->getRowDimension(1)->setRowHeight(24);
                for ($r = 2; $r <= $last; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(18);
                }

                // Zebra striping
                for ($r = 2; $r <= $last; $r++) {
                    if ($r % 2 === 0) {
                        $sheet->getStyle('A'.$r.':J'.$r)->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                        ]);
                    }
                }

                // Auto-filter
                $sheet->setAutoFilter('A1:J1');

                // Center no & tanggal
                $sheet->getStyle('A2:A'.$last)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('H2:J'.$last)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
