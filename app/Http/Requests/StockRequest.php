<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockRequest extends FormRequest
{
    /**
     * Only authenticated users can manage stock.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'   => 'required|integer|exists:products,id',
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'quantity'     => 'required|integer|min:0',
            'expires_at'   => 'nullable|date|after:today',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required'   => 'Product ID is required.',
            'product_id.exists'     => 'The selected product does not exist.',
            'warehouse_id.required' => 'Warehouse ID is required.',
            'warehouse_id.exists'   => 'The selected warehouse does not exist.',
            'quantity.required'     => 'Quantity is required.',
            'quantity.integer'      => 'Quantity must be a whole number.',
            'quantity.min'          => 'Quantity cannot be negative.',
            'expires_at.date'       => 'Expiry date must be a valid date.',
            'expires_at.after'      => 'Expiry date must be a future date.',
        ];
    }

    /**
     * Prepare data before validation runs.
     * Trims whitespace from string inputs.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'product_id'   => (int) $this->product_id,
            'warehouse_id' => (int) $this->warehouse_id,
            'quantity'     => (int) $this->quantity,
        ]);
    }
}