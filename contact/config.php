<?php
/**
 * メールフォーム設定ファイル
 *
 * @package SowaPlantMailForm
 * @version 1.0.0
 */

declare(strict_types=1);

// エラー表示設定（本番環境では0に設定）
ini_set('display_errors', '0');
error_reporting(E_ALL);

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// セッション設定
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_samesite', 'Strict');

/**
 * 基本設定
 */
define('SITE_NAME', '株式会社相和プラント');
define('SITE_URL', 'https://example.com'); // 本番環境のURLに変更してください
define('ADMIN_EMAIL', 'info@sowa-plant.co.jp'); // 管理者メールアドレス
define('FROM_EMAIL', 'noreply@sowa-plant.co.jp'); // 送信元メールアドレス
define('REPLY_TO_EMAIL', 'info@sowa-plant.co.jp'); // 返信先メールアドレス

/**
 * メール件名
 */
define('MAIL_SUBJECT_ADMIN', '【' . SITE_NAME . '】お問い合わせがありました');
define('MAIL_SUBJECT_USER', '【' . SITE_NAME . '】お問い合わせありがとうございます');

/**
 * セキュリティ設定
 */
// CSRF トークン有効期限（秒）
define('CSRF_TOKEN_EXPIRE', 3600);

// 送信回数制限（同一セッションでの制限回数）
define('SEND_LIMIT_COUNT', 3);

// 送信回数制限のリセット時間（秒）
define('SEND_LIMIT_TIME', 3600);

// 文字数制限
define('MAX_LENGTH_NAME', 100);
define('MAX_LENGTH_EMAIL', 255);
define('MAX_LENGTH_COMPANY', 200);
define('MAX_LENGTH_DEPARTMENT', 100);
define('MAX_LENGTH_JOB_TITLE', 100);
define('MAX_LENGTH_POSTCODE', 10);
define('MAX_LENGTH_ADDRESS', 500);
define('MAX_LENGTH_TEL', 20);
define('MAX_LENGTH_FAX', 20);
define('MAX_LENGTH_MESSAGE', 5000);

/**
 * 禁止ワード設定
 */
define('FORBIDDEN_WORDS', [
    'http://',
    'https://',
    '[url=',
    '<a href',
    'viagra',
    'casino',
    'porn',
    'xxx',
    'adult',
    'sex',
    'gambling',
    'lottery',
    'bitcoin',
    'cryptocurrency',
]);

/**
 * フォームフィールド定義
 */
define('FORM_FIELDS', [
    'email' => [
        'label' => 'メールアドレス',
        'required' => true,
        'type' => 'email',
        'max_length' => MAX_LENGTH_EMAIL,
    ],
    'email_confirm' => [
        'label' => '確認用メールアドレス',
        'required' => true,
        'type' => 'email',
        'max_length' => MAX_LENGTH_EMAIL,
    ],
    'name' => [
        'label' => 'お名前',
        'required' => true,
        'type' => 'text',
        'max_length' => MAX_LENGTH_NAME,
    ],
    'company' => [
        'label' => '法人名',
        'required' => true,
        'type' => 'text',
        'max_length' => MAX_LENGTH_COMPANY,
    ],
    'department' => [
        'label' => '部署名',
        'required' => false,
        'type' => 'text',
        'max_length' => MAX_LENGTH_DEPARTMENT,
    ],
    'job_title' => [
        'label' => '役職名',
        'required' => false,
        'type' => 'text',
        'max_length' => MAX_LENGTH_JOB_TITLE,
    ],
    'postcode' => [
        'label' => '郵便番号',
        'required' => false,
        'type' => 'postcode',
        'max_length' => MAX_LENGTH_POSTCODE,
    ],
    'address' => [
        'label' => '住所',
        'required' => false,
        'type' => 'textarea',
        'max_length' => MAX_LENGTH_ADDRESS,
    ],
    'tel' => [
        'label' => '電話番号',
        'required' => false,
        'type' => 'tel',
        'max_length' => MAX_LENGTH_TEL,
    ],
    'fax' => [
        'label' => 'FAX番号',
        'required' => false,
        'type' => 'tel',
        'max_length' => MAX_LENGTH_FAX,
    ],
    'message' => [
        'label' => 'お問い合わせ内容',
        'required' => true,
        'type' => 'textarea',
        'max_length' => MAX_LENGTH_MESSAGE,
    ],
]);

/**
 * デバッグモード（本番環境ではfalseに設定）
 */
define('DEBUG_MODE', false);

/**
 * テストモード（本番環境ではfalseに設定）
 * trueの場合、メール送信の代わりにログファイルに保存
 */
define('TEST_MODE', true);

/**
 * データベース設定
 * 本番環境では適切な値に変更してください
 */
// DB機能の有効/無効（true: 有効, false: 無効）
define('DB_ENABLED', false);

// データベース接続情報
define('DB_HOST', 'localhost');
define('DB_NAME', 'sowa_plant');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_CHARSET', 'utf8mb4');
