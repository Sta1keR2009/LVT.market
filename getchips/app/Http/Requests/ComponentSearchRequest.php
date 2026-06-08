<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ComponentSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'componentNum' => ['nullable', 'string', 'max:255'],
            'amount' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'sort' => ['nullable', 'in:price_asc,price_desc,lead_asc,stock_desc'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
            'display_currency' => ['nullable', 'in:rub,usd'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'suppliers' => ['nullable', 'array'],
            'suppliers.*' => ['string', 'max:255'],
            'brands' => ['nullable', 'array'],
            'brands.*' => ['string', 'max:255'],
            'part_numbers' => ['nullable', 'array'],
            'part_numbers.*' => ['string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $min = $v->getData()['price_min'] ?? null;
            $max = $v->getData()['price_max'] ?? null;
            if ($min !== null && $min !== '' && $max !== null && $max !== ''
                && (float) $max < (float) $min) {
                $v->errors()->add('price_max', 'Максимальная цена не может быть меньше минимальной.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('componentNum')) {
            $this->merge([
                'componentNum' => strtoupper(trim((string) $this->input('componentNum'))),
            ]);
        }

        $suppliers = $this->input('suppliers');
        if (is_array($suppliers)) {
            $this->merge([
                'suppliers' => array_values(array_filter(array_map('trim', $suppliers), fn ($s) => $s !== '')),
            ]);
        }

        $brands = $this->input('brands');
        if (is_array($brands)) {
            $this->merge([
                'brands' => array_values(array_filter(array_map('trim', $brands), fn ($s) => $s !== '')),
            ]);
        }

        $partNumbers = $this->input('part_numbers');
        if (is_array($partNumbers)) {
            $this->merge([
                'part_numbers' => array_values(array_filter(array_map('trim', $partNumbers), fn ($s) => $s !== '')),
            ]);
        }

        if (! $this->has('display_currency') || $this->input('display_currency') === null || $this->input('display_currency') === '') {
            $this->merge([
                'display_currency' => config('services.getchips.pricing.default_currency', 'rub'),
            ]);
        }
    }
}
