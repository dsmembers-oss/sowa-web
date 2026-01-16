-- ============================================================
-- お問い合わせフォーム データベーステーブル作成SQL
-- 株式会社相和プラント
-- ============================================================

-- データベース作成（必要に応じて）
-- CREATE DATABASE IF NOT EXISTS sowa_plant DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE sowa_plant;

-- ------------------------------------------------------------
-- お問い合わせテーブル
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS inquiries (
    -- 主キー
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

    -- お問い合わせ内容
    email VARCHAR(255) NOT NULL COMMENT 'メールアドレス',
    name VARCHAR(100) NOT NULL COMMENT 'お名前',
    company VARCHAR(200) NOT NULL COMMENT '法人名',
    department VARCHAR(100) DEFAULT '' COMMENT '部署名',
    job_title VARCHAR(100) DEFAULT '' COMMENT '役職名',
    postcode VARCHAR(10) DEFAULT '' COMMENT '郵便番号',
    address TEXT COMMENT '住所',
    tel VARCHAR(20) DEFAULT '' COMMENT '電話番号',
    fax VARCHAR(20) DEFAULT '' COMMENT 'FAX番号',
    message TEXT NOT NULL COMMENT 'お問い合わせ内容',

    -- ステータス管理
    status ENUM('new', 'in_progress', 'completed', 'cancelled') DEFAULT 'new' COMMENT 'ステータス',

    -- メタ情報
    ip_address VARCHAR(45) DEFAULT '' COMMENT 'IPアドレス',
    user_agent TEXT COMMENT 'ユーザーエージェント',

    -- タイムスタンプ
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '作成日時',
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新日時',

    -- インデックス
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='お問い合わせ';


-- ------------------------------------------------------------
-- サンプルデータ（テスト用・本番環境では実行しないでください）
-- ------------------------------------------------------------
-- INSERT INTO inquiries (email, name, company, department, tel, message) VALUES
-- ('test@example.com', 'テスト太郎', '株式会社テスト', '営業部', '03-1234-5678', 'テストお問い合わせです。');
