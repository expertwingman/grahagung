<?php

namespace App\Imports;

use App\Models\Lead;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class LeadsImport implements ToCollection
{
    public array $errors    = [];
    public int   $imported  = 0;
    public int   $skipped   = 0;
    public int   $duplicates = 0;
    public array $duplicateDetails = [];

    private array $staffCache   = [];
    private array $productCache = [];

    private function findStaffByName(?string $name): ?int
    {
        if (!$name) return auth()->id();
        $name = trim(strtolower($name));
        if (isset($this->staffCache[$name])) return $this->staffCache[$name];
        $user = \App\Models\User::where('role', 'staff')
            ->whereRaw('LOWER(name) LIKE ?', ['%'.$name.'%'])
            ->first();
        $id = $user ? $user->id : auth()->id();
        $this->staffCache[$name] = $id;
        return $id;
    }

    private function findProductByName(?string $name): ?int
    {
        if (!$name) return null;
        $name = trim(strtolower($name));
        if (isset($this->productCache[$name])) return $this->productCache[$name];
        $product = \App\Models\Product::whereRaw('LOWER(name) LIKE ?', ['%'.$name.'%'])->first();
        $id = $product ? $product->id : null;
        $this->productCache[$name] = $id;
        return $id;
    }

    public function collection(Collection $rows)
    {
        $headerRowIndex = null;
        $headers = [];

        // Cari baris header (ada kolom NAMA USER atau NAMA CUSTOMER)
        foreach ($rows as $index => $row) {
            $rowValues = $row->toArray();
            foreach ($rowValues as $cell) {
                if ($cell && (
                    stripos((string)$cell, 'nama_user') !== false ||
                    stripos((string)$cell, 'nama user') !== false ||
                    stripos((string)$cell, 'nama_customer') !== false ||
                    stripos((string)$cell, 'nama customer') !== false ||
                    stripos((string)$cell, 'nama marketing') !== false
                )) {
                    $headerRowIndex = $index;
                    $headers = array_map(fn($h) => strtolower(trim((string)($h ?? ''))), $rowValues);
                    break 2;
                }
            }
        }

        if ($headerRowIndex === null) {
            $this->errors[] = 'Header row tidak ditemukan';
            return;
        }

        // Proses data setelah header
        foreach ($rows as $index => $row) {
            if ($index <= $headerRowIndex) continue;

            $rowData = $row->toArray();
            $mapped  = [];
            foreach ($headers as $i => $header) {
                $mapped[$header] = $rowData[$i] ?? null;
            }

            $name = $mapped['nama_user'] ?? $mapped['nama user'] ?? $mapped['nama_customer'] ?? $mapped['nama customer'] ?? null;
            if (empty($name)) {
                $this->skipped++;
                continue;
            }

            $phone = $mapped['no_hp'] ?? $mapped['no hp'] ?? $mapped['nomor wa/hp'] ?? null;
            $phone = $phone ? (string)$phone : null;

            $waPhone = null;
            if ($phone) {
                $clean = preg_replace('/\D/', '', $phone);
                if (str_starts_with($clean, '0')) {
                    $clean = '62' . substr($clean, 1);
                } elseif (str_starts_with($clean, '8')) {
                    $clean = '62' . $clean;
                }
                $waPhone = $clean;
            }

            // Tanggal masuk (dipakai juga untuk deteksi duplikat)
            $inputDate = $this->parseDate(
                $mapped['tanggal masuk'] ?? $mapped['tanggal'] ?? null
            );

            $productId  = $this->findProductByName($mapped['produk'] ?? null);
            $assignedTo = $this->findStaffByName($mapped['nama_sales'] ?? $mapped['nama sales'] ?? $mapped['nama_marketing'] ?? $mapped['nama marketing'] ?? null);

            // Deteksi duplikat: hanya lewati kalau IDENTIK PENUH
            // (WA + Produk + Sales + Nama sama). Lead bentrok — calon sama
            // dipegang sales berbeda — tetap masuk agar bisa ditinjau manajer.
            if ($waPhone && $this->isDuplicate($waPhone, $productId, $assignedTo, trim((string)$name))) {
                $this->duplicates++;
                if (count($this->duplicateDetails) < 50) {
                    $this->duplicateDetails[] = trim((string)$name) . ' (' . $waPhone . ')';
                }
                continue;
            }

            try {
                Lead::create([
                    'name'              => trim((string)$name),
                    'phone'             => $waPhone ?? $phone,
                    'wa_phone'          => $waPhone,
                    'email'             => $mapped['email'] ?? null,
                    'source'            => $mapped['sumber'] ?? $mapped['sumber leads'] ?? 'iklan meta',
                    'status'            => $this->mapStatus($mapped['kategori'] ?? $mapped['status'] ?? null),
                    'notes'             => $mapped['report fu'] ?? $mapped['catatan'] ?? null,
                    'created_by'        => auth()->id(),
                    'assigned_to'       => $assignedTo,
                    'product_id'        => $productId,
                    'input_date'        => $inputDate,
                    'interest_type'     => $mapped['minat tipe'] ?? null,
                    'budget_range'      => $mapped['range budget'] ?? null,
                    'location_interest' => $mapped['lokasi minat'] ?? null,
                    'survey_plan'       => $mapped['rencana survey'] ?? null,
                    'survey_result'     => $mapped['hasil survey'] ?? null,
                    'cancel_reason'     => $mapped['alasan pending/batal'] ?? null,
                    'utj_status'        => isset($mapped['utj']) && strtolower((string)($mapped['utj'] ?? '')) === 'ya',
                    'follow_up_date'    => isset($mapped['tanggal follow up terakhir']) ? $this->parseDate($mapped['tanggal follow up terakhir']) : null,
                ]);
                $this->imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Baris " . ($index + 1) . ": " . $e->getMessage();
                $this->skipped++;
            }
        }
    }

    /**
     * Cek apakah lead yang IDENTIK PENUH sudah ada:
     * WA + Produk + Sales + Nama semuanya sama.
     * Tujuan: cegah dobel-klik import, TANPA menolak lead bentrok
     * (calon sama dipegang sales berbeda — itu sengaja dibiarkan masuk
     *  agar manajer bisa meninjau & menentukan siapa yang menangani).
     */
    private function isDuplicate(string $waPhone, ?int $productId, ?int $assignedTo, string $name): bool
    {
        return Lead::where('wa_phone', $waPhone)
            ->where('product_id', $productId)
            ->where('assigned_to', $assignedTo)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->exists();
    }

    private function mapStatus(?string $status): string
    {
        if (!$status) return 'no_respon';
        $s = strtolower(trim((string)$status));
        return match(true) {
            str_contains($s, 'no respon')  => 'no_respon',
            str_contains($s, 'belum')      => 'no_respon',
            str_contains($s, 'closing')    => 'closing',
            str_contains($s, 'deal')       => 'closing',
            str_contains($s, 'utj')        => 'utj',
            str_contains($s, 'survey')     => 'survey',
            str_contains($s, 'pl')         => 'kirim_pl',  // "sdh dikirim PL"
            str_contains($s, 'price')      => 'kirim_pl',
            str_contains($s, 'batal')      => 'batal',
            str_contains($s, 'cancel')     => 'batal',
            str_contains($s, 'pending')    => 'batal',
            str_contains($s, 'respon')     => 'respon',    // "RESPON" (setelah no respon dicek di atas)
            default                        => 'no_respon',
        };
    }

    private function parseDate($value): ?string
    {
        if ($value === null || $value === '') return null;

        try {
            if ($value instanceof \DateTime) {
                return $value->format('Y-m-d');
            }

            // Serial number Excel (mis. 46074) → tanggal.
            // Excel menghitung hari sejak 1899-12-30.
            if (is_numeric($value)) {
                $num = (float) $value;
                if ($num > 0 && $num < 100000) {
                    $date = \Carbon\Carbon::createFromTimestamp(
                        ($num - 25569) * 86400
                    )->format('Y-m-d');
                    return $date;
                }
            }

            $parsed = \Carbon\Carbon::parse($value)->format('Y-m-d');
            // Tolak epoch 1970 — itu tanda parse gagal, bukan tanggal asli
            return $parsed === '1970-01-01' ? null : $parsed;
        } catch (\Exception $e) {
            return null;
        }
    }
}
