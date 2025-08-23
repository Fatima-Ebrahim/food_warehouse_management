<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Shelf;
use App\Models\BatchStorageLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchStorageService
{

    public function calculateStorableUnits(Batch $batch, Shelf $shelf): int
    {
        $unit = $batch->unit;

        if (!$unit) {
            throw new \Exception('الوحدة غير معرّفة للدفعة.');
        }

        $availableWeight = $shelf->max_weight - $shelf->current_weight;
        $availableLength = $shelf->max_length - $shelf->current_length;

        $unitWeight = $unit->max_weight ?? $batch->item->weight_per_unit;
        $unitLength = $unit->length ?? 0;

        if ($unitWeight <= 0 || $unitLength <= 0) {
            throw new \Exception('قيم الوزن أو الطول للوحدة غير صحيحة.');
        }

        $byWeight = floor($availableWeight / $unitWeight);
        $byLength = floor($availableLength / $unitLength);

        return min($byWeight, $byLength);
    }

    /**
     * تخزين دفعة على رف معين
     */
    public function storeBatchToShelf(Batch $batch, Shelf $shelf, float $quantity): BatchStorageLocation
    {
        $remaining = $this->getRemainingQuantity($batch);

        if ($quantity > $remaining) {
            throw new \Exception('الكمية المطلوبة أكبر من الكمية المتبقية.');
        }

        $unit = $batch->unit;
        $unitWeight = $unit->max_weight ?? $batch->item->weight_per_unit;
        $unitLength = $unit->length ?? 0;

        $totalWeight = $quantity * $unitWeight;
        $totalLength = $quantity * $unitLength;

        if (($shelf->current_weight + $totalWeight) > $shelf->max_weight ||
            ($shelf->current_length + $totalLength) > $shelf->max_length) {
            throw new \Exception('السعة غير كافية على هذا الرف.');
        }

        return DB::transaction(function () use ($batch, $shelf, $quantity, $totalWeight, $totalLength) {
            $record = BatchStorageLocation::create([
                'batch_id' => $batch->id,
                'shelf_id' => $shelf->id,
                'quantity' => $quantity,
            ]);

            $shelf->increment('current_weight', $totalWeight);
            $shelf->increment('current_length', $totalLength);

            return $record;
        });
    }

    /**
     * حساب الكمية المتبقية من الدفعة بعد التخزين
     */
    public function getRemainingQuantity(Batch $batch): float
    {
        $stored = BatchStorageLocation::where('batch_id', $batch->id)->sum('quantity');
        return $batch->quantity - $stored;
    }
}
