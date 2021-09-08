<?php

/*
 * ▼ このファイルの使い方
 * GitHubの Settings > Webhooks に以下のように設定する
 * Payload URL:     http://xxxxx.net/.deploy.php
 * Content type:    application/json_decode
 * Secret:          $SECRET_KEY
 * Which events...: Just the push event.
 *
 * #ドキュメント
 * - APIテスト
 *  - https://api.slack.com/methods/chat.postMessage/test
 * - 参考サイト
 *  - https://zenn.dev/kou_pg_0131/articles/slack-api-post-message
 *  - https://qiita.com/HitomiHoshisaki/items/51df1c1eaac39d864a04
 */

# ************************************************
# 設定(基本的にここだけ環境に合わせて変更する)
# ************************************************

# GitHubに設定するパスワード的な物(お好きな文字列)
$SECRET_KEY = "";

# git pullしたいブランチ
$branch = "develop";

# slackチャンネル
$channel = "";

# slackAPItoken
$slackAPItoken = "";

# **************** 設定ここまで ******************

# 全てのHTTPリクエストヘッダを取得
$header = getallheaders();

# POSTの生データを取得
$post_data = file_get_contents( 'php://input' );

# ハッシュ値を生成
$hmac = hash_hmac('sha1', $post_data, $SECRET_KEY);

# 'X-Hub-Signature'はGitHubのWebhooksで設定したSecret項目
# リクエストヘッダで受け取ったSecretと$SECRET_KEYが同一であれば認証成功
if ( isset($header['X-Hub-Signature']) && $header['X-Hub-Signature'] === 'sha1='.$hmac ) {

    # 受け取ったJSONデータ
    $payload = json_decode($post_data, true);

    # ブランチ判断 上で設定したブランチ以外は処理しない
    if($payload['ref'] != 'refs/heads/'.$branch){
        return;
    }
    # pull実行
    exec('git pull origin '.$branch.' 2>&1', $output, $return);
    $resultsText = [];
    $resultsText[] = $payload['head_commit']['message'];
    $resultsText[] = $output;
    $results = print_r($resultsText, true);
    slackbotSend($results,$channel,$slackAPItoken);
} else {
    # 認証失敗
    $results = "認証失敗しました";
    $result = slackbotSend($results,$channel,$slackAPItoken);
}

function slackbotSend ($results,$channel,$slackAPItoken){
    $channel = urlencode($channel);
    //投稿するメッセージ
    $text = urlencode($results);
    $opts = array(
        'http'=>array(
          'method'=>"POST",
          'header'=>"Authorization: Bearer ${slackAPItoken}\r\n" .
                    "Accept: application/json"
        )
    );
    $context = stream_context_create($opts);
    $url = "https://slack.com/api/chat.postMessage?channel=${channel}&text=${text}&&pretty=1";
    $response = file_get_contents($url, false, $context);
    return $response;
}
?>