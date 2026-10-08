# Envoie chaque page publique de Robotix19 au validateur HTML du W3C (validator.w3.org/nu)
# Usage : powershell -ExecutionPolicy Bypass -File tools\w3c.ps1
param([string] $base = 'http://localhost/Robotix19/')  # ex. : -base http://localhost:8080/ avec php spark serve
# Un robot présenté lors d'un événement du mois courant ou du suivant (dates de démo glissantes)
$evenements = @()
foreach ($decalage in 0, 1) {
    $evenements += Invoke-RestMethod "$($base)api/evenements?mois=$((Get-Date).AddMonths($decalage).ToString('yyyy-MM'))"  # += déplie le tableau JSON
}
$idRobot = ($evenements | Where-Object { $_.idProduit } | Select-Object -First 1).idProduit
$pages = @('', 'store', "store/robot/$idRobot", 'news', 'tarifs', 'evenements', 'showrooms', 'contact', 'compte/connexion', 'compte/inscription', 'club', 'credits', 'a-propos', 'compte/mot-de-passe-oublie')
$total = 0

foreach ($page in $pages) {
    # En-tête AJAX : la barre de debug (mode développement) n'est pas injectée, on valide la page réelle
    $html = (Invoke-WebRequest "$base$page" -UseBasicParsing -Headers @{ 'X-Requested-With' = 'XMLHttpRequest' }).Content
    $reponse = Invoke-RestMethod 'https://validator.w3.org/nu/?out=json' -Method Post `
        -ContentType 'text/html; charset=utf-8' -Body ([Text.Encoding]::UTF8.GetBytes($html))
    $erreurs = @($reponse.messages | Where-Object { $_.type -eq 'error' })
    $total += $erreurs.Count
    Write-Host ("{0,-22} {1} erreur(s)" -f "/$page", $erreurs.Count)
    $erreurs | ForEach-Object { Write-Host "   ligne $($_.lastLine) : $($_.message)" }
    Start-Sleep -Seconds 1  # le validateur public limite le nombre de requêtes
}

# Pages réservées aux comptes connectés : exportées par PHPUnit (W3C_EXPORT=1)
$env:W3C_EXPORT = '1'
& 'C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe' vendor/bin/phpunit --no-coverage tests/feature/ExportW3cTest.php | Out-Null
Remove-Item Env:W3C_EXPORT

foreach ($fichier in Get-ChildItem writable\w3c\*.html) {
    $reponse = Invoke-RestMethod 'https://validator.w3.org/nu/?out=json' -Method Post `
        -ContentType 'text/html; charset=utf-8' -Body ([IO.File]::ReadAllBytes($fichier.FullName))
    $erreurs = @($reponse.messages | Where-Object { $_.type -eq 'error' })
    $total += $erreurs.Count
    Write-Host ("{0,-22} {1} erreur(s)" -f "[$($fichier.BaseName)]", $erreurs.Count)
    $erreurs | ForEach-Object { Write-Host "   ligne $($_.lastLine) : $($_.message)" }
    Start-Sleep -Seconds 1
}
Write-Host "TOTAL : $total erreur(s)"
if ($total -gt 0) { exit 1 }
