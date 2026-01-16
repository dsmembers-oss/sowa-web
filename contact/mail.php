<?php
/**
 * お問い合わせフォーム - メール送信処理
 *
 * @package SowaPlantMailForm
 * @version 1.0.0
 */

declare(strict_types=1);

// 設定ファイル読み込み
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/database.php';

// セキュリティヘッダー設定
setSecurityHeaders();

// セッション開始
startSecureSession();

// POSTリクエストのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// CSRFトークン検証
$csrfToken = $_POST['csrf_token'] ?? '';
if (!validateCsrfToken($csrfToken)) {
    $_SESSION['form_errors'] = ['セキュリティトークンが無効です。もう一度最初からお試しください。'];
    header('Location: index.php');
    exit;
}

// 送信回数制限チェック
if (!checkSendLimit()) {
    $_SESSION['form_errors'] = ['送信回数の制限に達しました。しばらく時間をおいてから再度お試しください。'];
    header('Location: index.php');
    exit;
}

// セッションからフォームデータを取得
$formData = getFormData();

if (empty($formData)) {
    $_SESSION['form_errors'] = ['フォームデータが見つかりません。もう一度最初からお試しください。'];
    header('Location: index.php');
    exit;
}

// 再度バリデーション（セキュリティ強化）
$validation = validateFormData($formData);

if (!$validation['valid']) {
    $_SESSION['form_errors'] = $validation['errors'];
    header('Location: index.php');
    exit;
}

/**
 * メールヘッダーインジェクション対策
 *
 * @param string $value
 * @return string
 */
function sanitizeMailHeader(string $value): string
{
    return str_replace(["\r", "\n", "%0a", "%0d"], '', $value);
}

/**
 * 管理者向けメール本文を作成
 *
 * @param array $data
 * @return string
 */
function createAdminMailBody(array $data): string
{
    $datetime = date('Y年m月d日 H:i:s');

    $body = <<<EOT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
【お問い合わせがありました】
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

ホームページよりお問い合わせがありました。

【受信日時】
{$datetime}

【お問い合わせ内容】
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

■メールアドレス
{$data['email']}

■お名前
{$data['name']}

■法人名
{$data['company']}

EOT;

    if (!empty($data['department'])) {
        $body .= "■部署名\n{$data['department']}\n\n";
    }

    if (!empty($data['job_title'])) {
        $body .= "■役職名\n{$data['job_title']}\n\n";
    }

    if (!empty($data['postcode'])) {
        $body .= "■郵便番号\n{$data['postcode']}\n\n";
    }

    if (!empty($data['address'])) {
        $body .= "■住所\n{$data['address']}\n\n";
    }

    if (!empty($data['tel'])) {
        $body .= "■電話番号\n{$data['tel']}\n\n";
    }

    if (!empty($data['fax'])) {
        $body .= "■FAX番号\n{$data['fax']}\n\n";
    }

    $body .= <<<EOT
■お問い合わせ内容
{$data['message']}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

【送信者情報】
IPアドレス: {$_SERVER['REMOTE_ADDR']}
ユーザーエージェント: {$_SERVER['HTTP_USER_AGENT']}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
EOT;

    return $body;
}

/**
 * ユーザー向け自動返信メール本文を作成
 *
 * @param array $data
 * @return string
 */
function createUserMailBody(array $data): string
{
    $siteName = SITE_NAME;

    $body = <<<EOT
{$data['name']} 様

この度は、{$siteName}にお問い合わせいただき、
誠にありがとうございます。

以下の内容でお問い合わせを受け付けいたしました。
内容を確認の上、担当者より折り返しご連絡いたしますので、
今しばらくお待ちくださいますようお願い申し上げます。

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
【お問い合わせ内容】
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

■メールアドレス
{$data['email']}

■お名前
{$data['name']}

■法人名
{$data['company']}

EOT;

    if (!empty($data['department'])) {
        $body .= "■部署名\n{$data['department']}\n\n";
    }

    if (!empty($data['job_title'])) {
        $body .= "■役職名\n{$data['job_title']}\n\n";
    }

    if (!empty($data['postcode'])) {
        $body .= "■郵便番号\n{$data['postcode']}\n\n";
    }

    if (!empty($data['address'])) {
        $body .= "■住所\n{$data['address']}\n\n";
    }

    if (!empty($data['tel'])) {
        $body .= "■電話番号\n{$data['tel']}\n\n";
    }

    if (!empty($data['fax'])) {
        $body .= "■FAX番号\n{$data['fax']}\n\n";
    }

    $body .= <<<EOT
■お問い合わせ内容
{$data['message']}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

※このメールは自動送信されています。
※このメールに心当たりがない場合は、お手数ですが
  下記連絡先までご一報ください。

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
{$siteName}
〒457-0863 愛知県名古屋市南区豊2-33-1
TEL: 052-691-7536
FAX: 052-691-7930
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
EOT;

    return $body;
}

/**
 * メールを送信
 *
 * @param string $to
 * @param string $subject
 * @param string $body
 * @param string $fromEmail
 * @param string $fromName
 * @param string $replyTo
 * @return bool
 */
function sendMail(
    string $to,
    string $subject,
    string $body,
    string $fromEmail,
    string $fromName = '',
    string $replyTo = ''
): bool {
    // テストモードの場合はファイルに保存
    if (defined('TEST_MODE') && TEST_MODE) {
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/mail_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.txt';
        $logContent = "========================================\n";
        $logContent .= "【テストモード - メール送信ログ】\n";
        $logContent .= "========================================\n";
        $logContent .= "送信日時: " . date('Y-m-d H:i:s') . "\n";
        $logContent .= "宛先: {$to}\n";
        $logContent .= "件名: {$subject}\n";
        $logContent .= "差出人: {$fromName} <{$fromEmail}>\n";
        $logContent .= "返信先: {$replyTo}\n";
        $logContent .= "========================================\n";
        $logContent .= "【本文】\n";
        $logContent .= "========================================\n";
        $logContent .= $body . "\n";
        $logContent .= "========================================\n";

        file_put_contents($logFile, $logContent);
        return true;
    }

    // ヘッダーのサニタイズ
    $to = sanitizeMailHeader($to);
    $subject = sanitizeMailHeader($subject);
    $fromEmail = sanitizeMailHeader($fromEmail);
    $fromName = sanitizeMailHeader($fromName);
    $replyTo = sanitizeMailHeader($replyTo);

    // メールヘッダー作成
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = 'Content-Transfer-Encoding: base64';

    if (!empty($fromName)) {
        $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
        $headers[] = "From: {$encodedFromName} <{$fromEmail}>";
    } else {
        $headers[] = "From: {$fromEmail}";
    }

    if (!empty($replyTo)) {
        $headers[] = "Reply-To: {$replyTo}";
    }

    // 件名をエンコード
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    // 本文をBase64エンコード
    $encodedBody = base64_encode($body);

    // メール送信
    return mail($to, $encodedSubject, $encodedBody, implode("\r\n", $headers));
}

// メール送信処理
$sendSuccess = true;

// 1. 管理者向けメール送信
$adminMailBody = createAdminMailBody($formData);
$adminResult = sendMail(
    ADMIN_EMAIL,
    MAIL_SUBJECT_ADMIN,
    $adminMailBody,
    FROM_EMAIL,
    SITE_NAME,
    $formData['email']
);

if (!$adminResult) {
    $sendSuccess = false;
    logError('Failed to send admin mail', [
        'to' => ADMIN_EMAIL,
        'from' => $formData['email']
    ]);
}

// 2. ユーザー向け自動返信メール送信
$userMailBody = createUserMailBody($formData);
$userResult = sendMail(
    $formData['email'],
    MAIL_SUBJECT_USER,
    $userMailBody,
    FROM_EMAIL,
    SITE_NAME,
    REPLY_TO_EMAIL
);

if (!$userResult) {
    // ユーザーへの返信が失敗しても、管理者には届いていれば処理続行
    logError('Failed to send user confirmation mail', [
        'to' => $formData['email']
    ]);
}

// 送信成功時の処理
if ($sendSuccess) {
    // データベースに保存（DB機能が有効な場合のみ）
    if (DB_ENABLED) {
        $dbResult = saveInquiry($formData);
        if ($dbResult === false) {
            // DB保存に失敗してもメールは送信済みなので処理を続行
            logError('Failed to save inquiry to database', [
                'email' => $formData['email'],
                'name' => $formData['name']
            ]);
        }
    }

    // 送信回数をインクリメント
    incrementSendCount();

    // フォームデータをクリア
    clearFormData();

    // CSRFトークンを再生成
    generateCsrfToken();

    // 完了画面へリダイレクト
    $_SESSION['mail_sent'] = true;
    header('Location: thanks.php');
    exit;
} else {
    // 送信失敗時
    $_SESSION['form_errors'] = ['メールの送信に失敗しました。お手数ですが、お電話にてお問い合わせください。'];
    header('Location: index.php');
    exit;
}
