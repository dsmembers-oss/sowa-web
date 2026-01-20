# 株式会社相和プラント コーポレートサイト

## ローカル環境でのテスト方法

### 1. PHPのインストール

#### Mac
```bash
brew install php
```

#### Windows
XAMPP または MAMP をインストールしてください。
- XAMPP: https://www.apachefriends.org/
- MAMP: https://www.mamp.info/

### 2. サーバーの起動

ターミナル（コマンドプロンプト）で以下を実行：

```bash
cd sowa-web
php -S localhost:8000
```

### 3. ブラウザでアクセス

```
http://localhost:8000
```

## お問い合わせフォームのテスト

### テストモードについて

現在 `TEST_MODE = true` に設定されています。
この状態では、実際のメール送信は行われず、送信内容が `contact/logs/` フォルダに保存されます。

### テスト手順

1. サーバーを起動
   ```bash
   php -S localhost:8000
   ```

2. ブラウザでフォームを開く
   ```
   http://localhost:8000/contact/index.php
   ```

3. フォームに入力して送信

4. `contact/logs/` フォルダ内のテキストファイルを確認
   - 管理者宛メールとユーザー宛自動返信メールの内容が保存されています

### 本番環境への切り替え

`contact/config.php` を編集：

```php
// テストモードをオフに
define('TEST_MODE', false);

// 管理者メールアドレスを設定
define('ADMIN_EMAIL', 'info@sowa-plant.co.jp');
```

## ファイル構成

```
sowa-web/
├── index.html          # トップページ
├── company.html        # 会社概要
├── service.html        # 事業案内
├── contact/            # お問い合わせフォーム
│   ├── index.php       # 入力画面
│   ├── confirm.php     # 確認画面
│   ├── mail.php        # 送信処理
│   ├── thanks.php      # 完了画面
│   ├── config.php      # 設定ファイル
│   ├── security.php    # セキュリティ関数
│   ├── database.php    # DB操作（オプション）
│   └── logs/           # テストモード時のログ保存先
├── css/
│   └── style.css
├── js/
│   └── script.js
└── images/
```

## 注意事項

- `contact/logs/` フォルダはテスト用です。本番環境では削除またはアクセス制限をかけてください。
- 本番環境では必ず `TEST_MODE = false` に設定してください。
