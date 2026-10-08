# Sauvegarde de la base Robotix58 sous forme de script SQL (structure + données),
# équivalent de SSMS « Tâches > Générer des scripts ». Utilise SMO (module SQLPS).
# Usage : powershell -ExecutionPolicy Bypass -File tools\sauvegarde-sql.ps1
param(
    [string] $Fichier = (Join-Path $PSScriptRoot '..\docs\sql\robotix58.sql')
)

$ErrorActionPreference = 'Stop'

# Paramètres de connexion lus dans le .env du projet (aucun mot de passe dans ce script)
$env_ = @{}
Get-Content (Join-Path $PSScriptRoot '..\.env') | Where-Object { $_ -match '^\s*database\.default\.(\w+)\s*=\s*(.*)$' } | ForEach-Object {
    $env_[$Matches[1]] = $Matches[2].Trim().Trim("'").Trim('"')
}

Push-Location
Import-Module SQLPS -DisableNameChecking | Out-Null
Pop-Location

$serveur = New-Object Microsoft.SqlServer.Management.Smo.Server ("{0},{1}" -f $env_['hostname'], $env_['port'])
$serveur.ConnectionContext.LoginSecure = $false
$serveur.ConnectionContext.Login = $env_['username']
$serveur.ConnectionContext.Password = $env_['password']
$serveur.ConnectionContext.TrustServerCertificate = $true
$base = $serveur.Databases[$env_['database']]
if ($null -eq $base) { throw "Base $($env_['database']) introuvable." }

$scripteur = New-Object Microsoft.SqlServer.Management.Smo.Scripter $serveur
$o = $scripteur.Options
$o.ScriptSchema       = $true
$o.ScriptData         = $true
$o.DriAll             = $true    # clés primaires, étrangères, contraintes CHECK et DEFAULT
$o.Indexes            = $true
$o.Triggers           = $true
$o.IncludeHeaders     = $true
$o.WithDependencies   = $false   # les tables sont triées ci-dessous selon leurs clés étrangères
$o.ToFileOnly         = $true
$o.AppendToFile       = $true
$o.Encoding           = [System.Text.Encoding]::UTF8
$o.FileName           = [IO.Path]::GetFullPath($Fichier)

New-Item -ItemType Directory -Force (Split-Path $o.FileName) | Out-Null
"-- Sauvegarde de la base $($base.Name) générée le $(Get-Date -Format 'dd/MM/yyyy HH:mm') (SMO)`r`n-- Structure, données, procédures stockées, déclencheurs et vues`r`n-- Restauration : créer une base vide, la sélectionner, puis exécuter ce script`r`nGO`r`n" |
    Out-File -FilePath $o.FileName -Encoding utf8

# 1. Tables (structure, données, déclencheurs) dans l'ordre des clés étrangères :
#    une table n'est écrite qu'après celles qu'elle référence
$tables  = @($base.Tables | Where-Object { -not $_.IsSystemObject -and $_.Name -ne 'sysdiagrams' })
$ecrites = @{}
$ordre   = @()
while ($ordre.Count -lt $tables.Count) {
    $avant = $ordre.Count
    foreach ($table in $tables | Where-Object { -not $ecrites.ContainsKey($_.Name) }) {
        $parents = @($table.ForeignKeys | ForEach-Object { $_.ReferencedTable } | Where-Object { $_ -ne $table.Name })
        if (@($parents | Where-Object { -not $ecrites.ContainsKey($_) }).Count -eq 0) {
            $ecrites[$table.Name] = $true
            $ordre += $table
        }
    }
    if ($ordre.Count -eq $avant) { throw 'Dépendances circulaires entre tables.' }
}
$scripteur.EnumScript([Microsoft.SqlServer.Management.Sdk.Sfc.Urn[]] @($ordre | ForEach-Object { $_.Urn })) | Out-Null

# 2. Vues puis procédures stockées : définitions lues dans sys.sql_modules
#    (les déclencheurs sont déjà écrits avec leur table)
$requete = "SELECT o.name, o.type, m.definition
            FROM [$($base.Name)].sys.sql_modules m
            JOIN [$($base.Name)].sys.objects o ON o.object_id = m.object_id
            WHERE (o.type = 'V' AND o.name LIKE 'v[_]%') OR (o.type = 'P' AND o.name LIKE 'ps[_]%')
            ORDER BY o.type DESC, o.name"
$definitions = $serveur.ConnectionContext.ExecuteWithResults($requete).Tables[0].Rows
foreach ($ligne in $definitions) {
    Add-Content -Path $o.FileName -Encoding UTF8 -Value ("`r`n-- " + $ligne.name + "`r`n" + $ligne.definition.Trim() + "`r`nGO")
}

$taille = [math]::Round((Get-Item $o.FileName).Length / 1KB)
Write-Host "Script écrit : $($o.FileName) ($taille Ko, $($ordre.Count) tables, $(@($definitions).Count) vues et procédures)"
