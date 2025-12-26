<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserved;
use App\Models\CafeTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckExpiredReserved extends Command
{
    protected $signature = 'reserved:check-expired';
    protected $description = 'Cek reserved expired (jam & menit, WIB). Jika cafe_tables update berhasil → reserved.status = 0';

    public function handle()
    {
        /**
         * ==============================
         * WAKTU SEKARANG (WIB)
         * ==============================
         */
        $nowDate = now()->toDateString(); // YYYY-MM-DD
        $nowTime = now()->format('H:i');  // HH:MM (tanpa detik)

        Log::info('===== CRON RESERVED START =====', [
            'now_date' => $nowDate,
            'now_time' => $nowTime,
        ]);

        DB::transaction(function () use ($nowDate, $nowTime) {

            /**
             * ==============================
             * RULE STATUS
             * ==============================
             * order_reserved.status:
             *   1 = aktif
             *   0 = expired
             *
             * cafe_tables.status:
             *   'reserved'
             *   'available'
             */

            /**
             * ==============================
             * AMBIL RESERVED YANG MASIH AKTIF
             * ==============================
             */
            $expiredReserved = Reserved::where('status', 1) // HANYA reserved aktif
                ->whereDate('start_date', '<=', $nowDate)
                ->where(function ($q) use ($nowDate, $nowTime) {

                    // end_date < hari ini
                    $q->whereDate('end_date', '<', $nowDate)

                      // end_date = hari ini DAN jam:menit sudah lewat
                      ->orWhere(function ($q) use ($nowDate, $nowTime) {
                          $q->whereDate('end_date', '=', $nowDate)
                            ->whereRaw("TIME_FORMAT(end_date, '%H:%i') <= ?", [$nowTime]);
                      });
                })
                ->get();

            Log::info('TOTAL RESERVED EXPIRED', [
                'count' => $expiredReserved->count(),
            ]);

            /**
             * ==============================
             * PROSES UPDATE
             * ==============================
             */
            foreach ($expiredReserved as $item) {

                Log::info('PROCESS RESERVED', [
                    'reserved_id' => $item->id,
                    'table_id'    => $item->cafe_tables_id,
                    'start_date'  => $item->start_date,
                    'end_date'    => $item->end_date,
                ]);

                /**
                 * 1️⃣ UPDATE cafe_tables
                 *    reserved → available
                 */
                $updatedTable = CafeTable::where('id', $item->cafe_tables_id)
                    ->where('status', 'reserved')
                    ->update([
                        'status'     => 'available',
                        'updated_at' => now(),
                    ]);

                Log::info('UPDATE CAFE TABLE RESULT', [
                    'table_id' => $item->cafe_tables_id,
                    'updated'  => $updatedTable, // 1 = sukses, 0 = tidak
                ]);

                /**
                 * 2️⃣ JIKA cafe_tables BERHASIL DIUPDATE
                 *    → update order_reserved.status = 0
                 */
                if ($updatedTable > 0) {
                    $item->update([
                        'status' => 0, // expired
                    ]);

                    Log::info('RESERVED STATUS UPDATED', [
                        'reserved_id' => $item->id,
                        'status'      => 0,
                    ]);
                }
            }
        });

        Log::info('===== CRON RESERVED END =====');

        $this->info('✔ Cron selesai: table updated → reserved expired');
    }
}
