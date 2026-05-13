#!/usr/bin/env pwsh
# new_branch.ps1 - 创建新分支脚本
# 用法: .\new_branch.ps1 -Type "feature" -Name "add-lawyer-profile"

param(
    [Parameter(Mandatory=$true)]
    [ValidateSet("feature", "fix", "hotfix", "task")]
    [string]$Type,
    
    [Parameter(Mandatory=$true)]
    [string]$Name,
    
    [string]$BaseBranch = "master"
)

$ErrorActionPreference = "Stop"

# 分支名称格式: type/name
$branchName = "$Type/$Name"

Write-Host "=======================================================================" -ForegroundColor Cyan
Write-Host "创建新分支: $branchName" -ForegroundColor Cyan
Write-Host "=======================================================================" -ForegroundColor Cyan
Write-Host ""

# 确保当前分支是干净的
Write-Host "[1/5] 检查工作目录状态..." -ForegroundColor Yellow
$status = git status --porcelain
if ($status.Count -gt 0) {
    Write-Host "[ERROR] 工作目录有未提交的更改！" -ForegroundColor Red
    Write-Host "请先提交或保存你的更改。" -ForegroundColor Red
    exit 1
}
Write-Host "      工作目录干净" -ForegroundColor Green
Write-Host ""

# 切换到基础分支
Write-Host "[2/5] 切换到 $BaseBranch 分支..." -ForegroundColor Yellow
git checkout $BaseBranch
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] 切换分支失败！" -ForegroundColor Red
    exit 1
}
Write-Host "      已切换到 $BaseBranch" -ForegroundColor Green
Write-Host ""

# 拉取最新代码
Write-Host "[3/5] 拉取最新代码..." -ForegroundColor Yellow
git pull origin $BaseBranch
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] 拉取失败！" -ForegroundColor Red
    exit 1
}
Write-Host "      代码已更新" -ForegroundColor Green
Write-Host ""

# 创建新分支
Write-Host "[4/5] 创建并切换到新分支 $branchName..." -ForegroundColor Yellow
git checkout -b $branchName
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] 创建分支失败！" -ForegroundColor Red
    exit 1
}
Write-Host "      已创建并切换到分支: $branchName" -ForegroundColor Green
Write-Host ""

# 推送新分支到远程
Write-Host "[5/5] 推送新分支到远程仓库..." -ForegroundColor Yellow
git push -u origin $branchName
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] 推送失败！" -ForegroundColor Red
    exit 1
}
Write-Host "      已推送到远程仓库" -ForegroundColor Green
Write-Host ""

Write-Host "=======================================================================" -ForegroundColor Green
Write-Host "[成功] 新分支创建完成！" -ForegroundColor Green
Write-Host "分支名称: $branchName" -ForegroundColor Cyan
Write-Host "基础分支: $BaseBranch" -ForegroundColor Cyan
Write-Host "远程状态: 已推送" -ForegroundColor Cyan
Write-Host "=======================================================================" -ForegroundColor Green
Write-Host ""
Write-Host "接下来可以开始修改代码，完成后执行:" -ForegroundColor Yellow
Write-Host "  .\commit_and_push.ps1 -Message '你的提交信息'" -ForegroundColor Yellow
