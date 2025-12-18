<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRouteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Route::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Admins have relaxed validation - most fields are optional
        if ($this->user() && $this->user()->isAdmin()) {
            return [
                // Identification
                'name' => ['required', 'string', 'max:255'],
                'location_id' => ['nullable', 'exists:locations,id'],

                // Technical specifications - all optional for admins
                'length_m' => ['nullable', 'integer', 'min:1', 'max:10000'],
                'pitch_count' => ['nullable', 'integer', 'min:1', 'max:50'],
                'grade_type' => ['nullable', 'in:UIAA,French'],
                'grade_value' => ['nullable', 'string', 'max:10'],
                'risk_rating' => ['nullable', 'in:None,R,X'],

                // Logistics
                'approach_description' => ['nullable', 'string', 'max:5000'],
                'descent_description' => ['nullable', 'string', 'max:5000'],
                'required_gear' => ['nullable', 'string', 'max:2000'],

                // Type and status - optional for admins
                'route_type' => ['nullable', 'in:Alpine,Sport,Traditional'],
                'status' => ['nullable', 'in:New,Equipped,Needs Repair,Closed'],

                // File upload
                'topo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // 5MB max
                'topo_data' => ['nullable', 'json'],
                'photos' => ['nullable', 'array', 'max:10'],
                'photos.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            ];
        }

        // Standard validation for non-admin users
        return [
            // Identification
            'name' => ['required', 'string', 'max:255'],
            'location_id' => ['required', 'exists:locations,id'],

            // Technical specifications
            'length_m' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'pitch_count' => ['required', 'integer', 'min:1', 'max:50'],
            'grade_type' => ['required', 'in:UIAA,French'],
            'grade_value' => ['required', 'string', 'max:10'],
            'risk_rating' => ['required', 'in:None,R,X'],

            // Logistics
            'approach_description' => ['nullable', 'string', 'max:5000'],
            'descent_description' => ['nullable', 'string', 'max:5000'],
            'required_gear' => ['nullable', 'string', 'max:2000'],

            // Type and status
            'route_type' => ['required', 'in:Alpine,Sport,Traditional'],
            'status' => ['required', 'in:New,Equipped,Needs Repair,Closed'],

            // File upload
            'topo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // 5MB max
            'topo_data' => ['nullable', 'json'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }
}
