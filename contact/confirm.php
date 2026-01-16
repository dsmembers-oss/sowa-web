<?php
/**
 * お問い合わせフォーム - 確認画面
 *
 * @package SowaPlantMailForm
 * @version 1.0.0
 */

declare(strict_types=1);

// 設定ファイル読み込み
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';

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
    $_SESSION['form_errors'] = ['セキュリティトークンが無効です。もう一度お試しください。'];
    header('Location: index.php');
    exit;
}

// ハニーポットチェック
$honeypot = $_POST['website'] ?? '';
if (!checkHoneypot($honeypot)) {
    // ボット検出時はエラーを出さずにリダイレクト
    logError('Honeypot triggered', ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    header('Location: index.php');
    exit;
}

// 送信回数制限チェック
if (!checkSendLimit()) {
    $_SESSION['form_errors'] = ['送信回数の制限に達しました。しばらく時間をおいてから再度お試しください。'];
    header('Location: index.php');
    exit;
}

// フォームデータ取得とサニタイズ
$formData = [];
foreach (FORM_FIELDS as $field => $config) {
    $formData[$field] = cleanInput($_POST[$field] ?? '');
}

// バリデーション
$validation = validateFormData($formData);

if (!$validation['valid']) {
    $_SESSION['form_errors'] = $validation['errors'];
    saveFormData($formData);
    header('Location: index.php');
    exit;
}

// フォームデータをセッションに保存
saveFormData($formData);

// 新しいCSRFトークンを生成
$newCsrfToken = generateCsrfToken();

/**
 * フィールドの値を取得（XSSエスケープ済み）
 *
 * @param string $field
 * @return string
 */
function getFieldValue(string $field): string
{
    global $formData;
    return escapeHtml($formData[$field] ?? '');
}

/**
 * フィールドの値を改行付きで取得（XSSエスケープ済み）
 *
 * @param string $field
 * @return string
 */
function getFieldValueWithBr(string $field): string
{
    global $formData;
    return nl2br(escapeHtml($formData[$field] ?? ''));
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="株式会社相和プラントへのお問い合わせ確認ページです。">
    <title>お問い合わせ内容確認 | 株式会社相和プラント</title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&family=Josefin+Sans:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* 確認画面用スタイル */
        .confirm-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .confirm-table th,
        .confirm-table td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            vertical-align: top;
        }
        .confirm-table th {
            width: 180px;
            background-color: #f8f9fa;
            font-weight: 500;
            color: #333;
        }
        .confirm-table td {
            color: #555;
        }
        .confirm-note {
            background-color: #e8f4fd;
            border: 1px solid #1e88d2;
            border-radius: 4px;
            padding: 15px 20px;
            margin-bottom: 25px;
            text-align: center;
        }
        .confirm-note p {
            margin: 0;
            color: #1e88d2;
            font-weight: 500;
        }
        .form-submit {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .form-submit .btn-back {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .form-submit .btn-back:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
        @media (max-width: 768px) {
            .confirm-table th,
            .confirm-table td {
                display: block;
                width: 100%;
            }
            .confirm-table th {
                padding-bottom: 5px;
            }
            .confirm-table td {
                padding-top: 5px;
                padding-bottom: 20px;
            }
        }
    </style>
</head>

<body>

    <!-- Overlay for SP Menu -->
    <div class="overlay"></div>

    <!-- Header -->
    <header class="header">
        <div class="header-inner">
            <a href="../index.html" class="logo">
                <img src="../images/logo.png" alt="相和プラント">
            </a>
            <nav class="nav">
                <ul class="nav-menu">
                    <li><a href="../index.html" class="nav-home">HOME</a></li>
                    <li><a href="../works.html">事業内容</a></li>
                    <li><a href="../example.html">納入実績</a></li>
                    <li><a href="../company.html">会社概要</a></li>
                    <li><a href="index.php" class="current">お問い合わせ</a></li>
                </ul>
            </nav>
            <button class="menu-btn"><span></span></button>
        </div>
    </header>

    <!-- Page Header -->
    <section class="page-header page-header--contact">
        <h1>お問い合わせ内容確認</h1>
    </section>

    <!-- Main Content -->
    <main>

        <!-- Confirm Section -->
        <section class="section">
            <div class="container">
                <h3 class="heading">入力内容のご確認</h3>

                <div class="confirm-note">
                    <p>以下の内容でよろしければ「送信する」ボタンを押してください。</p>
                </div>

                <table class="confirm-table">
                    <tr>
                        <th>メールアドレス</th>
                        <td><?php echo getFieldValue('email'); ?></td>
                    </tr>
                    <tr>
                        <th>お名前</th>
                        <td><?php echo getFieldValue('name'); ?></td>
                    </tr>
                    <tr>
                        <th>法人名</th>
                        <td><?php echo getFieldValue('company'); ?></td>
                    </tr>
                    <?php if (!empty($formData['department'])): ?>
                    <tr>
                        <th>部署名</th>
                        <td><?php echo getFieldValue('department'); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($formData['job_title'])): ?>
                    <tr>
                        <th>役職名</th>
                        <td><?php echo getFieldValue('job_title'); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($formData['postcode'])): ?>
                    <tr>
                        <th>郵便番号</th>
                        <td><?php echo getFieldValue('postcode'); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($formData['address'])): ?>
                    <tr>
                        <th>住所</th>
                        <td><?php echo getFieldValueWithBr('address'); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($formData['tel'])): ?>
                    <tr>
                        <th>電話番号</th>
                        <td><?php echo getFieldValue('tel'); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($formData['fax'])): ?>
                    <tr>
                        <th>FAX番号</th>
                        <td><?php echo getFieldValue('fax'); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th>お問い合わせ内容</th>
                        <td><?php echo getFieldValueWithBr('message'); ?></td>
                    </tr>
                </table>

                <div class="form-submit">
                    <form action="mail.php" method="post" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo escapeHtml($newCsrfToken); ?>">
                        <button type="submit">送信する</button>
                    </form>
                    <form action="index.php" method="get" style="display: inline;">
                        <button type="submit" class="btn-reset btn-back">修正する</button>
                    </form>
                </div>

            </div>
        </section>

    </main>

    <!-- Banner Links -->
    <section class="banner-section">
        <div class="container">
            <div class="banner-links">
                <a href="http://wa-ltd.com/" target="_blank" rel="noopener noreferrer">
                    <img src="../images/banners/bn07.gif" alt="関連会社">
                </a>
                <a href="https://www.2.solars.jp/" target="_blank" rel="noopener noreferrer">
                    <img src="../images/banners/bn08.gif" alt="関連会社">
                </a>
                <a href="https://www.2.solars.jp/" target="_blank" rel="noopener noreferrer">
                    <img src="../images/banners/img122ea5826_1.png" alt="関連会社">
                </a>
            </div>
        </div>
    </section>

    <!-- Page Top -->
    <div class="page-top">
        <a href="#">▲ページトップへ</a>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <a href="index.php" class="btn">お問い合わせ</a>
            <p class="tel">
                <a href="tel:052-691-7536">TEL：052-691-7536</a>
            </p>
            <p class="fax">FAX：052-691-7930</p>
            <p class="company-name">株式会社 相和プラント</p>
            <address>
                〒457-0863 愛知県名古屋市南区豊2-33-1
            </address>
            <small>Copyright (C) 2026 Sowa plant Corporation. All Rights Reserved.</small>
        </div>
    </footer>

    <script src="../js/main.js"></script>
</body>

</html>
