<?php
header('Content-Type: application/json');
$config = require 'ini.php';
$apiKey = $config['openai_api_key'];
if (!isset($_POST['prompt'])) {
  echo json_encode(['error' => 'No input']);
  exit;
}

$promptText = $_POST['prompt'];
$mode = $_POST['mode'];
$systemPromptText = '';
if($mode=='strong'){
  $systemPromptText = 'あなたはHTMLマークアップの専門家です。ユーザーから与えられた日本語のhtmlテキスト中の読者に訴求力のある部分を <strong>タグで強調してください。';
}
else{
  $systemPromptText = 'あなたはHTMLマークアップの専門家です。ユーザーから与えられた日本語のテキストをWeb向けに自然なHTML（pタグ、strong、h2など）で出力してください。';
}
$data = [
  'model' => 'gpt-4-1106-preview',
  'messages' => [
    ['role' => 'system', 'content' => $systemPromptText],
    ['role' => 'user', 'content' => $promptText]
  ],
  'temperature' => 0.7
];

$options = [
  'http' => [
    'method' => 'POST',
    'header'  => "Content-Type: application/json\r\n" .
                 "Authorization: Bearer $apiKey\r\n",
    'content' => json_encode($data)
  ]
];

$context  = stream_context_create($options);
$response = file_get_contents('https://api.openai.com/v1/chat/completions', false, $context);

if ($response === FALSE) {
  echo json_encode(['error' => 'API error']);
  exit;
}

$resultData = json_decode($response, true);
$output = $resultData['choices'][0]['message']['content'];
echo json_encode(['result' => $output]);
