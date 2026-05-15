<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        .test-section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
        }
        .test-section h2 {
            margin-top: 0;
            font-size: 18px;
            color: #555;
        }
        button {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background: #2980b9;
        }
        .result {
            margin-top: 15px;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow-x: auto;
        }
        .success {
            background: #d4edda !important;
            border-color: #c3e6cb !important;
            color: #155724;
        }
        .error {
            background: #f8d7da !important;
            border-color: #f5c6cb !important;
            color: #721c24;
        }
        pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 API 测试页面</h1>
        
        <div class="test-section">
            <h2>1. 测试表单列表 API</h2>
            <button onclick="testListAPI()">测试列表 API</button>
            <div id="list-result" class="result" style="display: none;"></div>
        </div>
        
        <div class="test-section">
            <h2>2. 测试表单详情 API</h2>
            <div style="margin-bottom: 10px;">
                <label>记录 ID: </label>
                <input type="number" id="detail-id" value="1" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <button onclick="testDetailAPI()">测试详情 API</button>
            <div id="detail-result" class="result" style="display: none;"></div>
        </div>
        
        <div class="test-section">
            <h2>3. 直接访问 API 链接</h2>
            <a href="forms-api.php?action=list" target="_blank" style="color: #3498db; text-decoration: none;">forms-api.php?action=list</a>
            <br>
            <small>在新标签页中打开，查看原始响应</small>
        </div>
    </div>

    <script>
        async function testListAPI() {
            const resultDiv = document.getElementById('list-result');
            resultDiv.style.display = 'block';
            resultDiv.className = 'result';
            resultDiv.innerHTML = '<pre>加载中...</pre>';
            
            try {
                const response = await fetch('forms-api.php?action=list');
                const text = await response.text();
                
                console.log('Raw response:', text);
                
                try {
                    const json = JSON.parse(text);
                    resultDiv.innerHTML = '<pre>' + JSON.stringify(json, null, 2) + '</pre>';
                    if (json.success) {
                        resultDiv.classList.add('success');
                        resultDiv.innerHTML = `<p>✅ 成功！共 ${json.data.length} 条记录</p>` + resultDiv.innerHTML;
                    } else {
                        resultDiv.classList.add('error');
                    }
                } catch (e) {
                    resultDiv.classList.add('error');
                    resultDiv.innerHTML = '<pre>❌ JSON 解析失败！\n\n原始响应：\n' + text + '\n\n错误：' + e.message + '</pre>';
                }
            } catch (e) {
                resultDiv.classList.add('error');
                resultDiv.innerHTML = '<pre>❌ 请求失败！\n\n错误：' + e.message + '</pre>';
            }
        }
        
        async function testDetailAPI() {
            const id = document.getElementById('detail-id').value;
            const resultDiv = document.getElementById('detail-result');
            resultDiv.style.display = 'block';
            resultDiv.className = 'result';
            resultDiv.innerHTML = '<pre>加载中...</pre>';
            
            try {
                const response = await fetch('forms-api.php?action=detail&id=' + id);
                const text = await response.text();
                
                try {
                    const json = JSON.parse(text);
                    resultDiv.innerHTML = '<pre>' + JSON.stringify(json, null, 2) + '</pre>';
                    if (json.success) {
                        resultDiv.classList.add('success');
                    } else {
                        resultDiv.classList.add('error');
                    }
                } catch (e) {
                    resultDiv.classList.add('error');
                    resultDiv.innerHTML = '<pre>❌ JSON 解析失败！\n\n原始响应：\n' + text + '</pre>';
                }
            } catch (e) {
                resultDiv.classList.add('error');
                resultDiv.innerHTML = '<pre>❌ 请求失败！\n\n错误：' + e.message + '</pre>';
            }
        }
    </script>
</body>
</html>