<?php

namespace ThunderPack\Livewire\SuperAdmin;

use Livewire\Component;
use ThunderPack\Models\Tenant;
use ThunderPack\Models\TenantWhatsappPhone;
use ThunderPack\Models\WhatsappMessageLog;
use ThunderPack\Services\WhatsAppService;
use Illuminate\Validation\Rule;
use Exception;

class TenantShow extends Component
{
    public Tenant $tenant;
    public string $activeTab = 'info';

    // WhatsApp phone form
    public $showPhoneForm = false;
    public $editingPhoneId = null;
    public $phoneNumber = '';
    public $instanceName = '';
    public $isDefault = false;
    public $isActive = true;
    public $selectedNotificationTypes = [];

    // Test message form
    public $showTestForm = false;
    public $testPhoneId = null;
    public $testMessage = '';

    protected $queryString = ['activeTab'];

    public function mount(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPhoneForm();
        $this->resetTestForm();
    }

    // ========== WhatsApp Phone Management ==========

    public function addPhone()
    {
        $this->resetPhoneForm();
        $this->showPhoneForm = true;
    }

    public function editPhone($phoneId)
    {
        $phone = TenantWhatsappPhone::findOrFail($phoneId);
        
        $this->editingPhoneId = $phone->id;
        $this->phoneNumber = $phone->phone_number;
        $this->instanceName = $phone->instance_name ?? '';
        $this->isDefault = $phone->is_default;
        $this->isActive = $phone->is_active;
        $this->selectedNotificationTypes = $phone->notification_types ?? [];
        $this->showPhoneForm = true;
    }

    public function savePhone()
    {
        $rules = [
            'phoneNumber' => [
                'required',
                'string',
                'regex:/^\+?[1-9]\d{7,14}$/',
                Rule::unique('tenant_whatsapp_phones', 'phone_number')
                    ->where('tenant_id', $this->tenant->id)
                    ->ignore($this->editingPhoneId),
            ],
            'instanceName' => 'nullable|string|max:255',
            'isDefault' => 'boolean',
            'isActive' => 'boolean',
            'selectedNotificationTypes' => 'nullable|array',
        ];

        $this->validate($rules, [
            'phoneNumber.required' => 'El número de teléfono es requerido',
            'phoneNumber.regex' => 'El número debe estar en formato E.164 (ej: +50312345678)',
            'phoneNumber.unique' => 'Este número ya está registrado para este tenant',
        ]);

        try {
            $data = [
                'tenant_id' => $this->tenant->id,
                'phone_number' => $this->phoneNumber,
                'instance_name' => $this->instanceName ?: null,
                'is_default' => $this->isDefault,
                'is_active' => $this->isActive,
                'notification_types' => !empty($this->selectedNotificationTypes) ? $this->selectedNotificationTypes : null,
            ];

            if ($this->editingPhoneId) {
                $phone = TenantWhatsappPhone::findOrFail($this->editingPhoneId);
                $phone->update($data);
                session()->flash('message', 'Teléfono actualizado exitosamente');
            } else {
                TenantWhatsappPhone::create($data);
                session()->flash('message', 'Teléfono agregado exitosamente');
            }

            $this->resetPhoneForm();
        } catch (Exception $e) {
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function deletePhone($phoneId)
    {
        try {
            $phone = TenantWhatsappPhone::findOrFail($phoneId);
            $phone->delete();
            session()->flash('message', 'Teléfono eliminado exitosamente');
        } catch (Exception $e) {
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function togglePhoneActive($phoneId)
    {
        $phone = TenantWhatsappPhone::findOrFail($phoneId);
        $phone->update(['is_active' => !$phone->is_active]);
        session()->flash('message', 'Estado actualizado');
    }

    public function setAsDefault($phoneId)
    {
        $phone = TenantWhatsappPhone::findOrFail($phoneId);
        $phone->update(['is_default' => true]);
        session()->flash('message', 'Teléfono marcado como predeterminado');
    }

    public function resetPhoneForm()
    {
        $this->showPhoneForm = false;
        $this->editingPhoneId = null;
        $this->phoneNumber = '';
        $this->instanceName = '';
        $this->isDefault = false;
        $this->isActive = true;
        $this->selectedNotificationTypes = [];
        $this->resetValidation();
    }

    // ========== Test Message ==========

    public function openTestForm($phoneId = null)
    {
        $this->testPhoneId = $phoneId;
        $this->testMessage = "🧪 Mensaje de prueba desde Custody\n\nEste es un mensaje de prueba del sistema de notificaciones WhatsApp.\n\nFecha: " . now()->format('d/m/Y H:i:s');
        $this->showTestForm = true;
    }

    public function sendTestMessage()
    {
        $this->validate([
            'testPhoneId' => 'required|exists:tenant_whatsapp_phones,id',
            'testMessage' => 'required|string|min:1',
        ], [
            'testPhoneId.required' => 'Debe seleccionar un teléfono',
            'testMessage.required' => 'El mensaje no puede estar vacío',
        ]);

        try {
            $phone = TenantWhatsappPhone::findOrFail($this->testPhoneId);
            $whatsappService = app(WhatsAppService::class);

            $result = $whatsappService->sendTestMessage($phone, $this->testMessage);

            if ($result['success']) {
                session()->flash('message', '✅ Mensaje enviado exitosamente a ' . $phone->display_phone);
            } else {
                session()->flash('error', '❌ Error al enviar: ' . $result['message']);
            }

            $this->resetTestForm();
        } catch (Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function resetTestForm()
    {
        $this->showTestForm = false;
        $this->testPhoneId = null;
        $this->testMessage = '';
        $this->resetValidation(['testPhoneId', 'testMessage']);
    }

    // ========== Render ==========

    public function render()
    {
        $this->tenant->loadCount(['users']);
        $this->tenant->load([
            'subscriptions' => fn($q) => $q->latest(),
            'whatsappPhones' => fn($q) => $q->orderBy('is_default', 'desc')->orderBy('created_at', 'desc'),
        ]);

        $storagePercentage = $this->tenant->storage_quota_bytes > 0
            ? round(($this->tenant->storage_used_bytes / $this->tenant->storage_quota_bytes) * 100, 2)
            : 0;

        $whatsappStats = [];
        $whatsappLogs = collect();
        
        if ($this->activeTab === 'whatsapp') {
            $whatsappService = app(WhatsAppService::class);
            $whatsappStats = $whatsappService->getStatistics($this->tenant);
            $whatsappLogs = WhatsappMessageLog::where('tenant_id', $this->tenant->id)
                ->with('whatsappPhone')
                ->recent()
                ->limit(20)
                ->get();
        }

        return view('thunder-pack::livewire.super-admin.tenant-show', [
            'storagePercentage' => $storagePercentage,
            'notificationTypes' => WhatsAppService::notificationTypes(),
            'whatsappStats' => $whatsappStats,
            'whatsappLogs' => $whatsappLogs,
            'whatsappConfigured' => app(WhatsAppService::class)->isConfigured(),
        ])->layout('layouts.app-sidebar-sa');
    }
}
