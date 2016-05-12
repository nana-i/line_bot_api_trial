<?php
error_log("callback start.");

// アカウント情報設定
$channel_id = "アカウントのチャネルID";
$channel_secret = "アカウントのシークレット";
$mid = "アカウントのMID";

// メッセージ受信
$json_string = file_get_contents('php://input');
$json_object = json_decode($json_string);
$content = $json_object->result{0}->content;
$text = $content->text;
$from = $content->from;
$message_id = $content->id;
$content_type = $content->contentType;

// ユーザ情報取得
//api_get_user_profile_request($from);

if ($content_type <> 1) {
    // メッセージがテキスト以外の場合は適当にあしらう
    $send_text = "へーすごいね！";
} elseif (preg_match("/教えて/", $text)) { 
    // 教えて検索
    $curl = curl_init("http://cdn.oshiete.goo.ne.jp/search_goo/result/?MT=".$text."&type=json&code=utf8");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $qa_json = curl_exec($curl);
    $qa = json_decode($qa_json);
    if (!empty($qa->items{0})) {
        $qa_title = $qa->items{0}->title;
        $qa_body = $qa->items{0}->body;
        $qa_qid = $qa->items{0}->qid;
        $send_text = "これはどう？\n\n" . $qa_title ."\n". $qa_body. "\n http://oshiete.goo.ne.jp/qa/".$qa_qid.".html";
    } else {
        $send_text = "たまにはggrks";
    }
} else { 
    // とりあえずおうむ返し
    $send_text = $text."ですね";
}

$post_object = array(
    "to" => array ($from),
    "toChannel" => "1383378250",
    "eventType" => "138311608800106203",
    "content" => array (
        "toType" => 1,
        "contentType" => 1,
        "text" => $send_text
        )
    );
$post = json_encode($post_object);

api_post_request("/v1/events", $post);

error_log("callback end.");


// メッセージ送信
function api_post_request($path, $post) {
    $url = "https://trialbot-api.line.me{$path}";
    $headers = array(
        "Content-Type: application/json",
        "X-Line-ChannelID: {$GLOBALS['channel_id']}",
        "X-Line-ChannelSecret: {$GLOBALS['channel_secret']}",
        "X-Line-Trusted-User-With-ACL: {$GLOBALS['mid']}"
    );

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($curl);
    error_log($output);
}

// ユーザ情報取得
function api_get_user_profile_request($mid) {
    $url = "https://trialbot-api.line.me/v1/profiles?mids={$mid}";
    $headers = array(
        "X-Line-ChannelID: {$GLOBALS['channel_id']}",
        "X-Line-ChannelSecret: {$GLOBALS['channel_secret']}",
        "X-Line-Trusted-User-With-ACL: {$GLOBALS['mid']}"
    ); 

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($curl);
    error_log($output);
}
