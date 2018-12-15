# BcMachineLearning

画像コンテンツ分析 サンプルプラグイン

## Documentation

* このプラグインでは指定した画像をアップして Google Cloud Vision API を試すことができます。
画像解析の結果は画像アップロード時に指定したブログにブログ記事として保存されます。

* APIキーは事前にGoogle Cloud Platformにて準備が必要です。（要クレジットカード、$300分無料トライアルあり）
BcMachineLearning/Config/setting.php を変更してからご利用ください。


## Installation

1. 圧縮ファイルを解凍後、BASERCMS/app/Plugin/BcMachineLearning に配置します。
2. 管理システムのプラグイン管理に入って、表示されている 画像コンテンツ分析プラグイン を有効化して下さい。
3. プラグインの有効化後、システムナビの「画像コンテンツ分析プラグイン」の設定一覧へ移動し、利用するブログを追加し、有効化を行なってください。
4. 利用が有効なブログ記事の投稿画面にアクセスすると、入力項目が追加されてます。


## TODO
- APIキーの登録を管理画面で登録できるようにする
- Google Cloud Machine Learningを使って解析するようにする


## License
Lincensed under the MIT lincense since version 2.0


## Thanks
- [http://basercms.net/](http://basercms.net/)
- [http://wiki.basercms.net/](http://wiki.basercms.net/)
- [http://cakephp.jp](http://cakephp.jp)
- [Semantic Versioning 2.0.0](http://semver.org/lang/ja/)
- [Google Cloud Platform](https://console.cloud.google.com/)
- [Google Cloud Vision API](https://cloud.google.com/vision/)
- [Google Cloud Machine Learning](https://cloud.google.com/ml-engine/)
- [PHP Code Sample (@t-fujiwara)](https://qiita.com/t-fujiwara/items/7e1f7c52a73887519ac1)