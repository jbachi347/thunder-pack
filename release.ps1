# Thunder-Pack Release Script v1.6.0
# Automated release process for Thunder-Pack

Write-Host "================================" -ForegroundColor Cyan
Write-Host "Thunder-Pack Release v1.6.0" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# Verificar que estamos en el directorio correcto
$currentPath = Get-Location
if (-not ($currentPath.Path -like "*thunder-pack*")) {
    Write-Host "ERROR: No estás en el directorio thunder-pack" -ForegroundColor Red
    Write-Host "Ejecuta: cd d:\laragon\www\thunder-pack" -ForegroundColor Yellow
    exit 1
}

# Verificar git status
Write-Host "1. Verificando estado de Git..." -ForegroundColor Yellow
git status

Write-Host ""
$continue = Read-Host "¿Hay cambios sin commitear? (s/n)"
if ($continue -eq "s") {
    Write-Host ""
    Write-Host "2. Agregando archivos al staging..." -ForegroundColor Yellow
    git add .
    
    Write-Host ""
    Write-Host "3. Creando commit..." -ForegroundColor Yellow
    git commit -m "Release v1.6.0: Complete CRUD for Tenants and Users

- Add TenantsCreate, TenantsEdit components
- Add complete User management (UsersIndex, UsersShow, UsersCreate)
- Convert TenantsIndex and SubscriptionsIndex to table format
- Fix storage display bug (MB to GB)
- Add comprehensive Copilot instructions
- Update documentation"
    
    Write-Host ""
    Write-Host "4. Creando tag v1.6.0..." -ForegroundColor Yellow
    git tag -a v1.6.0 -m "Version 1.6.0 - Complete Tenant and User CRUD"
    
    Write-Host ""
    Write-Host "5. Pusheando a GitHub..." -ForegroundColor Yellow
    git push origin main
    git push origin v1.6.0
    
    Write-Host ""
    Write-Host "✅ Release completado!" -ForegroundColor Green
    Write-Host "Packagist se actualizará automáticamente en ~5 minutos" -ForegroundColor Cyan
    Write-Host "Verifica en: https://packagist.org/packages/bachisoft/thunder-pack" -ForegroundColor Cyan
} else {
    Write-Host "Todos los cambios ya están commiteados" -ForegroundColor Green
}

Write-Host ""
Write-Host "================================" -ForegroundColor Cyan
Write-Host "Siguiente paso: Actualizar proyectos consumidores" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "CUSTODY:" -ForegroundColor Yellow
Write-Host "  cd d:\laragon\www\custody" -ForegroundColor Gray
Write-Host "  composer update bachisoft/thunder-pack --no-cache" -ForegroundColor Gray
Write-Host "  php artisan optimize:clear" -ForegroundColor Gray
Write-Host ""
Write-Host "THUNDER-THEME:" -ForegroundColor Yellow
Write-Host "  cd d:\laragon\www\thunder-theme" -ForegroundColor Gray
Write-Host "  composer update bachisoft/thunder-pack --no-cache" -ForegroundColor Gray
Write-Host "  php artisan optimize:clear" -ForegroundColor Gray
Write-Host ""
