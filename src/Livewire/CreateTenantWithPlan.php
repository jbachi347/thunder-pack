<?php

namespace ThunderPack\Livewire;

use ThunderPack\Models\Plan;
use ThunderPack\Models\Tenant;
use ThunderPack\Services\SubscriptionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CreateTenantWithPlan extends Component
{
    public $name = '';
    public $plan_id = '';
    public $plans;
    public $showModal = false;
    public $terms_accepted = false;
    public $privacy_accepted = false;

    protected $listeners = ['open-create-tenant-modal' => 'openModal'];

    protected $rules = [
        'name' => 'required|string|min:3|max:255',
        'plan_id' => 'required|exists:plans,id',
        'terms_accepted' => 'required|accepted',
        'privacy_accepted' => 'required|accepted',
    ];

    protected $messages = [
        'name.required' => 'El nombre del tenant es obligatorio.',
        'name.min' => 'El nombre debe tener al menos 3 caracteres.',
        'name.max' => 'El nombre no puede exceder 255 caracteres.',
        'plan_id.required' => 'Debes seleccionar un plan.',
        'plan_id.exists' => 'El plan seleccionado no es válido.',
        'terms_accepted.required' => 'Debes aceptar los términos y condiciones.',
        'terms_accepted.accepted' => 'Debes aceptar los términos y condiciones.',
        'privacy_accepted.required' => 'Debes aceptar la política de privacidad.',
        'privacy_accepted.accepted' => 'Debes aceptar la política de privacidad.',
    ];

    public function mount()
    {
        $this->plans = Plan::orderBy('monthly_price_cents')->get();
    }

    public function openModal()
    {
        $this->showModal = true;
        $this->reset(['name', 'plan_id']);
        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['name', 'plan_id', 'terms_accepted', 'privacy_accepted']);
        $this->resetValidation();
    }

    public function createTenant()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // 1. Crear el tenant
            $tenant = Tenant::create([
                'name' => $this->name,
                'owner_id' => Auth::id(),
                'terms_accepted_at' => now(),
                'privacy_accepted_at' => now(),
            ]);

            // 2. Asociar usuario actual al tenant
            $tenant->users()->attach(Auth::id());

            // 3. Crear suscripción trial de 7 días
            $plan = Plan::findOrFail($this->plan_id);
            $subscriptionService = app(SubscriptionService::class);
            
            $subscriptionService->activateManual(
                $tenant,
                $plan,
                7, // 7 días de trial
                true // is_trial
            );

            DB::commit();

            // 4. Establecer el tenant recién creado como actual
            Auth::user()->setCurrentTenant($tenant);

            session()->flash('message', "Tenant '{$tenant->name}' creado exitosamente con {$plan->name}. Trial de 7 días activado.");
            
            $this->closeModal();
            
            // Emitir evento para que TenantSelector se actualice
            $this->dispatch('tenant-created');
            
            // Redirigir al dashboard
            return $this->redirect(route('dashboard'));

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating tenant with plan', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            session()->flash('error', 'Error al crear el tenant: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('thunder-pack::livewire.create-tenant-with-plan');
    }
}
