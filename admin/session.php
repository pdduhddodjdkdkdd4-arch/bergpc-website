<?php
session_start();

function isLoggedIn() {
    if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
        return false;
    }
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        header('Location: ' . $base . '/login.php');
        exit;
    }
}

function login($username, $password) {
    try {
        require_once __DIR__ . '/db.php';
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare('SELECT password_hash FROM admin_users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($password, $row['password_hash'])) {
            admin_set_session($username);
            return true;
        }

        if (defined('ADMIN_USERNAME') && defined('ADMIN_FALLBACK_PASSWORD')) {
            if ($username === ADMIN_USERNAME && $password === ADMIN_FALLBACK_PASSWORD) {
                admin_ensure_db_user($db, $username, $password);
                admin_set_session($username);
                return true;
            }
        }

        return false;
    } catch (Exception $e) {
        if (defined('ADMIN_USERNAME') && defined('ADMIN_FALLBACK_PASSWORD')) {
            if ($username === ADMIN_USERNAME && $password === ADMIN_FALLBACK_PASSWORD) {
                admin_set_session($username);
                return true;
            }
        }
        return false;
    }
}

function admin_set_session($username) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_user'] = $username;
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
}

function admin_ensure_db_user($db, $username, $password) {
    $stmt = $db->prepare('SELECT id FROM admin_users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    if (!$stmt->fetch()) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $db->prepare('INSERT INTO admin_users (username, password_hash) VALUES (:username, :hash)');
        $insert->execute([':username' => $username, ':hash' => $hash]);
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $update = $db->prepare('UPDATE admin_users SET password_hash = :hash WHERE username = :username');
        $update->execute([':hash' => $hash, ':username' => $username]);
    }
}

function logout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

function generateCsrfToken() {
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();
    return $token;
}

function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    if (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_LIFETIME) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
