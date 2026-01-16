<?php
/**
 * セキュリティ関数ライブラリ
 *
 * @package SowaPlantMailForm
 * @version 1.0.0
 */

declare(strict_types=1);

/**
 * セキュリティヘッダーを設定
 *
 * @return void
 */
function setSecurityHeaders(): void
{
    // XSS対策
    header('X-XSS-Protection: 1; mode=block');
    // クリックジャッキング対策
    header('X-Frame-Options: DENY');
    // MIMEタイプスニッフィング対策
    header('X-Content-Type-Options: nosniff');
    // リファラーポリシー
    header('Referrer-Policy: strict-origin-when-cross-origin');
    // コンテンツセキュリティポリシー
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:;");
}

/**
 * セッションを安全に開始
 *
 * @return void
 */
function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // セッション固定攻撃対策：一定時間でセッションIDを再生成
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } elseif (time() - $_SESSION['created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

/**
 * CSRFトークンを生成
 *
 * @return string
 */
function generateCsrfToken(): string
{
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();
    return $token;
}

/**
 * CSRFトークンを検証
 *
 * @param string $token
 * @return bool
 */
function validateCsrfToken(string $token): bool
{
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time'])) {
        return false;
    }

    // トークンの有効期限チェック
    if (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_EXPIRE) {
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
        return false;
    }

    // トークンの一致チェック（タイミング攻撃対策）
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * リファラーをチェック
 *
 * @return bool
 */
function checkReferer(): bool
{
    if (empty($_SERVER['HTTP_REFERER'])) {
        return false;
    }

    $referer = parse_url($_SERVER['HTTP_REFERER']);
    $server = parse_url(SITE_URL);

    // ホスト名が一致するかチェック
    return isset($referer['host']) && isset($server['host'])
        && $referer['host'] === $server['host'];
}

/**
 * 送信回数制限をチェック
 *
 * @return bool true: 送信可能, false: 制限中
 */
function checkSendLimit(): bool
{
    $currentTime = time();

    // 初回送信の場合
    if (!isset($_SESSION['send_count']) || !isset($_SESSION['send_first_time'])) {
        $_SESSION['send_count'] = 0;
        $_SESSION['send_first_time'] = $currentTime;
    }

    // 制限時間が過ぎていたらリセット
    if ($currentTime - $_SESSION['send_first_time'] > SEND_LIMIT_TIME) {
        $_SESSION['send_count'] = 0;
        $_SESSION['send_first_time'] = $currentTime;
    }

    // 送信回数チェック
    return $_SESSION['send_count'] < SEND_LIMIT_COUNT;
}

/**
 * 送信回数をインクリメント
 *
 * @return void
 */
function incrementSendCount(): void
{
    if (!isset($_SESSION['send_count'])) {
        $_SESSION['send_count'] = 0;
        $_SESSION['send_first_time'] = time();
    }
    $_SESSION['send_count']++;
}

/**
 * ハニーポットをチェック
 *
 * @param string $honeypotValue
 * @return bool true: 正常, false: ボット検出
 */
function checkHoneypot(string $honeypotValue): bool
{
    return empty($honeypotValue);
}

/**
 * 入力値をサニタイズ
 *
 * @param string $value
 * @return string
 */
function sanitizeInput(string $value): string
{
    // 基本サニタイズ
    $value = trim($value);
    $value = stripslashes($value);

    // NULLバイト除去
    $value = str_replace("\0", '', $value);

    // 改行コードを統一
    $value = str_replace(["\r\n", "\r"], "\n", $value);

    return $value;
}

/**
 * XSS対策のためのエスケープ（HTML出力用）
 *
 * @param string $value
 * @return string
 */
function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * JavaScriptとイベントハンドラーを除去
 *
 * @param string $value
 * @return string
 */
function removeJavaScript(string $value): string
{
    // scriptタグを除去
    $value = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $value);

    // イベントハンドラー属性を除去
    $eventHandlers = [
        'onabort', 'onblur', 'onchange', 'onclick', 'ondblclick', 'onerror',
        'onfocus', 'onkeydown', 'onkeypress', 'onkeyup', 'onload', 'onmousedown',
        'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onreset',
        'onresize', 'onscroll', 'onselect', 'onsubmit', 'onunload'
    ];

    foreach ($eventHandlers as $handler) {
        $value = preg_replace('/\s*' . $handler . '\s*=\s*["\'][^"\']*["\']/i', '', $value);
        $value = preg_replace('/\s*' . $handler . '\s*=\s*[^\s>]*/i', '', $value);
    }

    // javascript:プロトコルを除去
    $value = preg_replace('/javascript\s*:/i', '', $value);

    // data:プロトコルを除去（XSS対策）
    $value = preg_replace('/data\s*:/i', '', $value);

    return $value;
}

/**
 * 入力値を完全にクリーンアップ
 *
 * @param string $value
 * @param bool $stripTags HTMLタグを除去するか
 * @return string
 */
function cleanInput(string $value, bool $stripTags = true): string
{
    $value = sanitizeInput($value);
    $value = removeJavaScript($value);

    if ($stripTags) {
        $value = strip_tags($value);
    }

    return $value;
}

/**
 * メールアドレスをバリデーション
 *
 * @param string $email
 * @return bool
 */
function validateEmail(string $email): bool
{
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * 電話番号をバリデーション
 *
 * @param string $tel
 * @return bool
 */
function validateTel(string $tel): bool
{
    if (empty($tel)) {
        return true; // 空の場合はOK（必須でない場合）
    }
    // 数字、ハイフン、括弧、スペースのみ許可
    return preg_match('/^[\d\-\(\)\s]+$/', $tel) === 1;
}

/**
 * 郵便番号をバリデーション
 *
 * @param string $postcode
 * @return bool
 */
function validatePostcode(string $postcode): bool
{
    if (empty($postcode)) {
        return true; // 空の場合はOK（必須でない場合）
    }
    // 日本の郵便番号形式（XXX-XXXX または XXXXXXX）
    return preg_match('/^\d{3}-?\d{4}$/', $postcode) === 1;
}

/**
 * 禁止ワードをチェック
 *
 * @param string $value
 * @return bool true: 問題なし, false: 禁止ワード検出
 */
function checkForbiddenWords(string $value): bool
{
    $lowerValue = mb_strtolower($value, 'UTF-8');

    foreach (FORBIDDEN_WORDS as $word) {
        if (mb_strpos($lowerValue, mb_strtolower($word, 'UTF-8')) !== false) {
            return false;
        }
    }

    return true;
}

/**
 * フォームデータをバリデーション
 *
 * @param array $data
 * @return array ['valid' => bool, 'errors' => array]
 */
function validateFormData(array $data): array
{
    $errors = [];

    foreach (FORM_FIELDS as $field => $config) {
        $value = $data[$field] ?? '';

        // 必須チェック
        if ($config['required'] && empty($value)) {
            $errors[$field] = $config['label'] . 'は必須項目です。';
            continue;
        }

        // 空の場合はスキップ（必須でないフィールド）
        if (empty($value)) {
            continue;
        }

        // 文字数チェック
        if (mb_strlen($value, 'UTF-8') > $config['max_length']) {
            $errors[$field] = $config['label'] . 'は' . $config['max_length'] . '文字以内で入力してください。';
            continue;
        }

        // 形式チェック
        switch ($config['type']) {
            case 'email':
                if (!validateEmail($value)) {
                    $errors[$field] = '正しいメールアドレスの形式で入力してください。';
                }
                break;

            case 'tel':
                if (!validateTel($value)) {
                    $errors[$field] = '正しい電話番号の形式で入力してください。';
                }
                break;

            case 'postcode':
                if (!validatePostcode($value)) {
                    $errors[$field] = '正しい郵便番号の形式で入力してください。（例：457-0863）';
                }
                break;
        }

        // 禁止ワードチェック
        if (!checkForbiddenWords($value)) {
            $errors[$field] = $config['label'] . 'に不適切な内容が含まれています。';
        }
    }

    // メールアドレス一致チェック
    if (
        !empty($data['email']) && !empty($data['email_confirm'])
        && $data['email'] !== $data['email_confirm']
    ) {
        $errors['email_confirm'] = 'メールアドレスが一致しません。';
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * フォームデータをセッションに保存
 *
 * @param array $data
 * @return void
 */
function saveFormData(array $data): void
{
    $_SESSION['form_data'] = [];

    foreach (FORM_FIELDS as $field => $config) {
        $_SESSION['form_data'][$field] = cleanInput($data[$field] ?? '');
    }
}

/**
 * セッションからフォームデータを取得
 *
 * @return array
 */
function getFormData(): array
{
    return $_SESSION['form_data'] ?? [];
}

/**
 * フォームデータをセッションから削除
 *
 * @return void
 */
function clearFormData(): void
{
    unset($_SESSION['form_data']);
}

/**
 * エラーログを記録
 *
 * @param string $message
 * @param array $context
 * @return void
 */
function logError(string $message, array $context = []): void
{
    $logMessage = date('[Y-m-d H:i:s] ') . $message;

    if (!empty($context)) {
        $logMessage .= ' Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
    }

    error_log($logMessage);
}

/**
 * デバッグ情報を出力（開発環境のみ）
 *
 * @param mixed $data
 * @param string $label
 * @return void
 */
function debugLog($data, string $label = ''): void
{
    if (!DEBUG_MODE) {
        return;
    }

    $output = $label ? "[$label] " : '';
    $output .= print_r($data, true);
    error_log($output);
}
