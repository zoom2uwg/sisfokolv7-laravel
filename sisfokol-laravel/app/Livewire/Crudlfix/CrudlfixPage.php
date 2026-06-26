<?php

namespace App\Livewire\Crudlfix;

use App\Support\Crudlfix\CrudlfixConfig;
use Livewire\Component;

/**
 * Livewire page orchestrator for Crudlfix.
 *
 * Manages CRUD mode switching (index/create/edit/show) and
 * coordinates between Table, Form, and Modal sub-components.
 */
class CrudlfixPage extends Component
{
    public CrudlfixConfig $config;
    public array $viewData = [];
    public array $columns = [];
    public array $formFields = [];
    public string $mode = 'index'; // index|create|edit|show
    public ?int $editId = null;
    public string $title = '';

    protected $listeners = [
        'crudlfix-saved' => 'handleSaved',
    ];

    public function mount(
        string $model,
        string $view,
        string $route,
        array $columns = [],
        array $formFields = [],
        array $search = [],
        array $with = [],
        array $filters = [],
        array $rules = [],
        array $viewData = [],
        int $perPage = 15,
        ?string $defaultSort = 'created_at',
        string $defaultDir = 'desc',
        ?array $exportColumns = null,
        ?string $authorize = null,
        ?string $authType = null,
        string $action = 'index',
        ?int $editId = null,
    ): void {
        $this->config = CrudlfixConfig::make([
            'model' => $model,
            'view' => $view,
            'route' => $route,
            'search' => $search,
            'with' => $with,
            'filters' => $filters,
            'rules' => $rules,
            'viewData' => $viewData,
            'perPage' => $perPage,
            'defaultSort' => $defaultSort,
            'defaultDir' => $defaultDir,
            'exportColumns' => $exportColumns,
            'authorize' => $authorize,
            'authType' => $authType,
        ]);

        $this->viewData = $viewData;
        $this->columns = $columns;
        $this->formFields = $formFields;
        $this->title = ucfirst(str_replace('.', ' ', $view));

        // Set mode based on action
        $this->mode = in_array($action, ['index', 'create', 'edit', 'show']) ? $action : 'index';
        $this->editId = $editId;
    }

    public function setMode(string $mode, ?int $id = null): void
    {
        $this->mode = $mode;
        $this->editId = $id;
    }

    public function handleSaved(array $data): void
    {
        $this->mode = 'index';
        $this->editId = null;
    }

    public function render()
    {
        return view('livewire.crudlfix.page');
    }
}
