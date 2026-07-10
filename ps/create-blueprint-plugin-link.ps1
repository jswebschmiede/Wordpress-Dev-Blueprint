# ===========================================
# WP Plugin Symlink Script (Project-specific)
# ===========================================

# >>> ENTER PROJECT PATHS HERE <<<
$Source = "your-source-folder\_wp-content-dev\boilerplate-theme\plugins\boilerplate-plugin"
$Target = "your-target-folder\wp-content\plugins\boilerplate-plugin"

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
        Write-Host "[ERROR] Could not delete the target folder. Run PowerShell as administrator!" -ForegroundColor Red
        exit 1
    }
}

Write-Host "[*] Creating symlink from:`n$Source`n-> to:`n$Target"

try {
    New-Item -ItemType SymbolicLink -Path $Target -Target $Source | Out-Null
    Write-Host "[OK] Symlink successfully created!" -ForegroundColor Green
}
catch {
    Write-Host "[ERROR] Error while creating the symlink. Run PowerShell as administrator?" -ForegroundColor Red
    exit 1
}
