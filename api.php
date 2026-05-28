<?php
// CORS Headers - Taaki Vercel ya kisi bhi frontend se call ho sake
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$year = $_GET['year'] ?? '';
$regNo = $_GET['redg_no'] ?? '';
$semester = $_GET['semester'] ?? '';
$examHeld = $_GET['exam_held'] ?? '';

if (!$year || !$regNo || !$semester || !$examHeld) {
    echo json_encode(['status' => 400, 'message' => 'Missing parameters']);
    exit;
}

// Cookie Jar Setup
$cookie_jar = tempnam(sys_get_temp_dir(), 'beu_cookie_');

// STEP 1: FETCH TOKEN & COOKIE
$ch1 = curl_init("https://beu-bih.ac.in/backend/v1/result/token?t=" . time());
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch1, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
curl_setopt($ch1, CURLOPT_COOKIEJAR, $cookie_jar);
$res1 = curl_exec($ch1);
curl_close($ch1);

$token = "";
if ($res1) {
    $j = json_decode($res1, true);
    $token = isset($j['token']) ? $j['token'] : trim($res1);
}

// STEP 2: FETCH RESULT WITH TOKEN + COOKIE
$params = [
    'year' => $year,
    'redg_no' => $regNo,
    'semester' => $semester,
    'exam_held' => $examHeld,
    'token' => $token
];
$url = "https://beu-bih.ac.in/backend/v1/result/get-result?" . http_build_query($params);

$ch2 = curl_init($url);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch2, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
curl_setopt($ch2, CURLOPT_COOKIEFILE, $cookie_jar); // Send cookie back
$result = curl_exec($ch2);
curl_close($ch2);

// Cleanup
@unlink($cookie_jar);

// Output Result directly to frontend
echo $result;
?>
