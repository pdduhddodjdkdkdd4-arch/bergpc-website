#!/usr/bin/env pwsh
# auto_commit.ps1 - 自动提交脚本
# 用法: .\auto_commit.ps1 "提交信息"

param(
    [Parameter(Mandatory=$false)]
    [string]$Message = ""
)

$ErrorActionPreference = "Stop"

Write-Host "=======================================================================" -ForegroundColor Cyan
Write-Host "自动 Git 提交和推送脚本" -ForegroundColor Cyan
Write-Host "=======================================================================" -ForegroundColor Cyan
Write-Host ""

# 检查是否有更改
$status = git status --porcelain
if ($status.Count -eq 0) {
    Write-Host "[INFO] 没有检测到任何更改，跳过提交" -ForegroundColor Yellow
    exit 0
}

# 生成提交信息
if ([string]::IsNullOrWhiteSpace($Message)) {
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $Message = "Update: $timestamp"
}

Write-Host "[1/4] 检测到 $($status.Count) 个文件有更改" -ForegroundColor Green
Write-Host ""

# Stage 所有更改
Write-Host "[2/4] 暂存所有更改..." -ForegroundColor Yellow
git add .
Write-Host "      完成" -ForegroundColor Green
Write-Host ""

# 提交
Write-Host "[3/4] 提交更改..." -ForegroundColor Yellow
git commit -m $Message
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] 提交失败！" -ForegroundColor Red
    exit 1
}
Write-Host "      提交成功: $Message" -ForegroundColor Green
Write-Host ""

# 推送
Write-Host "[4/4] 推送到 GitHub..." -ForegroundColor Yellow
git push origin master
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] 推送失败！" -ForegroundColor Red
    exit 1
}
Write-Host "      推送成功" -ForegroundColor Green
Write-Host ""

# 显示提交日志
Write-Host "最新提交:" -ForegroundColor Cyan
git log -1 --pretty=format:"%h - %s (%ci)" --abbrev-commit
Write-Host ""

Write-Host "=======================================================================" -ForegroundColor Green
Write-Host "[成功] 所有更改已提交并推送到 GitHub！" -ForegroundColor Green
Write-Host "=======================================================================" -ForegroundColor Green
