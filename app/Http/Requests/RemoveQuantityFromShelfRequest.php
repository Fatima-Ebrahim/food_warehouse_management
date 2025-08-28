<?php

namespace App\Http\Requests;

use App\Models\BatchStorageLocation;
use App\Models\ItemUnit;
use App\Models\PurchaseReceiptItem;
use Illuminate\Foundation\Http\FormRequest;

class RemoveQuantityFromShelfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $receiptItemId = $this->input('purchase_receipt_item_id');
        $item = PurchaseReceiptItem::find($receiptItemId)?->item;
        $itemId = $item ? $item->id : null;

        return [
            'purchase_receipt_item_id' => 'required|integer|exists:purchase_receipt_items,id',
            'shelf_id' => 'required|integer|exists:shelves,id',
            'unit_id' => [
                'required','integer',
                function ($attribute, $value, $fail) use ($itemId) {
                    if ($itemId && !ItemUnit::where('item_id', $itemId)->where('unit_id', $value)->exists()) {
                        $fail("الوحدة المحددة غير صالحة لهذا المنتج.");
                    }
                },
            ],
            'quantity' => 'required|numeric|gt:0',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) return;

            $data = $this->validated();
            $receiptItem = PurchaseReceiptItem::find($data['purchase_receipt_item_id']);

            $userUnitFactor = ItemUnit::where('item_id', $receiptItem->item_id)->where('unit_id', $data['unit_id'])->value('conversion_factor') ?? 1;
            $shelfUnitFactor = ItemUnit::where('item_id', $receiptItem->item_id)->where('unit_id', $receiptItem->unit_id)->value('conversion_factor') ?? 1;

            $quantityInBase = $data['quantity'] * $userUnitFactor;
            $quantityInShelfUnit = $quantityInBase / $shelfUnitFactor;

            $storage = BatchStorageLocation::where('purchase_receipt_items_id', $data['purchase_receipt_item_id'])
                ->where('shelf_id', $data['shelf_id'])->first();

            if (!$storage || $quantityInShelfUnit > $storage->quantity) {
                $available = $storage ? $storage->quantity : 0;
                $validator->errors()->add('quantity', "الكمية المطلوبة ({$quantityInShelfUnit}) تتجاوز المخزون على الرف ({$available}).");
            }
        });
    }
}
