/**
 * Data Management Utility - API Based
 */
class DataManager {
    constructor() {
        this.API_BASE = 'api/';
        this.CSV_HEADERS = ['id', 'name', 'email', 'phone', 'platform', 'amount', 'created_at', 'ip'];
    }

    /**
     * Fetch all records from API
     */
    async getData(page = 1, limit = 50) {
        try {
            const response = await fetch(`${this.API_BASE}records.php?page=${page}&limit=${limit}`, {
                headers: {
                    'Authorization': 'Bearer ' + (authManager ? authManager.getToken() : '')
                }
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch data');
            }
            
            const result = await response.json();
            return result.data || [];
        } catch (error) {
            console.error('Error fetching data:', error);
            return [];
        }
    }

    /**
     * Add new record via API
     */
    async addRecord(record) {
        try {
            const response = await fetch(`${this.API_BASE}records.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(record)
            });
            
            if (!response.ok) {
                throw new Error('Failed to add record');
            }
            
            const newRecord = await response.json();
            return newRecord;
        } catch (error) {
            console.error('Error adding record:', error);
            throw error;
        }
    }

    /**
     * Update record via API
     */
    async updateRecord(id, updatedData) {
        try {
            const response = await fetch(`${this.API_BASE}records.php?id=${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + (authManager ? authManager.getToken() : '')
                },
                body: JSON.stringify(updatedData)
            });
            
            if (!response.ok) {
                throw new Error('Failed to update record');
            }
            
            return await response.json();
        } catch (error) {
            console.error('Error updating record:', error);
            throw error;
        }
    }

    /**
     * Delete record via API
     */
    async deleteRecord(id) {
        try {
            const response = await fetch(`${this.API_BASE}records.php?id=${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + (authManager ? authManager.getToken() : '')
                }
            });
            
            if (!response.ok) {
                throw new Error('Failed to delete record');
            }
            
            return await response.json();
        } catch (error) {
            console.error('Error deleting record:', error);
            throw error;
        }
    }

    /**
     * Clear all records (requires admin)
     */
    async clearAll() {
        try {
            const response = await fetch(`${this.API_BASE}records.php?clear=all`, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + (authManager ? authManager.getToken() : '')
                }
            });
            
            if (!response.ok) {
                throw new Error('Failed to clear records');
            }
            
            return await response.json();
        } catch (error) {
            console.error('Error clearing records:', error);
            throw error;
        }
    }

    /**
     * Get latest submitted record
     * Checks localStorage cache first (for unauthenticated thank-you page)
     * Falls back to API call if no cache
     */
    async getLatestRecord() {
        // Check localStorage cache first (no auth needed)
        const cached = localStorage.getItem('levinlaw_last_form');
        if (cached) {
            try {
                return JSON.parse(cached);
            } catch (e) {
                // Invalid JSON, continue to API
            }
        }

        // Fallback to API if no cache
        try {
            const response = await fetch(`${this.API_BASE}records.php?limit=1`, {
                headers: {
                    'Authorization': 'Bearer ' + (authManager ? authManager.getToken() : '')
                }
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch latest record');
            }
            
            const result = await response.json();
            return result.data && result.data.length > 0 ? result.data[0] : null;
        } catch (error) {
            console.error('Error fetching latest record:', error);
            return null;
        }
    }

    /**
     * Get data for export with optional date range
     * @param {string|null} startDate - Start date in ISO format (YYYY-MM-DDTHH:mm:ss)
     * @param {string|null} endDate - End date in ISO format (YYYY-MM-DDTHH:mm:ss)
     */
    async getDataForExport(startDate = null, endDate = null) {
        let url = `${this.API_BASE}records.php?limit=10000`;

        if (startDate) {
            // Convert datetime-local format to SQL format
            const startDateTime = startDate.replace('T', ' ') + ':00';
            url += `&start_date=${encodeURIComponent(startDateTime)}`;
        }

        if (endDate) {
            // Convert datetime-local format to SQL format
            const endDateTime = endDate.replace('T', ' ') + ':59';
            url += `&end_date=${encodeURIComponent(endDateTime)}`;
        }

        try {
            const response = await fetch(url, {
                headers: {
                    'Authorization': 'Bearer ' + (authManager ? authManager.getToken() : '')
                }
            });

            if (!response.ok) {
                throw new Error('Failed to fetch data for export');
            }

            const result = await response.json();
            return result.data || [];
        } catch (error) {
            console.error('Error fetching export data:', error);
            throw error;
        }
    }

    /**
     * Export records to XLSX file
     * @param {Array} records - Array of record objects
     */
    exportToXLSX(records) {
        if (!records || records.length === 0) {
            alert('No data to export');
            return;
        }

        // Prepare data for XLSX
        const data = records.map(r => ({
            'ID': r.id,
            'Name': r.name,
            'Email': r.email,
            'Phone': r.phone,
            'Platform': r.platform,
            'Amount': r.amount,
            'Submitted At': r.created_at || r.timestamp,
            'IP': r.ip || ''
        }));

        // Create worksheet
        const worksheet = XLSX.utils.json_to_sheet(data);

        // Set column widths
        worksheet['!cols'] = [
            { wch: 10 },  // ID
            { wch: 20 },  // Name
            { wch: 30 },  // Email
            { wch: 20 },  // Phone
            { wch: 20 },  // Platform
            { wch: 15 },  // Amount
            { wch: 25 },  // Submitted At
            { wch: 15 }   // IP
        ];

        // Create workbook
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Form Submissions');

        // Generate filename with timestamp
        const now = new Date();
        const timestamp = now.getFullYear() +
            String(now.getMonth() + 1).padStart(2, '0') +
            String(now.getDate()).padStart(2, '0') +
            '_' +
            String(now.getHours()).padStart(2, '0') +
            String(now.getMinutes()).padStart(2, '0') +
            String(now.getSeconds()).padStart(2, '0');
        const filename = `levinlaw_export_${timestamp}.xlsx`;

        // Download
        XLSX.writeFile(workbook, filename);
    }

    /**
     * Export CSV from records
     */
    exportCSV(records, filename = 'form_data.csv') {
        if (!records || records.length === 0) {
            alert('No data to export');
            return;
        }

        const headers = ['ID', 'Name', 'Email', 'Phone', 'Platform', 'Amount', 'Submitted At', 'IP'];
        const csvContent = [
            headers.join(','),
            ...records.map(r => [
                r.id,
                r.name,
                r.email,
                r.phone,
                r.platform,
                r.amount,
                r.created_at || r.timestamp,
                r.ip
            ].join(','))
        ].join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.click();
    }

    /**
     * Import CSV (sends to API)
     */
    async importCSV(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = async (e) => {
                try {
                    const text = e.target.result;
                    const lines = text.split('\n');
                    
                    if (lines.length < 2) {
                        reject('CSV file format is invalid');
                        return;
                    }

                    // Skip header row
                    const records = [];
                    for (let i = 1; i < lines.length; i++) {
                        const line = lines[i].trim();
                        if (!line) continue;
                        
                        const values = this.parseCSVLine(line);
                        if (values.length >= 5) {
                            records.push({
                                name: values[1] || '',
                                email: values[2] || '',
                                phone: values[3] || '',
                                platform: values[4] || '',
                                amount: values[5] || ''
                            });
                        }
                    }

                    // Import each record via API
                    for (const record of records) {
                        await this.addRecord(record);
                    }

                    resolve(records.length);
                } catch (error) {
                    reject('Failed to parse CSV file: ' + error.message);
                }
            };
            reader.onerror = () => reject('Failed to read file');
            reader.readAsText(file);
        });
    }

    /**
     * Parse CSV line (handles quotes)
     */
    parseCSVLine(line) {
        const result = [];
        let current = '';
        let inQuotes = false;
        
        for (let i = 0; i < line.length; i++) {
            const char = line[i];
            if (char === '"') {
                inQuotes = !inQuotes;
            } else if (char === ',' && !inQuotes) {
                result.push(current.trim());
                current = '';
            } else {
                current += char;
            }
        }
        result.push(current.trim());
        return result;
    }
}

// Global instance
window.csvManager = new DataManager();

/**
 * Authentication Manager
 */
class AuthManager {
    constructor() {
        this.CREDENTIALS = {
            username: 'admin_5_9',
            password: 'admin@2659Levinlaw'
        };
        this.AUTH_KEY = 'levinlaw_admin_auth';
        this.TOKEN_KEY = 'levinlaw_admin_token';
    }

    async login(username, password) {
        // 首先本地验证
        if (username !== this.CREDENTIALS.username || password !== this.CREDENTIALS.password) {
            return false;
        }

        // 尝试获取后端 JWT token
        try {
            const response = await fetch('api/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username, password })
            });

            if (response.ok) {
                const result = await response.json();
                localStorage.setItem(this.AUTH_KEY, 'true');
                localStorage.setItem(this.TOKEN_KEY, result.token);
                return true;
            }
        } catch (error) {
            console.warn('Failed to get JWT from API, using fallback');
        }

        // 降级：本地认证（没有后端时使用）
        localStorage.setItem(this.AUTH_KEY, 'true');
        localStorage.setItem(this.TOKEN_KEY, btoa(username + ':' + Date.now()));
        return true;
    }

    isLoggedIn() {
        return localStorage.getItem(this.AUTH_KEY) === 'true';
    }

    getToken() {
        return localStorage.getItem(this.TOKEN_KEY);
    }

    logout() {
        localStorage.removeItem(this.AUTH_KEY);
        localStorage.removeItem(this.TOKEN_KEY);
    }
}

window.authManager = new AuthManager();

/**
 * Form Validation Utility
 */
class FormValidator {
    /**
     * Validate name
     * @param {string} name - Name string
     * @returns {object} Validation result { valid: boolean, message: string }
     */
    static validateName(name) {
        if (!name || !name.trim()) {
            return { valid: false, message: 'Name is required' };
        }
        if (name.trim().length < 2) {
            return { valid: false, message: 'Name must be at least 2 characters' };
        }
        if (name.trim().length > 50) {
            return { valid: false, message: 'Name must not exceed 50 characters' };
        }
        return { valid: true, message: '' };
    }

    /**
     * Validate email
     * @param {string} email - Email string
     * @returns {object} Validation result
     */
    static validateEmail(email) {
        if (!email || !email.trim()) {
            return { valid: false, message: 'Email is required' };
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.trim())) {
            return { valid: false, message: 'Please enter a valid email address' };
        }
        return { valid: true, message: '' };
    }

    /**
     * Validate phone
     * @param {string} phone - Phone string
     * @returns {object} Validation result
     */
    static validatePhone(phone) {
        if (!phone || !phone.trim()) {
            return { valid: false, message: 'Phone number is required' };
        }
        const phoneRegex = /^[\+]?[1-9][\d\s-]{7,15}$/;
        if (!phoneRegex.test(phone.trim())) {
            return { valid: false, message: 'Please enter a valid phone number (7-15 digits)' };
        }
        return { valid: true, message: '' };
    }

    /**
     * Validate platform name
     * @param {string} platform - Platform name
     * @returns {object} Validation result
     */
    static validatePlatform(platform) {
        if (!platform || !platform.trim()) {
            return { valid: false, message: 'Platform name is required' };
        }
        if (platform.trim().length < 2) {
            return { valid: false, message: 'Platform name must be at least 2 characters' };
        }
        if (platform.trim().length > 100) {
            return { valid: false, message: 'Platform name must not exceed 100 characters' };
        }
        return { valid: true, message: '' };
    }

    /**
     * Validate amount
     * @param {string} amount - Amount string
     * @returns {object} Validation result
     */
    static validateAmount(amount) {
        if (!amount || !amount.trim()) {
            return { valid: false, message: 'Amount is required' };
        }
        const amountRegex = /^[\$\€\£\¥]?\s?[\d,]+\.?\d*$/;
        if (!amountRegex.test(amount.trim())) {
            return { valid: false, message: 'Please enter a valid amount format (e.g., $1000 or 1000.00)' };
        }
        return { valid: true, message: '' };
    }

    /**
     * 验证完整表单
     * @param {object} formData - 表单数据对象
     * @returns {object} 验证结果 { valid: boolean, errors: object }
     */
    static validateForm(formData) {
        const errors = {};

        const nameResult = this.validateName(formData.name);
        if (!nameResult.valid) errors.name = nameResult.message;

        const emailResult = this.validateEmail(formData.email);
        if (!emailResult.valid) errors.email = emailResult.message;

        const phoneResult = this.validatePhone(formData.phone);
        if (!phoneResult.valid) errors.phone = phoneResult.message;

        const platformResult = this.validatePlatform(formData.platform);
        if (!platformResult.valid) errors.platform = platformResult.message;

        const amountResult = this.validateAmount(formData.amount);
        if (!amountResult.valid) errors.amount = amountResult.message;

        return {
            valid: Object.keys(errors).length === 0,
            errors: errors
        };
    }

    /**
     * 显示表单错误
     * @param {HTMLElement} formElement - 表单元素
     * @param {object} errors - 错误信息对象
     */
    static showErrors(formElement, errors) {
        // 清除之前的错误
        this.clearErrors(formElement);

        // 添加新错误
        for (const [field, message] of Object.entries(errors)) {
            const input = formElement.querySelector(`[name="${field}"], #edit-${field}`);
            if (input) {
                input.classList.add('input-error');

                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.textContent = message;
                errorDiv.style.color = '#dc2626';
                errorDiv.style.fontSize = '12px';
                errorDiv.style.marginTop = '4px';

                const formGroup = input.closest('.form-group');
                if (formGroup) {
                    formGroup.appendChild(errorDiv);
                }
            }
        }
    }

    /**
     * 清除表单错误
     * @param {HTMLElement} formElement - 表单元素
     */
    static clearErrors(formElement) {
        formElement.querySelectorAll('.input-error').forEach(input => {
            input.classList.remove('input-error');
        });
        formElement.querySelectorAll('.error-message').forEach(el => {
            el.remove();
        });
    }

    /**
     * 为输入添加实时验证
     * @param {HTMLElement} formElement - 表单元素
     */
    static addRealtimeValidation(formElement) {
        const inputs = formElement.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                const fieldName = input.name || input.id.replace('edit-', '');
                const value = input.value;

                let result;
                switch (fieldName) {
                    case 'name':
                        result = this.validateName(value);
                        break;
                    case 'email':
                        result = this.validateEmail(value);
                        break;
                    case 'phone':
                        result = this.validatePhone(value);
                        break;
                    case 'platform':
                        result = this.validatePlatform(value);
                        break;
                    case 'amount':
                        result = this.validateAmount(value);
                        break;
                    default:
                        return;
                }

                const formGroup = input.closest('.form-group');
                if (!formGroup) return;

                const existingError = formGroup.querySelector('.error-message');

                if (!result.valid) {
                    input.classList.add('input-error');
                    if (!existingError) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'error-message';
                        errorDiv.textContent = result.message;
                        errorDiv.style.color = '#dc2626';
                        errorDiv.style.fontSize = '12px';
                        errorDiv.style.marginTop = '4px';
                        formGroup.appendChild(errorDiv);
                    } else {
                        existingError.textContent = result.message;
                    }
                } else {
                    input.classList.remove('input-error');
                    if (existingError) existingError.remove();
                }
            });

            input.addEventListener('input', () => {
                input.classList.remove('input-error');
                const formGroup = input.closest('.form-group');
                const existingError = formGroup?.querySelector('.error-message');
                if (existingError) existingError.remove();
            });
        });
    }
}

window.FormValidator = FormValidator;

/**
 * Form submission handler - API based
 */
window.handleFormSubmit = async function (event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const data = {
        name: formData.get('name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        platform: formData.get('platform'),
        amount: formData.get('amount')
    };

    // Validate form
    const validation = FormValidator.validateForm(data);
    if (!validation.valid) {
        FormValidator.showErrors(form, validation.errors);

        // Scroll to first error
        const firstError = form.querySelector('.input-error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
        return;
    }

    // Clear error states
    FormValidator.clearErrors(form);

    // Save to localStorage cache for Messenger redirect (no auth needed on thank-you page)
    localStorage.setItem('levinlaw_last_form', JSON.stringify(data));

    // Disable submit button
    const submitBtn = form.querySelector('.submit-btn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
    }

    try {
        // Save to database via API
        await csvManager.addRecord(data);

        // Redirect to thank-you page
        window.location.href = 'thank-you.html';
    } catch (error) {
        console.error('Form submission error:', error);
        alert('Failed to submit form. Please try again.');

        // Re-enable submit button
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit';
        }
    }
};

/**
 * 初始化表单
 */
window.initContactForm = function () {
    const form = document.getElementById('contact-form');
    if (form) {
        FormValidator.addRealtimeValidation(form);
    }
};

/**
 * Messenger跳转管理器
 */
class MessengerManager {
    constructor() {
        // 默认值
        this.DEFAULT_PAGE_ID = '100068675438543';
        this.DEFAULT_MESSENGER_LINK = 'https://m.me/' + this.DEFAULT_PAGE_ID;

        // 应用商店链接
        this.IOS_APP_STORE_URL = 'https://apps.apple.com/us/app/messenger/id454638411';
        this.ANDROID_PLAY_STORE_URL = 'https://play.google.com/store/apps/details?id=com.facebook.orca';
        this.MESSENGER_WEB_URL = 'https://www.messenger.com/t/';

        // URL Scheme
        this.MESSENGER_SCHEME = 'fb-messenger://';

        // 启用状态
        this.enabled = true;

        // 异步加载配置
        this.loadSettings();
    }

    /**
     * 从服务端加载配置
     */
    async loadSettings() {
        try {
            const response = await fetch('api/settings.php');
            if (response.ok) {
                const settings = await response.json();
                if (settings.messenger) {
                    this.DEFAULT_PAGE_ID = settings.messenger.pageId || this.DEFAULT_PAGE_ID;
                    this.DEFAULT_MESSENGER_LINK = 'https://m.me/' + this.DEFAULT_PAGE_ID;
                    this.enabled = settings.messenger.enabled !== false;
                }
            }
        } catch (error) {
            console.warn('Failed to load Messenger settings, using defaults:', error);
        }
    }

        /**
         * 检测当前平台
         * @returns {string} 'ios', 'android', 'web'
         */
        detectPlatform() {
            const userAgent = navigator.userAgent.toLowerCase();

            if (userAgent.includes('iphone') || userAgent.includes('ipad') || userAgent.includes('ipod')) {
                return 'ios';
            } else if (userAgent.includes('android')) {
                return 'android';
            }
            return 'web';
        }

    /**
     * 检测浏览器类型
     * @returns {object} { isInApp: boolean, browser: string, message: string }
     */
    detectBrowser() {
        const userAgent = navigator.userAgent.toLowerCase();

        // 微信内置浏览器
        if (userAgent.includes('micromessenger')) {
            return {
                isInApp: true,
                browser: 'wechat',
                message: 'In-app browser restricts app links. Please use your device browser.'
            };
        }

        // 抖音/TikTok 内置浏览器
        if (userAgent.includes('tiktok') || userAgent.includes('douyin')) {
            return {
                isInApp: true,
                browser: 'tiktok',
                message: 'In-app browser restricts app links. Please use your device browser.'
            };
        }

        // Instagram 内置浏览器
        if (userAgent.includes('instagram')) {
            return {
                isInApp: true,
                browser: 'instagram',
                message: 'In-app browser restricts app links. Please use your device browser.'
            };
        }

        // QQ 内置浏览器
        if (userAgent.includes('qq') && !userAgent.includes('mqqbrowser')) {
            return {
                isInApp: true,
                browser: 'qq',
                message: 'In-app browser restricts app links. Please use your device browser.'
            };
        }

        // Facebook 内置浏览器
        if (userAgent.includes('fbav') || userAgent.includes('fbios')) {
            return {
                isInApp: true,
                browser: 'facebook',
                message: 'In-app browser restricts app links. Please use your device browser.'
            };
        }

        return {
            isInApp: false,
            browser: 'external',
            message: ''
        };
    }

    /**
     * Build Messenger message content
     * @param {object} formData - Form data object
     * @returns {string} Formatted message text
     */
    buildMessage(formData) {
        let message = 'Case Report Details:\n\n';
        
        // Convert formData to "Question: Answer" format
        for (const [key, value] of Object.entries(formData)) {
            if (value && value !== 'Not provided') {
                message += `${key}：${value}\n`;
            }
        }
        
        message += '\nPlease assist with my case. Thank you!';

        return encodeURIComponent(message.trim());
    }

    /**
     * 构建Messenger跳转URL
     * @param {object} formData - 表单数据对象
     * @returns {object} { schemeUrl, fallbackUrl }
     */
    buildURL(formData) {
        const message = this.buildMessage(formData);
        const pageId = this.DEFAULT_PAGE_ID;

        return {
            // Messenger URL Scheme（用于App跳转）
            schemeUrl: `${this.MESSENGER_SCHEME}user-thread/${pageId}?text=${message}`,
            // 网页版链接（包含预填充消息）
            webUrl: `${this.MESSENGER_WEB_URL}${pageId}?text=${message}`,
            // m.me链接（自动检测App或网页，包含预填充消息）
            mMeUrl: `https://m.me/${pageId}?text=${message}`,
            message,
        };
    }

    /**
     * 获取应用商店链接
     * @param {string} platform - 平台类型
     * @returns {string} 应用商店URL
     */
    getAppStoreUrl(platform) {
        return platform === 'ios' ? this.IOS_APP_STORE_URL : this.ANDROID_PLAY_STORE_URL;
    }

    /**
     * 尝试打开Messenger应用
     * @param {object} formData - 表单数据对象
     */
    openMessenger(formData) {
        // 检查是否启用
        if (!this.enabled) {
            console.warn('Messenger redirect is disabled');
            return;
        }

        const browser = this.detectBrowser();
        const platform = this.detectPlatform();
        const urls = this.buildURL(formData);

        // 检查是否在内置浏览器中
        if (browser.isInApp) {
            // 内置浏览器：直接打开网页版，并提示用户
            this.showBrowserWarning(browser.message);
            window.open(urls.webUrl, '_blank');
            return;
        }

        if (platform === 'ios') {
            // iOS: 使用多级深度链接策略
            this.tryOpenIOSMessenger(urls);
        } else if (platform === 'android') {
            // Android: 使用Intent打开应用
            this.tryOpenAndroidMessenger(urls);
        } else {
            // Web: 在新标签页打开Messenger网页版
            window.open(urls.webUrl, '_blank');
        }
    }

    /**
     * 显示浏览器限制警告
     * @param {string} message - 警告消息
     */
    showBrowserWarning(message) {
        // 检查是否已存在警告元素
        if (document.getElementById('messenger-browser-warning')) {
            return;
        }

        const warningDiv = document.createElement('div');
        warningDiv.id = 'messenger-browser-warning';
        warningDiv.style.cssText = `
            position: fixed;
            top: 20px;
            left: 20px;
            right: 20px;
            background: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 8px;
            padding: 16px;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            max-width: 400px;
            margin: 0 auto;
        `;

        warningDiv.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; background: #ffc107; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div style="flex: 1;">
                    <p style="margin: 0; font-weight: 600; color: #856404; font-size: 14px;">Notice</p>
                    <p style="margin: 4px 0 0 0; color: #856404; font-size: 13px;">${message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; cursor: pointer; padding: 4px; color: #856404;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        `;

        document.body.appendChild(warningDiv);

        // 3秒后自动隐藏
        setTimeout(() => {
            if (document.body.contains(warningDiv)) {
                warningDiv.style.opacity = '0';
                warningDiv.style.transition = 'opacity 0.3s';
                setTimeout(() => warningDiv.remove(), 300);
            }
        }, 5000);
    }

    /**
     * iOS专用：多级深度链接策略
     */
    tryOpenIOSMessenger(urls) {
        const mMeUrl = urls.mMeUrl;
        const schemeUrl = urls.schemeUrl ? urls.schemeUrl : `fb-messenger://user-thread/${this.DEFAULT_PAGE_ID}?text=${urls.message}`;
        // alert(schemeUrl)
        // window.open(schemeUrl, '_blank');
        window.location.href = schemeUrl;
        // 设置回退：如果Intent失败，使用m.me链接
        setTimeout(() => {
            window.location.href = mMeUrl;
        }, 1500);
    }

    /**
     * Android专用：使用Intent打开Messenger应用
     */
    tryOpenAndroidMessenger(urls) {
        const mMeUrl = urls.mMeUrl;

        // 使用Intent Scheme直接打开Messenger应用
        const intentUrl = `intent://user-thread/${this.DEFAULT_PAGE_ID}?text=${urls.message}#Intent;scheme=fb-messenger;package=com.facebook.orca;end`;
        window.location.href = intentUrl;

        // 设置回退：如果Intent失败，使用m.me链接
        setTimeout(() => {
            window.location.href = mMeUrl;
        }, 1500);
    }

    /**
     * 获取可复制的Messenger链接（带预填充消息）
     * @param {object} formData - 表单数据对象
     * @returns {string} 可分享的链接
     */
    getShareableLink(formData) {
        const message = this.buildMessage(formData);
        return `https://m.me/${this.DEFAULT_PAGE_ID}?text=${message}`;
    }
}

// 全局实例
window.messengerManager = new MessengerManager();

// HTML escape
window.escapeHtml = function (text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
};

// Date format
window.formatDate = function (isoString) {
    const date = new Date(isoString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

/**
 * Auto-redirect to Messenger after page load
 * Called on thank-you.html page load
 */
window.autoRedirectToMessenger = async function () {
    const record = await csvManager.getLatestRecord();
    const formData = record || {};

    // Auto-redirect after 200ms delay
    setTimeout(function () {
        messengerManager.openMessenger(formData);
    }, 200);
};

/**
 * Start Messenger chat manually via button click
 * Detects platform and redirects accordingly
 */
window.startMessengerChat = async function () {
    const record = await csvManager.getLatestRecord();
    const formData = record || {};

    // Detect platform
    const platform = messengerManager.detectPlatform();

    if (platform === 'ios' || platform === 'android') {
        // Mobile: Try to open Messenger app
        messengerManager.openMessenger(formData);

        // Show notification about app
        // const loadingIndicator = document.getElementById('loading-indicator');
        // if (loadingIndicator) {
        //     loadingIndicator.innerHTML = `
        //         <div style="padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 6px; color: #155724; margin-top: 20px;">
        //             <p style="margin: 0;">Opening Messenger app...</p>
        //         </div>
        //     `;
        // }
    } else {
        // Desktop/Web: Open Messenger webpage
        const urls = messengerManager.buildURL(formData);
        window.open(urls.webUrl, '_blank');

        // Show notification
        // const loadingIndicator = document.getElementById('loading-indicator');
        // if (loadingIndicator) {
        //     loadingIndicator.innerHTML = `
        //         <div style="padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 6px; color: #155724; margin-top: 20px;">
        //             <p style="margin: 0;">Messenger opened in new tab. If it didn't open, <a href="${urls.webUrl}" target="_blank" style="color: #007bff;">click here</a>.</p>
        //         </div>
        //     `;
        // }
    }
};
