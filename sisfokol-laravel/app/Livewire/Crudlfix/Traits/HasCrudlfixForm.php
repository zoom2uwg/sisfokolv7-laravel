<?php

namespace App\Livewire\Crudlfix\Traits;

use App\Support\Crudlfix\CrudlfixConfig;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Form logic for Livewire Crudlfix components.
 *
 * Provides real-time validation, data binding, and save logic
 * by reading from CrudlfixConfig.
 */
trait HasCrudlfixForm
{
    public array $data = [];
    public array $errors = [];
    public bool $isEdit = false;
    public ?int $editId = null;

    public function initForm(CrudlfixConfig $config, ?int $editId = null): void
    {
        $this->isEdit = $editId !== null;
        $this->editId = $editId;

        if ($this->isEdit && $editId) {
            $model = $config->model;
            $record = $model::findOrFail($editId);
            $this->data = $record->toArray();
        } else {
            $this->data = [];
        }
    }

    /**
     * Real-time single field validation on change.
     */
    public function updated($field): void
    {
        $config = $this->getConfigProperty();

        if (empty($config->rules)) {
            return;
        }

        // Only validate if field has a rule
        $rules = [$field => $config->rules[$field] ?? ''];
        $messages = $config->messages ?? [];

        if (empty($rules[$field])) {
            return;
        }

        try {
            Validator::make(
                [$field => data_get($this->data, $field)],
                $rules,
                $messages
            )->validate();
            unset($this->errors[$field]);
        } catch (ValidationException $e) {
            $this->errors[$field] = $e->errors()[$field][0] ?? '';
        }
    }

    /**
     * Save form data (create or update).
     */
    public function saveForm(): mixed
    {
        $config = $this->getConfigProperty();

        // Validate all fields
        try {
            $validated = Validator::make(
                $this->data,
                $config->rules ?? [],
                $config->messages ?? []
            )->validate();
        } catch (ValidationException $e) {
            $this->errors = [];
            foreach ($e->errors() as $field => $messages) {
                $this->errors[$field] = $messages[0] ?? '';
            }
            return null;
        }

        $model = $config->model;

        if ($this->isEdit) {
            $record = $model::findOrFail($this->editId);
            $record->fill($validated);
            $record->save();
            return $record;
        } else {
            return $model::create($validated);
        }
    }

    /**
     * Reset form state.
     */
    public function resetForm(): void
    {
        $this->data = [];
        $this->errors = [];
        $this->isEdit = false;
        $this->editId = null;
    }

    /**
     * Get CrudlfixConfig. Must be implemented by using class.
     */
    abstract protected function getConfigProperty(): CrudlfixConfig;
}
