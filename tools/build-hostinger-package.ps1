param(
    [string]$Domain = 'https://concursodebolsasanglo.com.br',
    [string]$Output = ''
)

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
if ($Output -eq '') {
    $Output = Join-Path $root 'cbanglo26-hostinger-pronto.zip'
}

$sourceEnvPath = Join-Path $root '.env'
if (-not (Test-Path -LiteralPath $sourceEnvPath)) {
    throw 'Crie o .env local com a configuracao SMTP antes de gerar o pacote.'
}
$sourceEnv = [IO.File]::ReadAllText($sourceEnvPath)

function Get-EnvValue([string]$Key) {
    $match = [regex]::Match($sourceEnv, "(?m)^$([regex]::Escape($Key))=(.*)$")
    if (-not $match.Success) { return '' }
    return $match.Groups[1].Value.Trim().Trim('"').Trim("'")
}

function New-Hex([int]$Bytes) {
    $buffer = New-Object byte[] $Bytes
    $random = [Security.Cryptography.RandomNumberGenerator]::Create()
    try { $random.GetBytes($buffer) } finally { $random.Dispose() }
    return ([BitConverter]::ToString($buffer) -replace '-', '').ToLowerInvariant()
}

$smtpPassword = Get-EnvValue 'SMTP_PASSWORD'
if ($smtpPassword -eq '') {
    throw 'SMTP_PASSWORD nao esta preenchida no .env local.'
}

$domain = $Domain.TrimEnd('/')
$appKey = New-Hex 32
$installToken = New-Hex 16
$stage = Join-Path $env:TEMP ('cbanglo26-hostinger-' + (New-Hex 6))
New-Item -ItemType Directory -Path $stage | Out-Null

try {
    foreach ($directory in @('app', 'bootstrap', 'config', 'public', 'routes')) {
        Copy-Item -LiteralPath (Join-Path $root $directory) -Destination (Join-Path $stage $directory) -Recurse
    }

    New-Item -ItemType Directory -Path (Join-Path $stage 'database') | Out-Null
    Copy-Item -LiteralPath (Join-Path $root 'database/.htaccess') -Destination (Join-Path $stage 'database/.htaccess')
    Copy-Item -LiteralPath (Join-Path $root 'database/schema.sql') -Destination (Join-Path $stage 'database/schema.sql')

    foreach ($path in @('storage', 'storage/cache', 'storage/logs')) {
        New-Item -ItemType Directory -Path (Join-Path $stage $path) -Force | Out-Null
    }
    Copy-Item -LiteralPath (Join-Path $root 'storage/.htaccess') -Destination (Join-Path $stage 'storage/.htaccess')
    Copy-Item -LiteralPath (Join-Path $root 'storage/cache/.gitkeep') -Destination (Join-Path $stage 'storage/cache/.gitkeep')
    Copy-Item -LiteralPath (Join-Path $root 'storage/logs/.gitkeep') -Destination (Join-Path $stage 'storage/logs/.gitkeep')
    Copy-Item -LiteralPath (Join-Path $root '.htaccess') -Destination (Join-Path $stage '.htaccess')
    Copy-Item -LiteralPath (Join-Path $root '.env.example') -Destination (Join-Path $stage '.env.example')

    $mailFrom = Get-EnvValue 'MAIL_FROM_ADDRESS'
    $mailName = Get-EnvValue 'MAIL_FROM_NAME'
    $smtpHost = Get-EnvValue 'SMTP_HOST'
    $smtpPort = Get-EnvValue 'SMTP_PORT'
    $smtpUser = Get-EnvValue 'SMTP_USERNAME'
    $smtpEncryption = Get-EnvValue 'SMTP_ENCRYPTION'
    $popHost = Get-EnvValue 'POP3_HOST'
    $popPort = Get-EnvValue 'POP3_PORT'
    $popUser = Get-EnvValue 'POP3_USERNAME'
    $popPassword = Get-EnvValue 'POP3_PASSWORD'
    $popEncryption = Get-EnvValue 'POP3_ENCRYPTION'

    $productionEnv = @"
APP_ENV=production
APP_DEBUG=false
APP_URL=$domain
APP_KEY=$appKey
APP_TIMEZONE=America/Sao_Paulo
APP_SETUP_TOKEN=$installToken

DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

MAIL_FROM_ADDRESS=$mailFrom
MAIL_FROM_NAME="$mailName"
SMTP_HOST=$smtpHost
SMTP_PORT=$smtpPort
SMTP_USERNAME=$smtpUser
SMTP_PASSWORD=$smtpPassword
SMTP_ENCRYPTION=$smtpEncryption

POP3_HOST=$popHost
POP3_PORT=$popPort
POP3_USERNAME=$popUser
POP3_PASSWORD=$popPassword
POP3_ENCRYPTION=$popEncryption
"@
    [IO.File]::WriteAllText((Join-Path $stage '.env'), $productionEnv, [Text.UTF8Encoding]::new($false))

    $instructions = @"
CONCURSO DE BOLSAS - PUBLICACAO SEM SSH

1. No hPanel da Hostinger, abra Bancos de dados MySQL e crie um banco vazio.
2. No Gerenciador de Arquivos, abra public_html, envie este ZIP e clique em Extrair.
3. Abra este endereco no navegador:

$domain/instalar?token=$installToken

4. Cole o nome do banco, usuario e senha mostrados pelo hPanel e clique em Ativar agora.
5. Entre em $domain/admin com:
   usuario: admin
   senha: cbanglo26##
6. Abra Usuarios e troque a senha inicial.

Nao precisa de SSH nem phpMyAdmin.
"@
    [IO.File]::WriteAllText((Join-Path $stage 'COMO-PUBLICAR.txt'), $instructions, [Text.UTF8Encoding]::new($false))

    if (Test-Path -LiteralPath $Output) {
        Remove-Item -LiteralPath $Output -Force
    }
    Add-Type -AssemblyName System.IO.Compression
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    $archive = [IO.Compression.ZipFile]::Open($Output, [IO.Compression.ZipArchiveMode]::Create)
    try {
        Get-ChildItem -LiteralPath $stage -File -Recurse | ForEach-Object {
            $relative = $_.FullName.Substring($stage.Length).TrimStart('\').Replace('\', '/')
            [IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
                $archive,
                $_.FullName,
                $relative,
                [IO.Compression.CompressionLevel]::Optimal
            ) | Out-Null
        }
    } finally {
        $archive.Dispose()
    }
    Write-Host "Pacote criado: $Output"
    Write-Host 'Abra COMO-PUBLICAR.txt dentro do ZIP para ver o endereco privado de instalacao.'
} finally {
    if (Test-Path -LiteralPath $stage) {
        Remove-Item -LiteralPath $stage -Recurse -Force
    }
}
