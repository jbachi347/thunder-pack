@component('mail::message')
# Invitación al equipo

Has sido invitado a unirte al equipo de **{{ $invitation->tenant->name }}** como **{{ $invitation->role }}**.

@component('mail::button', ['url' => $acceptUrl])
Aceptar Invitación
@endcomponent

Esta invitación expira el {{ $invitation->expires_at->format('d/m/Y H:i') }}.

Si no conoces a quien te invitó, puedes ignorar este correo de forma segura.

Gracias,<br>
{{ config('app.name') }}
@endcomponent
