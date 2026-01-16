<?php
/**
 * データベース操作ライブラリ
 *
 * @package SowaPlantMailForm
 * @version 1.0.0
 */

declare(strict_types=1);

/**
 * データベース接続を取得
 *
 * @return PDO|null
 */
function getDbConnection(): ?PDO
{
    // DB機能が無効の場合
    if (!DB_ENABLED) {
        return null;
    }

    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch (PDOException $e) {
            logError('Database connection failed: ' . $e->getMessage());
            return null;
        }
    }

    return $pdo;
}

/**
 * お問い合わせデータをDBに保存
 *
 * @param array $data フォームデータ
 * @return int|false 挿入されたID、失敗時はfalse
 */
function saveInquiry(array $data): int|false
{
    $pdo = getDbConnection();

    if ($pdo === null) {
        return false;
    }

    try {
        $sql = "INSERT INTO inquiries (
            email,
            name,
            company,
            department,
            job_title,
            postcode,
            address,
            tel,
            fax,
            message,
            ip_address,
            user_agent,
            created_at
        ) VALUES (
            :email,
            :name,
            :company,
            :department,
            :job_title,
            :postcode,
            :address,
            :tel,
            :fax,
            :message,
            :ip_address,
            :user_agent,
            NOW()
        )";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':email' => $data['email'],
            ':name' => $data['name'],
            ':company' => $data['company'],
            ':department' => $data['department'] ?? '',
            ':job_title' => $data['job_title'] ?? '',
            ':postcode' => $data['postcode'] ?? '',
            ':address' => $data['address'] ?? '',
            ':tel' => $data['tel'] ?? '',
            ':fax' => $data['fax'] ?? '',
            ':message' => $data['message'],
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);

        return (int) $pdo->lastInsertId();

    } catch (PDOException $e) {
        logError('Failed to save inquiry: ' . $e->getMessage(), $data);
        return false;
    }
}

/**
 * お問い合わせ一覧を取得
 *
 * @param int $limit 取得件数
 * @param int $offset オフセット
 * @return array
 */
function getInquiries(int $limit = 50, int $offset = 0): array
{
    $pdo = getDbConnection();

    if ($pdo === null) {
        return [];
    }

    try {
        $sql = "SELECT * FROM inquiries ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();

    } catch (PDOException $e) {
        logError('Failed to get inquiries: ' . $e->getMessage());
        return [];
    }
}

/**
 * お問い合わせ詳細を取得
 *
 * @param int $id
 * @return array|null
 */
function getInquiryById(int $id): ?array
{
    $pdo = getDbConnection();

    if ($pdo === null) {
        return null;
    }

    try {
        $sql = "SELECT * FROM inquiries WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        $result = $stmt->fetch();
        return $result ?: null;

    } catch (PDOException $e) {
        logError('Failed to get inquiry: ' . $e->getMessage());
        return null;
    }
}

/**
 * お問い合わせのステータスを更新
 *
 * @param int $id
 * @param string $status
 * @return bool
 */
function updateInquiryStatus(int $id, string $status): bool
{
    $pdo = getDbConnection();

    if ($pdo === null) {
        return false;
    }

    $allowedStatuses = ['new', 'in_progress', 'completed', 'cancelled'];
    if (!in_array($status, $allowedStatuses, true)) {
        return false;
    }

    try {
        $sql = "UPDATE inquiries SET status = :status, updated_at = NOW() WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':status' => $status, ':id' => $id]);

        return $stmt->rowCount() > 0;

    } catch (PDOException $e) {
        logError('Failed to update inquiry status: ' . $e->getMessage());
        return false;
    }
}

/**
 * お問い合わせの総件数を取得
 *
 * @return int
 */
function getInquiryCount(): int
{
    $pdo = getDbConnection();

    if ($pdo === null) {
        return 0;
    }

    try {
        $sql = "SELECT COUNT(*) as count FROM inquiries";
        $stmt = $pdo->query($sql);
        $result = $stmt->fetch();

        return (int) ($result['count'] ?? 0);

    } catch (PDOException $e) {
        logError('Failed to get inquiry count: ' . $e->getMessage());
        return 0;
    }
}

/**
 * 指定期間のお問い合わせを取得
 *
 * @param string $startDate 開始日 (Y-m-d)
 * @param string $endDate 終了日 (Y-m-d)
 * @return array
 */
function getInquiriesByDateRange(string $startDate, string $endDate): array
{
    $pdo = getDbConnection();

    if ($pdo === null) {
        return [];
    }

    try {
        $sql = "SELECT * FROM inquiries
                WHERE DATE(created_at) BETWEEN :start_date AND :end_date
                ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate,
        ]);

        return $stmt->fetchAll();

    } catch (PDOException $e) {
        logError('Failed to get inquiries by date range: ' . $e->getMessage());
        return [];
    }
}
