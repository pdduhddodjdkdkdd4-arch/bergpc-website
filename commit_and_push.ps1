#!/usr/bin/env pwsh
# commit_and_push.ps1 - 分支提交脚本
# 在当前分支上提交更改并推送到远程

param(
    [Parameter(Mandatory=$true)]
    [string]$Message
)

$ErrorActionPreference = "Stop"

Write-Host "=======================================================================" -ForegroundColor Cyan
Write-Host "提交更改到当前分支" -ForegroundColor Cyan
Write-Host "=======================================================================" -ForegroundColor Cyan
Write-Host ""

# 获取当前分支名称
$currentBranch = git branch --show-current
Write-Host "[INFO] 当前分支: $currentBranch" -ForegroundColor Yellow
Write-Host ""

# 检查是否有更改
Write-Host "[1/4] 检查工作目录状态..." -ForegroundColor Yellow
$status = git status --porcelain
if ($status.Count -eq 0) {
    Write-Host "[WARNING] 没有检测到任何更改" -ForegroundColor Yellow
    exit 0
}
Write-Host "      检测到 $($status.Count) 个文件有更改" -ForegroundColor Green
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
Write-Host "[4/4] 推送到远程分支 $currentBranch..." -ForegroundColor Yellow
git push origin $currentBranch
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
Write-Host "[成功] 更改已提交到分支 $currentBranch！" -ForegroundColor Green
Write-Host "提交信息: $Message" -ForegroundColor Cyan
Write-Host "=======================================================================" -ForegroundColor Green
Write-Host ""
Write-Host "完成后可以执行合并:" -ForegroundColor Yellow
Write-Host "  .\merge_to_master.ps1 -Branch '$currentBranch'" -ForegroundColor Yellow
