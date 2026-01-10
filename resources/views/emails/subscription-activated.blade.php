@component('mail::message')
# ¡Tu plan ha sido activado!

Hola,

Tu plan **{{ $subscription->plan->name }}** para **{{ $tenant->name }}** ha sido activado correctamente.

**Detalles de la suscripción:**
- Plan: {{ $subscription->plan->name }}
- Precio: ${{ number_format($subscription->plan->monthly_price_cents / 100, 2) }} USD/mes
@if($subscription->ends_at)
- Vence: {{ $subscription->ends_at->format('d/m/Y') }}
@endif

@component('mail::button', ['url' => config('app.url') . '/dashboard'])
Ir al Dashboard
@endcomponent

Gracias por confiar en nosotros,<br>
{{ config('app.name') }}
@endcomponent
