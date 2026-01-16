<?php
/**
 * お問い合わせフォーム - 入力画面
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

// CSRFトークン生成
$csrfToken = generateCsrfToken();

// エラーメッセージ
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);

// 保存されたフォームデータを取得
$formData = getFormData();

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
 * エラーメッセージを取得
 *
 * @param string $field
 * @return string
 */
function getError(string $field): string
{
    global $errors;
    if (isset($errors[$field])) {
        return '<span class="error-message">' . escapeHtml($errors[$field]) . '</span>';
    }
    return '';
}

/**
 * エラークラスを取得
 *
 * @param string $field
 * @return string
 */
function getErrorClass(string $field): string
{
    global $errors;
    return isset($errors[$field]) ? ' has-error' : '';
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="株式会社相和プラントへのお問い合わせページです。ダクト工事、プラント設計、脱臭装置、集塵装置などに関するご相談はこちらから。">
    <title>お問い合わせ | 株式会社相和プラント</title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&family=Josefin+Sans:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* エラー表示用スタイル */
        .error-message {
            color: #e74c3c;
            font-size: 13px;
            display: block;
            margin-top: 5px;
        }
        .has-error input,
        .has-error textarea {
            border-color: #e74c3c;
            background-color: #fff5f5;
        }
        .error-summary {
            background-color: #fff5f5;
            border: 1px solid #e74c3c;
            border-radius: 4px;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        .error-summary h4 {
            color: #e74c3c;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .error-summary ul {
            margin: 0;
            padding-left: 20px;
        }
        .error-summary li {
            color: #e74c3c;
            font-size: 14px;
            margin-bottom: 5px;
        }
        /* ハニーポット（非表示） */
        .hp-field {
            position: absolute;
            left: -9999px;
            opacity: 0;
            height: 0;
            width: 0;
            overflow: hidden;
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
        <h1>お問い合わせ</h1>
    </section>

    <!-- Main Content -->
    <main>

        <!-- Contact Form Section -->
        <section class="section">
            <div class="container">
                <h3 class="heading">お問い合わせフォーム</h3>
                <p class="form-intro">
                    以下のフォームに入力のうえ、[確認画面へ]ボタンをクリックしてください。<br>
                    <span class="required-note">*は必須項目です。必ずご入力ください。</span><br>
                    （株）、（有）や丸囲み数字等の機種依存文字は、<br>
                    文字化けの原因となりますので使用しないようお願い致します。
                </p>

                <?php if (!empty($errors)): ?>
                <div class="error-summary">
                    <h4>入力内容にエラーがあります</h4>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo escapeHtml($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form class="form" action="confirm.php" method="post" novalidate>
                    <!-- CSRF トークン -->
                    <input type="hidden" name="csrf_token" value="<?php echo escapeHtml($csrfToken); ?>">

                    <!-- ハニーポット（スパム対策） -->
                    <div class="hp-field" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="form-group<?php echo getErrorClass('email'); ?>">
                        <label for="email">メールアドレス <span class="required">*</span></label>
                        <input type="email" id="email" name="email" placeholder="例：info@example.com"
                            value="<?php echo getFieldValue('email'); ?>" required>
                        <?php echo getError('email'); ?>
                    </div>

                    <div class="form-group<?php echo getErrorClass('email_confirm'); ?>">
                        <label for="email_confirm">確認用メールアドレス <span class="required">*</span></label>
                        <input type="email" id="email_confirm" name="email_confirm" placeholder="確認のため再度入力してください"
                            value="<?php echo getFieldValue('email_confirm'); ?>" required>
                        <?php echo getError('email_confirm'); ?>
                    </div>

                    <div class="form-group<?php echo getErrorClass('name'); ?>">
                        <label for="name">お名前 <span class="required">*</span></label>
                        <input type="text" id="name" name="name" placeholder="例：山田 太郎"
                            value="<?php echo getFieldValue('name'); ?>" required>
                        <?php echo getError('name'); ?>
                    </div>

                    <div class="form-group<?php echo getErrorClass('company'); ?>">
                        <label for="company">法人名 <span class="required">*</span></label>
                        <input type="text" id="company" name="company" placeholder="例：株式会社○○"
                            value="<?php echo getFieldValue('company'); ?>" required>
                        <?php echo getError('company'); ?>
                    </div>

                    <div class="form-group<?php echo getErrorClass('department'); ?>">
                        <label for="department">部署名</label>
                        <input type="text" id="department" name="department" placeholder="例：営業部"
                            value="<?php echo getFieldValue('department'); ?>">
                        <?php echo getError('department'); ?>
                    </div>

                    <div class="form-group<?php echo getErrorClass('job_title'); ?>">
                        <label for="job_title">役職名</label>
                        <input type="text" id="job_title" name="job_title" placeholder="例：部長"
                            value="<?php echo getFieldValue('job_title'); ?>">
                        <?php echo getError('job_title'); ?>
                    </div>

                    <div class="form-group<?php echo getErrorClass('postcode'); ?>">
                        <label for="postcode">郵便番号</label>
                        <input type="text" id="postcode" name="postcode" placeholder="例：457-0863"
                            value="<?php echo getFieldValue('postcode'); ?>">
                        <?php echo getError('postcode'); ?>
                    </div>

                    <div class="form-group<?php echo getErrorClass('address'); ?>">
                        <label for="address">住所</label>
                        <textarea id="address" name="address" rows="3"
                            placeholder="例：愛知県名古屋市南区豊2-33-1"><?php echo getFieldValue('address'); ?></textarea>
                        <?php echo getError('address'); ?>
                    </div>

                    <div class="form-group<?php echo getErrorClass('tel'); ?>">
                        <label for="tel">電話番号</label>
                        <input type="tel" id="tel" name="tel" placeholder="例：052-691-7536"
                            value="<?php echo getFieldValue('tel'); ?>">
                        <?php echo getError('tel'); ?>
                    </div>

                    <div class="form-group<?php echo getErrorClass('fax'); ?>">
                        <label for="fax">FAX番号</label>
                        <input type="tel" id="fax" name="fax" placeholder="例：052-691-7930"
                            value="<?php echo getFieldValue('fax'); ?>">
                        <?php echo getError('fax'); ?>
                    </div>

                    <div class="form-group<?php echo getErrorClass('message'); ?>">
                        <label for="message">お問い合わせ内容 <span class="required">*</span></label>
                        <textarea id="message" name="message" rows="6"
                            placeholder="お問い合わせ内容をご記入ください" required><?php echo getFieldValue('message'); ?></textarea>
                        <?php echo getError('message'); ?>
                    </div>

                    <p class="form-note" style="color: #e74c3c;">*は必須項目です。</p>

                    <div class="form-submit">
                        <button type="submit">確認画面へ</button>
                        <button type="reset" class="btn-reset">リセット</button>
                    </div>

                </form>
            </div>
        </section>

        <!-- Privacy Policy Section -->
        <section class="section section--bg">
            <div class="container">
                <h3 class="heading">プライバシーポリシー</h3>
                <div class="policy-content">
                    <p>
                        株式会社相和プラント（以下「当社」）は、個人情報（氏名、住所、生年月日、電話番号、その他の個人情報）の重要性を認識し、以下のガイドラインに基づいて、適切な取り扱いと保護の徹底に努めます。<br>
                        当社ホームページのご利用にあたり、お客様には当プライバシーポリシーにご同意いただいたものとさせていただきます。
                    </p>

                    <h4>個人情報の管理・保護について</h4>
                    <p>
                        当社が収集したお客様の個人情報については、適切な管理を行い、紛失・破壊・改ざん・不正アクセス・漏えいなどの防止に努めます。<br>
                        取得したお客様の個人情報について、お客様の同意なく開示することはございません。<br>
                        また、当社サイトへのアクセスにより、他のお客様が個人情報を閲覧されることはございません。
                    </p>

                    <h4>個人情報の利用について</h4>
                    <p>お客様の個人情報は、以下の目的で利用いたします。</p>
                    <ul class="compact-list">
                        <li>お客様にサービスや商品の情報を的確にお伝えするため</li>
                        <li>お客様がサービスをご利用になる際の身分証明のため</li>
                        <li>より満足していただけるサイトへと改善するため</li>
                        <li>必要に応じてお客様に連絡を行うため</li>
                    </ul>

                    <h4>個人情報の第三者への開示、提供について</h4>
                    <p>
                        ご本人の許可なく第三者に個人情報を開示または提供することは原則的にいたしません。<br>
                        個人情報を第三者に提供する際には、個人情報保護法その他関連する法規に従い、収集及び利用目的の範囲内で行います。
                    </p>

                    <h4>お問い合せについて</h4>
                    <p>
                        個人情報の取扱いに関し、苦情・お問い合せ等ございましたら、下記担当窓口までご連絡ください。<br>
                        会社名：株式会社相和プラント<br>
                        住所：〒457-0863 愛知県名古屋市南区豊2-33-1<br>
                        電話番号：052-691-7536
                    </p>
                </div>
            </div>
        </section>

        <!-- Site Policy Section -->
        <section class="section">
            <div class="container">
                <h3 class="heading">サイトポリシー</h3>
                <div class="policy-content">
                    <p>
                        当サイト上の文書・写真・イラスト等（以下コンテンツと表現）は、当社及びその関係会社（以下総称して当社といいます）ならびに第三者が有する著作権により保護されております。<br>
                        当サイトご利用の皆様は、個人的にまたは家庭内その他これに準ずる限られた範囲内において使用することを目的とする場合にのみコンテンツをダウンロード等により複製することができます。<br>
                        また、当社は、当サイトからリンクしている他のサイトのコンテンツに関して一切の責任を負いません。
                    </p>
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
