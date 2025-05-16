Guia Completo para Resolver Erro 500 (Versão PowerShell)
📌 Passo a Passo Atualizado
1. Verificar logs do Laravel (PowerShell)
powershell
Get-Content -Path "storage\logs\laravel.log" -Wait -Tail 50
2. Comandos de permissão (PowerShell)
powershell
# Dar permissão à pasta storage
icacls "storage" /grant "IIS_IUSRS:(OI)(CI)F" /T

# Criar link simbólico (executar como administrador)
New-Item -ItemType SymbolicLink -Path "public\storage" -Target "storage\app\public"
3. Testar API com PowerShell (invés de cURL)
powershell
# Enviar arquivo via POST
$headers = @{
    "X-CSRF-TOKEN" = "seu_token_aqui"
}

$body = @{
    name = "teste"
    file = Get-Item -Path "C:\caminho\para\arquivo.xlsx"
}

Invoke-RestMethod -Uri "http://localhost/api/excel-tables" -Method Post -Form $body -Headers $headers
4. Verificar colunas no SQL Server (PowerShell)
powershell
# Via artisan tinker
php artisan tinker
> \DB::select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'excel_tables'");
5. Configurações importantes:
No php.ini (encontre com):

powershell
php --ini
No .env:

ini
APP_DEBUG=true
APP_ENV=local
📋 Checklist PowerShell
Logs visualizados com Get-Content

Permissões configuradas com icacls

Link simbólico criado com New-Item

Teste de API feito com Invoke-RestMethod

Colunas verificadas via php artisan tinker

Guarde este arquivo como solucao_powershell.md para referência!

🔍 Dicas Extras para PowerShell
Para monitorar logs em tempo real:

powershell
Get-Content -Path "storage\logs\laravel.log" -Wait
Para reiniciar o servidor:

powershell
Stop-Process -Name "php" -Force
php artisan serve
Se precisar do equivalente ao tail -f:

powershell
Get-Content -Path "arquivo.log" -Wait -Tail 10