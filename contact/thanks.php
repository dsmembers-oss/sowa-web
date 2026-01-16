<?php
/**
 * お問い合わせフォーム - 送信完了画面
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

// 送信完了フラグチェック（直接アクセス防止）
if (empty($_SESSION['mail_sent'])) {
    header('Location: index.php');
    exit;
}

// フラグをクリア（リロード対策）
unset($_SESSION['mail_sent']);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="株式会社相和プラントへのお問い合わせ完了ページです。">
    <meta name="robots" content="noindex, nofollow">
    <title>送信完了 | 株式会社相和プラント</title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&family=Josefin+Sans:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* 完了画面用スタイル */
        .thanks-content {
            text-align: center;
            padding: 40px 20px;
            max-width: 700px;
            margin: 0 auto;
        }
        .thanks-icon {
            font-size: 60px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .thanks-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
        }
        .thanks-message {
            font-size: 16px;
            line-height: 1.8;
            color: #555;
            margin-bottom: 30px;
        }
        .thanks-note {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .thanks-note p {
            margin: 0;
            font-size: 14px;
            color: #666;
        }
        .btn-home {
            display: inline-block;
            padding: 15px 40px;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            background-color: #1e88d2;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-home:hover {
            background-color: #1565c0;
            opacity: 1;
            transform: translateY(-2px);
        }
        @media (max-width: 768px) {
            .thanks-title {
                font-size: 22px;
            }
            .thanks-message {
                font-size: 14px;
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
        <h1>送信完了</h1>
    </section>

    <!-- Main Content -->
    <main>

        <!-- Thanks Section -->
        <section class="section">
            <div class="container">
                <div class="thanks-content">
                    <div class="thanks-icon">✓</div>
                    <h3 class="thanks-title">お問い合わせありがとうございます</h3>
                    <p class="thanks-message">
                        お問い合わせを受け付けいたしました。<br>
                        ご入力いただいたメールアドレス宛に確認メールをお送りしましたので、<br>
                        ご確認ください。<br><br>
                        内容を確認の上、担当者より折り返しご連絡いたします。<br>
                        今しばらくお待ちくださいますようお願い申し上げます。
                    </p>
                    <div class="thanks-note">
                        <p>
                            ※確認メールが届かない場合は、迷惑メールフォルダをご確認ください。<br>
                            ※数日経っても返信がない場合は、お手数ですがお電話にてお問い合わせください。
                        </p>
                    </div>
                    <a href="../index.html" class="btn-home">トップページへ戻る</a>
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
