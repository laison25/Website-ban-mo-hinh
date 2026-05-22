<?php
require_once __DIR__ . '/includes/init.php';

$openAiConfigPath = __DIR__ . '/includes/openai-config.php';
if (is_file($openAiConfigPath)) {
    require_once $openAiConfigPath;
}

$geminiConfigPath = __DIR__ . '/includes/gemini-config.php';
if (is_file($geminiConfigPath)) {
    require_once $geminiConfigPath;
}

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phuong thuc khong hop le.']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '{}', true);
if (!is_array($payload)) {
    $payload = [];
}
$message = trim((string) ($payload['message'] ?? $_POST['message'] ?? ''));

if ($message === '') {
    echo json_encode(['success' => false, 'message' => 'Vui long nhap noi dung can tu van.']);
    exit;
}

$products = fetch_chat_products($conn);
$fallbackReply = build_shop_ai_reply($message, $products);
$geminiKey = get_gemini_api_key();
$apiKey = get_openai_api_key();
$providerErrors = [];

if ($geminiKey !== '') {
    $geminiError = null;
    $geminiReply = call_gemini_response($geminiKey, $message, $products, $geminiError);
    if ($geminiReply !== null) {
        echo json_encode(['success' => true, 'reply' => $geminiReply, 'mode' => 'gemini']);
        exit;
    }
    $providerErrors['gemini'] = $geminiError;
}

if ($apiKey !== '') {
    $openaiError = null;
    $aiReply = call_openai_response($apiKey, $message, $products, $openaiError);
    if ($aiReply !== null) {
        echo json_encode(['success' => true, 'reply' => $aiReply, 'mode' => 'openai']);
        exit;
    }
    $providerErrors['openai'] = $openaiError;
}

$response = ['success' => true, 'reply' => $fallbackReply, 'mode' => 'local'];
if (isset($_GET['debug'])) {
    $response['provider_errors'] = $providerErrors;
}
echo json_encode($response);
exit;

function fetch_chat_products(mysqli $conn): array
{
    $result = $conn->query('
        SELECT id, name, category, studio, price, stock, rating, image_path
        FROM products
        ORDER BY is_featured DESC, rating DESC, id DESC
        LIMIT 30
    ');

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function build_shop_ai_reply(string $message, array $products): string
{
    $normalized = mb_strtolower($message, 'UTF-8');
    $budget = detect_budget($normalized);
    $keywords = extract_keywords($normalized);
    $matched = [];

    foreach ($products as $product) {
        $score = 0;
        if ($budget > 0 && (float) $product['price'] > $budget) {
            continue;
        }

        $haystack = mb_strtolower(
            ($product['name'] ?? '') . ' ' . ($product['category'] ?? '') . ' ' . ($product['studio'] ?? ''),
            'UTF-8'
        );

        foreach ($keywords as $keyword) {
            if ($keyword !== '' && mb_strpos($haystack, $keyword, 0, 'UTF-8') !== false) {
                $score += 3;
            }
        }

        if ($budget > 0 && (float) $product['price'] <= $budget) {
            $score += 4;
        }

        if ((int) ($product['stock'] ?? 0) > 0) {
            $score += 2;
        }

        if ($score > 0) {
            $product['chat_score'] = $score;
            $matched[] = $product;
        }
    }

    usort($matched, function ($a, $b) {
        return ($b['chat_score'] <=> $a['chat_score']) ?: ((float) $a['price'] <=> (float) $b['price']);
    });

    if (empty($matched)) {
        $matched = array_values(array_filter($products, fn($product) => (int) ($product['stock'] ?? 0) > 0));
    }

    $suggestions = array_slice($matched, 0, 3);

    if (str_contains($normalized, 'ship') || str_contains($normalized, 'giao')) {
        return 'Shop co ho tro giao hang toan quoc, dong goi chong soc cho figure/resin. Don tu 5.000.000d duoc mien phi giao hang. Ban gui khu vuc nhan hang de shop tu van phi ship chinh xac hon.';
    }

    if (str_contains($normalized, 'thanh toán') || str_contains($normalized, 'thanh toan') || str_contains($normalized, 'qr')) {
        return 'Shop ho tro COD, chuyen khoan ngan hang va VietQR. Khi dat hang tren web, he thong se hien tong tien va ma QR theo don hang de thanh toan nhanh.';
    }

    if (empty($suggestions)) {
        return 'Hien shop chua tim thay san pham phu hop voi cau hoi nay. Ban co the noi ro hon ve nhan vat, dong san pham hoac ngan sach de minh goi y chinh xac hon.';
    }

    $lines = ['Minh goi y cho ban mot vai mau phu hop:'];
    foreach ($suggestions as $product) {
        $stockText = ((int) $product['stock'] > 0) ? 'con ' . (int) $product['stock'] . ' san pham' : 'tam het hang';
        $lines[] = '- ' . $product['name'] . ' (' . $product['category'] . ', ' . format_currency((float) $product['price']) . ', ' . $stockText . ')';
    }
    $lines[] = 'Ban co the cho minh biet them ngan sach va nhan vat yeu thich de minh loc tiep.';

    return implode("\n", $lines);
}

function detect_budget(string $normalized): float
{
    if (preg_match('/(\d+(?:[,.]\d+)?)\s*(trieu|triệu|m|tr)/u', $normalized, $matches)) {
        return (float) str_replace(',', '.', $matches[1]) * 1000000;
    }

    $plainNumber = preg_replace('/[.\s]/', '', $normalized);
    if (preg_match('/(\d{6,})/u', $plainNumber, $matches)) {
        return (float) $matches[1];
    }

    if (str_contains($normalized, 're') || str_contains($normalized, 'rẻ')) {
        return 3000000;
    }

    return 0;
}

function extract_keywords(string $normalized): array
{
    $keywords = ['pokemon', 'resin', 'figure', 'nendoroid', 'scale', 'mini', 'pikachu', 'charizard', 'greninja', 'mewtwo', 'lucario', 'gengar', 'eevee', 'dragonite', 'rayquaza', 'snorlax'];
    return array_values(array_filter($keywords, fn($keyword) => mb_strpos($normalized, $keyword, 0, 'UTF-8') !== false));
}

function get_openai_api_key(): string
{
    $envKey = trim((string) (getenv('OPENAI_API_KEY') ?: ''));
    if ($envKey !== '') {
        return $envKey;
    }

    return defined('OPENAI_API_KEY') ? trim((string) OPENAI_API_KEY) : '';
}

function get_openai_model(): string
{
    $envModel = trim((string) (getenv('OPENAI_MODEL') ?: ''));
    if ($envModel !== '') {
        return $envModel;
    }

    return defined('OPENAI_MODEL') && trim((string) OPENAI_MODEL) !== ''
        ? trim((string) OPENAI_MODEL)
        : 'gpt-4o-mini';
}

function get_gemini_api_key(): string
{
    $envKey = trim((string) (getenv('GEMINI_API_KEY') ?: ''));
    if ($envKey !== '') {
        return $envKey;
    }

    return defined('GEMINI_API_KEY') ? trim((string) GEMINI_API_KEY) : '';
}

function get_gemini_model(): string
{
    $envModel = trim((string) (getenv('GEMINI_MODEL') ?: ''));
    if ($envModel !== '') {
        return $envModel;
    }

    return defined('GEMINI_MODEL') && trim((string) GEMINI_MODEL) !== ''
        ? trim((string) GEMINI_MODEL)
        : 'gemini-2.5-flash';
}

function call_gemini_response(string $apiKey, string $message, array $products, ?string &$error = null): ?string
{
    if (!function_exists('curl_init')) {
        $error = 'curl_missing';
        return null;
    }

    $model = get_gemini_model();
    $productContext = array_map(function ($product) {
        return [
            'id' => (int) $product['id'],
            'name' => $product['name'],
            'category' => $product['category'],
            'studio' => $product['studio'],
            'price' => (float) $product['price'],
            'stock' => (int) $product['stock'],
        ];
    }, array_slice($products, 0, 20));

    $prompt = "Ban la AI tu van cua shop Lzon Poke. Tra loi bang tieng Viet tu nhien, ngan gon, than thien. "
        . "Uu tien goi y san pham co trong du lieu, kem gia va tinh trang ton kho khi phu hop. "
        . "Duoc tra loi cau hoi chung ve figure, qua tang, bao quan, thanh toan va giao hang. "
        . "Khong bia san pham ngoai danh sach.\n\n"
        . "Du lieu san pham JSON:\n" . json_encode($productContext, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)
        . "\n\nCau hoi khach hang: " . $message;

    $body = [
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $prompt],
                ],
            ],
        ],
        'generationConfig' => [
            'temperature' => 0.5,
            'maxOutputTokens' => 500,
        ],
    ];

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $apiKey,
        ],
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 18,
    ]);

    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        $error = 'curl_error';
        return null;
    }

    $data = json_decode($response, true);
    if ($status < 200 || $status >= 300) {
        $error = 'http_' . $status . ':' . ($data['error']['status'] ?? '') . ':' . substr((string) ($data['error']['message'] ?? ''), 0, 160);
        return null;
    }

    $parts = $data['candidates'][0]['content']['parts'] ?? [];
    $chunks = [];
    foreach ($parts as $part) {
        if (isset($part['text'])) {
            $chunks[] = $part['text'];
        }
    }

    $reply = trim(implode("\n", $chunks));
    if ($reply === '') {
        $error = 'empty_reply';
    }
    return $reply !== '' ? $reply : null;
}

function call_openai_response(string $apiKey, string $message, array $products, ?string &$error = null): ?string
{
    if (!function_exists('curl_init')) {
        $error = 'curl_missing';
        return null;
    }

    $model = get_openai_model();
    $productContext = array_map(function ($product) {
        return [
            'id' => (int) $product['id'],
            'name' => $product['name'],
            'category' => $product['category'],
            'studio' => $product['studio'],
            'price' => (float) $product['price'],
            'stock' => (int) $product['stock'],
        ];
    }, array_slice($products, 0, 20));

    $instructions = 'Ban la AI tu van cua shop Lzon Poke. Tra loi bang tieng Viet tu nhien, ngan gon, than thien. Uu tien goi y san pham co trong du lieu, kem gia va tinh trang ton kho khi phu hop. Duoc tra loi cau hoi chung ve figure, qua tang, bao quan, thanh toan va giao hang. Khong bia san pham ngoai danh sach.';
    $body = [
        'model' => $model,
        'instructions' => $instructions,
        'input' => "Du lieu san pham JSON:\n" . json_encode($productContext, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) . "\n\nCau hoi khach hang: " . $message,
        'temperature' => 0.4,
        'max_output_tokens' => 500,
    ];

    $ch = curl_init('https://api.openai.com/v1/responses');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 18,
    ]);

    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        $error = 'curl_error';
        return null;
    }

    $data = json_decode($response, true);
    if ($status < 200 || $status >= 300) {
        $error = 'http_' . $status . ':' . ($data['error']['type'] ?? '') . ':' . substr((string) ($data['error']['message'] ?? ''), 0, 160);
        return null;
    }

    $reply = trim((string) ($data['output_text'] ?? ''));
    if ($reply === '' && !empty($data['output']) && is_array($data['output'])) {
        $chunks = [];
        foreach ($data['output'] as $item) {
            foreach (($item['content'] ?? []) as $content) {
                if (($content['type'] ?? '') === 'output_text' && isset($content['text'])) {
                    $chunks[] = $content['text'];
                }
            }
        }
        $reply = trim(implode("\n", $chunks));
    }

    if ($reply === '') {
        $error = 'empty_reply';
    }
    return $reply !== '' ? $reply : null;
}
