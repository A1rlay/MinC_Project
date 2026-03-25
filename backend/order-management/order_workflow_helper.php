<?php
/**
 * Shared helpers for MinC order workflow, payment proof handling, and schema checks.
 */

require_once __DIR__ . '/../../config/payment_config.php';

function mincNormalizeWhitespace($value) {
    return preg_replace('/\s+/', ' ', trim((string)$value));
}

function mincNormalizePhilippineMobile($value) {
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }

    $compact = preg_replace('/[\s\-\(\)]/', '', $value);
    if (strpos($compact, '+') === 0) {
        $compact = substr($compact, 1);
    }

    if (preg_match('/^09\d{9}$/', $compact)) {
        return $compact;
    }

    if (preg_match('/^63\d{10}$/', $compact)) {
        return '0' . substr($compact, 2);
    }

    return null;
}

function mincPaymentMethodRequiresProof($paymentMethod) {
    return in_array(strtolower(trim((string)$paymentMethod)), ['bank_transfer', 'gcash', 'paymaya'], true);
}

function mincGetPaymentConfigValue() {
    return getMincPaymentConfig();
}

function mincGetTableColumns(PDO $pdo, $tableName) {
    static $cache = [];

    $tableName = trim((string)$tableName);
    if ($tableName === '') {
        return [];
    }

    if (isset($cache[$tableName])) {
        return $cache[$tableName];
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM `{$tableName}`");
    $cache[$tableName] = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');

    return $cache[$tableName];
}

function mincTableHasColumn(PDO $pdo, $tableName, $columnName) {
    return in_array((string)$columnName, mincGetTableColumns($pdo, $tableName), true);
}

function mincOptionalColumnSelect(PDO $pdo, $tableName, $alias, $columnName, $fallbackExpression = 'NULL') {
    if (mincTableHasColumn($pdo, $tableName, $columnName)) {
        return "{$alias}.{$columnName}";
    }

    return "{$fallbackExpression} AS {$columnName}";
}

function mincProjectRootPath() {
    return dirname(__DIR__, 2);
}

function mincProjectBaseUrl() {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $basePath = dirname($scriptName);

    if (substr($basePath, -8) === '/backend') {
        $basePath = dirname($basePath);
    } elseif (substr($basePath, -5) === '/html') {
        $basePath = dirname($basePath);
    } elseif (substr($basePath, -13) === '/app/frontend') {
        $basePath = dirname(dirname($basePath));
    }

    $basePath = str_replace('\\', '/', $basePath);
    if ($basePath === '.' || $basePath === '\\') {
        $basePath = '';
    }

    return rtrim($basePath, '/');
}

function mincPublicAssetUrl($relativePath) {
    $relativePath = ltrim(str_replace('\\', '/', (string)$relativePath), '/');
    if ($relativePath === '') {
        return '';
    }

    $basePath = mincProjectBaseUrl();
    return ($basePath !== '' ? $basePath . '/' : '/') . $relativePath;
}

function mincEnsureDirectory($directoryPath) {
    if (!is_dir($directoryPath)) {
        if (!mkdir($directoryPath, 0775, true) && !is_dir($directoryPath)) {
            throw new RuntimeException('Unable to create upload directory.');
        }
    }
}

function mincStoreUploadedDocument(array $file, $targetFolder, $filePrefix, array $allowedMimeTypes, $maxBytes = 5242880) {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Invalid upload payload.');
    }

    if ((int)$file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed. Please try again.');
    }

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Uploaded file is missing.');
    }

    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > $maxBytes) {
        throw new RuntimeException('Uploaded file exceeds the allowed size limit.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = (string)$finfo->file($file['tmp_name']);

    if (!isset($allowedMimeTypes[$mimeType])) {
        throw new RuntimeException('Unsupported file type. Allowed formats: JPG, PNG, WEBP, or PDF.');
    }

    $extension = $allowedMimeTypes[$mimeType];
    $safePrefix = preg_replace('/[^a-z0-9_\-]/i', '_', (string)$filePrefix);
    $fileName = sprintf('%s_%s.%s', $safePrefix, bin2hex(random_bytes(8)), $extension);

    $relativeDirectory = trim(str_replace('\\', '/', (string)$targetFolder), '/');
    $absoluteDirectory = mincProjectRootPath() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeDirectory);
    mincEnsureDirectory($absoluteDirectory);

    $absolutePath = $absoluteDirectory . DIRECTORY_SEPARATOR . $fileName;
    if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
        throw new RuntimeException('Unable to store uploaded file.');
    }

    $relativePath = $relativeDirectory . '/' . $fileName;

    return [
        'relative_path' => $relativePath,
        'public_url' => mincPublicAssetUrl($relativePath),
        'mime_type' => $mimeType,
        'size' => $size
    ];
}

function mincDescribeOrderStatus($orderStatus, $deliveryMethod = 'shipping') {
    $orderStatus = strtolower(trim((string)$orderStatus));
    $deliveryMethod = strtolower(trim((string)$deliveryMethod));

    switch ($orderStatus) {
        case 'pending':
            return 'Awaiting Review';
        case 'confirmed':
            return 'Received';
        case 'processing':
            return 'Preparing Items';
        case 'shipped':
            return $deliveryMethod === 'pickup' ? 'Ready for Pickup' : 'Out for Delivery';
        case 'delivered':
            return 'Completed';
        case 'cancelled':
            return 'Cancelled';
        default:
            return ucwords(str_replace('_', ' ', $orderStatus));
    }
}

function mincDescribePaymentStatus($paymentStatus, $paymentMethod = 'cod', $paymentProofPath = '') {
    $paymentStatus = strtolower(trim((string)$paymentStatus));
    $paymentMethod = strtolower(trim((string)$paymentMethod));
    $hasProof = trim((string)$paymentProofPath) !== '';

    if ($paymentMethod !== 'cod' && $paymentStatus === 'pending') {
        return $hasProof ? 'Proof Under Review' : 'Awaiting Proof';
    }

    switch ($paymentStatus) {
        case 'paid':
            return 'Paid';
        case 'failed':
            return 'Payment Rejected';
        case 'refunded':
            return 'Refunded';
        case 'pending':
        default:
            return $paymentMethod === 'cod' ? 'Pending Collection' : 'Pending';
    }
}
