<?php

namespace App\Livewire\Crudlfix;

use App\Support\Crudlfix\CrudlfixConfig;
use Livewire\Component;

/**
 * Livewire form component for Crudlfix.
 *
 * Provides real-time validation and save functionality
 * by reading from CrudlfixConfig.
 */
class CrudlfixForm extends Component
{
    use \App\Livewire\Crudlfix\Traits\HasCrudlfixForm;

    public CrudlfixConfig $config;
    public array $viewData = [];
    public array $formFields = [];

    public function mount(CrudlfixConfig $config, array $viewData = [], array $formFields = [], bool $isEdit = false, ?int $editId = null): void
    {
        $this->config = $config;
        $this->viewData = $viewData;
        $this->formFields = $formFields;
        $this->initForm($config, $editId);
    }

    protected function getConfigProperty(): CrudlfixConfig
    {
        return $this->config;
    }

    public function save(): void
    {
        $result = $this->saveForm();

        if ($result) {
            $this->dispatch('crudlfix-saved', [
                'route' => $this->config->route,
                'isEdit' => $this->isEdit,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.crudlfix.form');
    }
}
