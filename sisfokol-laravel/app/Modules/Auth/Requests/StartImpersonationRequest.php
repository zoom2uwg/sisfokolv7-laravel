<?php

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartImpersonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! config('impersonate.enabled', false)) {
            abort(404);
        }

        $impersonator = $this->user();
        $target = $this->route('target');

        return $impersonator && $target
            && method_exists($impersonator, 'canImpersonate')
            && $impersonator->canImpersonate()
            && $impersonator->canBeImpersonated($target);
    }

    public function rules(): array
    {
        return [
            // Target user validated via route model binding
        ];
    }
}
