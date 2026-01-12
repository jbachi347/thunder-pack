<?php

namespace ThunderPack\Database\Seeders;

use Illuminate\Database\Seeder;
use ThunderPack\Models\AvailableLimit;

class AvailableLimitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $limits = [
            // General/Core Limits
            [
                'key' => 'staff_limit',
                'name' => 'Límite de Personal',
                'category' => 'general',
                'description' => 'Número máximo de usuarios que pueden ser parte del equipo',
                'default_value' => 3,
                'unit' => 'usuarios',
                'sort_order' => 10,
            ],
            [
                'key' => 'max_projects',
                'name' => 'Máximo de Proyectos',
                'category' => 'general',
                'description' => 'Número máximo de proyectos que se pueden crear',
                'default_value' => 5,
                'unit' => 'proyectos',
                'sort_order' => 20,
            ],
            [
                'key' => 'max_clients',
                'name' => 'Máximo de Clientes',
                'category' => 'general',
                'description' => 'Número máximo de clientes que se pueden gestionar',
                'default_value' => 10,
                'unit' => 'clientes',
                'sort_order' => 30,
            ],
            [
                'key' => 'max_teams',
                'name' => 'Máximo de Equipos',
                'category' => 'general',
                'description' => 'Número máximo de equipos que se pueden crear',
                'default_value' => 1,
                'unit' => 'equipos',
                'sort_order' => 40,
            ],

            // Storage & Files
            [
                'key' => 'storage_quota_bytes',
                'name' => 'Cuota de Almacenamiento',
                'category' => 'storage',
                'description' => 'Espacio total de almacenamiento disponible',
                'default_value' => 5368709120, // 5GB in bytes
                'unit' => 'bytes',
                'sort_order' => 100,
            ],
            [
                'key' => 'max_file_upload_size_mb',
                'name' => 'Tamaño Máximo de Archivo',
                'category' => 'storage',
                'description' => 'Tamaño máximo permitido por archivo subido',
                'default_value' => 100,
                'unit' => 'MB',
                'sort_order' => 110,
            ],
            [
                'key' => 'max_backups',
                'name' => 'Máximo de Backups',
                'category' => 'storage',
                'description' => 'Número máximo de backups que se pueden almacenar',
                'default_value' => 10,
                'unit' => 'backups',
                'sort_order' => 120,
            ],

            // Communication
            [
                'key' => 'max_whatsapp_phones',
                'name' => 'Teléfonos WhatsApp',
                'category' => 'communication',
                'description' => 'Número máximo de teléfonos WhatsApp configurados',
                'default_value' => 1,
                'unit' => 'teléfonos',
                'sort_order' => 200,
            ],
            [
                'key' => 'max_email_sends_per_day',
                'name' => 'Emails por Día',
                'category' => 'communication',
                'description' => 'Número máximo de emails que se pueden enviar por día',
                'default_value' => 100,
                'unit' => 'emails/día',
                'sort_order' => 210,
            ],
            [
                'key' => 'max_sms_sends_per_month',
                'name' => 'SMS por Mes',
                'category' => 'communication',
                'description' => 'Número máximo de SMS que se pueden enviar por mes',
                'default_value' => 50,
                'unit' => 'SMS/mes',
                'sort_order' => 220,
            ],

            // API & Performance
            [
                'key' => 'max_api_calls_per_day',
                'name' => 'Llamadas API por Día',
                'category' => 'api',
                'description' => 'Número máximo de llamadas a la API por día',
                'default_value' => 1000,
                'unit' => 'llamadas/día',
                'sort_order' => 300,
            ],
            [
                'key' => 'max_concurrent_sessions',
                'name' => 'Sesiones Concurrentes',
                'category' => 'api',
                'description' => 'Número máximo de sesiones activas simultáneas',
                'default_value' => 5,
                'unit' => 'sesiones',
                'sort_order' => 310,
            ],

            // Reporting & Analytics
            [
                'key' => 'max_reports_per_month',
                'name' => 'Reportes por Mes',
                'category' => 'reporting',
                'description' => 'Número máximo de reportes que se pueden generar por mes',
                'default_value' => 20,
                'unit' => 'reportes/mes',
                'sort_order' => 400,
            ],
            [
                'key' => 'max_dashboard_widgets',
                'name' => 'Widgets del Dashboard',
                'category' => 'reporting',
                'description' => 'Número máximo de widgets en el dashboard',
                'default_value' => 10,
                'unit' => 'widgets',
                'sort_order' => 410,
            ],

            // Business Features
            [
                'key' => 'max_invoices_per_month',
                'name' => 'Facturas por Mes',
                'category' => 'business',
                'description' => 'Número máximo de facturas que se pueden crear por mes',
                'default_value' => 50,
                'unit' => 'facturas/mes',
                'sort_order' => 500,
            ],
            [
                'key' => 'max_quotes_per_month',
                'name' => 'Cotizaciones por Mes',
                'category' => 'business',
                'description' => 'Número máximo de cotizaciones por mes',
                'default_value' => 25,
                'unit' => 'cotizaciones/mes',
                'sort_order' => 510,
            ],

            // Technology/Database (Custody specific)
            [
                'key' => 'max_installations',
                'name' => 'Máximo de Instalaciones',
                'category' => 'technology',
                'description' => 'Número máximo de instalaciones del agente permitidas',
                'default_value' => 1,
                'unit' => 'instalaciones',
                'sort_order' => 600,
            ],
            [
                'key' => 'max_databases',
                'name' => 'Máximo de Bases de Datos',
                'category' => 'technology',
                'description' => 'Número máximo de bases de datos que se pueden respaldar',
                'default_value' => 5,
                'unit' => 'bases de datos',
                'sort_order' => 610,
            ],
            [
                'key' => 'max_backup_frequency_hours',
                'name' => 'Frecuencia Mínima de Backup',
                'category' => 'technology',
                'description' => 'Tiempo mínimo entre backups automáticos',
                'default_value' => 24,
                'unit' => 'horas',
                'sort_order' => 620,
            ],
        ];

        foreach ($limits as $limit) {
            AvailableLimit::updateOrCreate(
                ['key' => $limit['key']],
                $limit
            );
        }
    }
}