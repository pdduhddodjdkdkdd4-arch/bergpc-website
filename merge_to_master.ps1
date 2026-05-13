#!/usr/bin/env pwsh
# merge_to_master.ps1 - 将分支合并到主分支
# 用法: .\merge_to_master.ps1 -Branch "feature/add-lawyer"

param(
    [Parameter(Mandatory=$true)]
    [string]$Branch
)

$ErrorActionPreference = "Stop"

Write-Host "=======================================================================" -ForegroundColor Cyan
Write-Host "合并分支 $Branch 到 master" -ForegroundColor Cyan
Write-Host "=======================================================================" -ForegroundColor Cyan
Write-Host ""

# 检查分支是否存在
Write-Host "[1/6] 检查分支是否存在..." -ForegroundColor Yellow
$branches = git branch --list $Branch
if (-not $branches) {
    Write-Host "[ERROR] 分支 $Branch 不存在！" -ForegroundColor Red
    exit 1
}
Write-Host "      分支存在" -ForegroundColor Green
Write-Host ""

# 确保当前分支是干净的
Write-Host "[2/6] 检查工作目录状态..." -ForegroundColor Yellow
$status = git status --porcelain
if ($status.Count -gt 0) {
    Write-Host "[ERROR] 工作目录有未提交的更改！" -ForegroundColor Red
    exit 1
}
Write-Host "      工作目录干净" -ForegroundColor Green
Write-Host ""

# 切换到目标分支
Write-Host "[3/6] 切换到分支 $Branch..." -ForegroundColor Yellow
git checkout $Branch
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] 切换分支失败！" -ForegroundColor Red
    exit 1
}
Write-Host "      已切换到 $Branch" -ForegroundColor Green
Write-Host ""

# 拉取最新代码
Write-Host "[4/6] 拉取最新代码..." -ForegroundColor Yellow
git pull origin $Branch
Write-Host "      代码已更新" -ForegroundColor Green
Write-Host ""

# 切换到 master
Write-Host "[5/6] 切换到 master 分支..." -ForegroundColor Yellow
git checkout master
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] 切换到 master 失败！" -ForegroundColor Red
    exit 1
}
Write-Host "      已切换到 master" -ForegroundColor Green
Write-Host ""

# 合并分支
Write-Host "[6/6] 合并 $Branch 到 master..." -ForegroundColor Yellow
git merge $Branch --no-ff -m "Merge branch '$Branch'"
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] 合并失败，可能存在冲突！" -ForegroundColor Red
    Write-Host "请手动解决冲突后重新执行。" -ForegroundColor Red
    exit 1
}
Write-Host "      合并成功" -ForegroundColor Green
Write-Host ""

# 推送合并结果
Write-Host "[额外] 推送合并结果到远程..." -ForegroundColor Yellow
git push origin master
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] 推送失败！" -ForegroundColor Red
    exit 1
}
Write-Host "      推送成功" -ForegroundColor Green
Write-Host ""

Write-Host "=======================================================================" -ForegroundColor Green
Write-Host "[成功] 分支 $Branch 已合并到 master！" -ForegroundColor Green
Write-Host "合并提交: $($commitHash)" -ForegroundColor Cyan
Write-Host "=======================================================================" -ForegroundColor Green
Write-Host ""
Write-Host "可选操作:" -ForegroundColor Yellow
Write-Host "  删除本地分支: git branch -d $Branch" -ForegroundColor Yellow
Write-Host "  删除远程分支: git push origin --delete $Branch" -ForegroundColor Yellow
