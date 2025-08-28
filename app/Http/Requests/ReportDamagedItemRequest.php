<?php

namespace App\Http\Requests;

use App\Models\BatchStorageLocation;
use App\Models\ItemUnit;
use App\Models\PurchaseReceiptItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class ReportDamagedItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $receiptItemId = $this->route('receiptItemId');
        $item = PurchaseReceiptItem::find($receiptItemId)?->item;
        $itemId = $item ? $item->id : null;

        return [
            'reason' => 'nullable|string|max:1000',
            'locations' => 'required|array|min:1',
            'locations.*.shelf_id' => 'required|integer|exists:shelves,id',
            'locations.*.unit_id' => [
                'required',
                'integer',
                // Ensure the provided unit is valid for this specific item
                function ($attribute, $value, $fail) use ($itemId) {
                    if ($itemId && !ItemUnit::where('item_id', $itemId)->where('unit_id', $value)->exists()) {
                        $fail("الوحدة المحددة غير صالحة لهذا المنتج.");
                    }
                },
            ],
            'locations.*.quantity' => 'required|numeric|gt:0',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $receiptItemId = $this->route('receiptItemId');
            $receiptItem = PurchaseReceiptItem::with('unit')->find($receiptItemId);
            if (!$receiptItem) return;

            // Get conversion factor for the batch's own unit
            $batchUnitFactor = ItemUnit::where('item_id', $receiptItem->item_id)
                    ->where('unit_id', $receiptItem->unit_id)
                    ->value('conversion_factor') ?? 1;

            $totalDamagedInBaseUnit = 0;

            foreach ($this->input('locations', []) as $index => $location) {
                // Get conversion factor for the user-provided unit
                $userUnitFactor = ItemUnit::where('item_id', $receiptItem->item_id)
                        ->where('unit_id', $location['unit_id'])
                        ->value('conversion_factor') ?? 1;

                // 1. Convert user's quantity to base unit
                $quantityInBase = $location['quantity'] * $userUnitFactor;
                $totalDamagedInBaseUnit += $quantityInBase;

                // 2. Convert base unit quantity to the batch's unit to compare with shelf stock
                $quantityInBatchUnit = $quantityInBase / $batchUnitFactor;

                // 3. Check if shelf has enough stock
                $storage = BatchStorageLocation::where('purchase_receipt_items_id', $receiptItemId)
                    ->where('shelf_id', $location['shelf_id'])
                    ->first();

                if (!$storage || $quantityInBatchUnit > $storage->quantity) {
                    $available = $storage ? $storage->quantity : 0;
                    $validator->errors()->add(
                        "locations.{$index}.quantity",
                        "الكمية المطلوبة تتجاوز المخزون على الرف ({$available} {$receiptItem->unit->name})."
                    );
                }
            }

            // 4. Check if total damaged quantity exceeds the batch's total available quantity
            $totalDamagedInBatchUnit = $totalDamagedInBaseUnit / $batchUnitFactor;
            if ($totalDamagedInBatchUnit > $receiptItem->available_quantity) {
                $validator->errors()->add('locations', "إجمالي الكمية التالفة يتجاوز المتاح في الدفعة.");
            }
        });
    }
}
