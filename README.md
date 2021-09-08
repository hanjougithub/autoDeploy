# autodeploy

## ▼ このファイルの使い方

GitHubの Settings > Webhooks に以下のように設定する

```
 * Payload URL:     http://xxxxx.net/.deploy.php
 * Content type:    application/json_decode
 * Secret:          $SECRET_KEY
 * Which events...: Just the push event.
```

##ドキュメント
 - APIテスト
   - https://api.slack.com/methods/chat.postMessage/test
 - 参考サイト
   - https://zenn.dev/kou_pg_0131/articles/slack-api-post-message
   - https://qiita.com/HitomiHoshisaki/items/51df1c1eaac39d864a04