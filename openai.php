<?php
header('Content-Type: application/json');
$config = require 'ini.php';
$apiKey = $config['openai_api_key'];
if (!isset($_POST['prompt']) || $_POST['prompt'] === '') {
  echo json_encode(['error' => 'No input']);
  exit;
}

$promptText = $_POST['prompt'];
$mode = $_POST['mode'];
$systemPromptText = '';
if($mode=='strong'){
  $systemPromptText = 'あなたはHTMLマークアップとWebコンテンツ編集の専門家です。ユーザーから与えられた日本語のhtmlテキスト中の読者に訴求力のある部分を <strong>タグで強調してください。';
}else if($mode=='figure'){
  $systemPromptText = "あなたはHTMLマークアップとWebコンテンツ編集の専門家です。以下に与えるHTMLテキストを読み、読者の理解・視覚的メリハリ・UX向上の観点から、
適切な箇所に図版・写真・動画・アイコンの挿入を提案してください。

挿入形式は以下の形式で明示してください：

- [図版: ○○を視覚的に説明する図があると効果的な箇所]
- [写真: ○○の実例や状況を視覚的に伝える写真があると読者の理解が深まる箇所]
- [動画: 操作手順や利用シーンを補足する短尺動画があるとわかりやすい箇所]
- [アイコン: 手順や概念を簡潔に視覚化するアイコンを挿入すると効果的な箇所]

出力時の条件：

- 元のHTML構造を保ちつつ、挿入ポイントの直後にコメント形式で挿入指示を記述してください
- 1記事あたり最大6件程度にとどめてください（多すぎると読みにくくなるため）
- 過剰な繰り返しや装飾は避け、自然な流れに配慮してください
";
}else{
  $systemPromptText = 'あなたはHTMLマークアップとWebコンテンツ編集の専門家です。ユーザーから与えられた日本語のテキストをWeb向けに自然なHTML（pタグ、strong、h2など）で出力してください。';
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
