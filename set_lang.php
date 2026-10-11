<?php
require_once '../includes/db.php';
if (!isLoggedIn()) { http_response_code(401); exit; }
$lang = in_array($_GET['lang']??'',['english','bangla']) ? $_GET['lang'] : 'english';
$_SESSION['preferred_lang'] = $lang;
$pdo->prepare("UPDATE students SET preferred_language=? WHERE student_id=?")->execute([$lang,$_SESSION['student_id']]);
echo 'ok';
