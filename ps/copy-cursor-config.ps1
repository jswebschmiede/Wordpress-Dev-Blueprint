# ===========================================
# Copy Cursor AI configuration to workspace root
# ===========================================

# >>> ENTER PROJECT PATHS HERE <<<
$Source = "your-source-folder\_wp-content-dev\cursor"
$Target = "your-source-folder\.cursor"

Write-Host "[*] Checking paths..."

if (-Not (Test-Path $Source)) {
    Write-Host "[ERROR] Source folder does not exist:`n$Source" -ForegroundColor Red
    exit 1
}

if (Test-Path $Target) {
    Write-Host "[WARNING] Target folder already exists. Removing it..."
    try {
        Remove-Item $Target -Recurse -Force
    }
    catch {
        Write-Host "[ERROR] Could not delete the target folder." -ForegroundColor Red
        exit 1
    }
}

Write-Host "[*] Copying Cursor configuration from:`n$Source`n-> to:`n$Target"

try {
    Copy-Item -Path $Source -Destination $Target -Recurse -Force
    Write-Host "[OK] Cursor configuration copied successfully!" -ForegroundColor Green
}
catch {
    Write-Host "[ERROR] Error while copying the Cursor configuration." -ForegroundColor Red
    exit 1
}
