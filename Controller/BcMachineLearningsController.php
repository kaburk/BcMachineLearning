<?php
/**
 * [Controller] BcMachineLearning
 *
 * @package BcMachineLearning
 * @package app.Controller
 * @property BcAuthConfigureComponent $BcAuthConfigure
 * @property BcAuthComponent $BcAuth
 */

class BcMachineLearningsController extends AppController {

	/**
	 * クラス名
	 *
	 * @var string
	 */
	public $name = 'BcMachineLearnings';

	/**
	 * モデル
	 *
	 * @var array
	 */
	public $uses = [
		'Blog.BlogContent',
		'Blog.BlogPost',
		'Content',
		'SiteConfig',
	];

	/**
	 * コンポーネント
	 *
	 * @var array
	 */
	public $components = [
		'BcAuth',
		'Cookie',
		'BcAuthConfigure',
	];

	/**
	 * サブメニューエレメント
	 *
	 * @var    array
	 * @access    public
	 */
	public $subMenuElements = [
		'bc_machine_learning',
	];

	/**
	 * ぱんくずナビ
	 *
	 * @var string
	 */
	public $crumbs = [
		[
			'name' => '画像コンテンツ分析',
			'url' => [
				'plugin' => 'bc_machine_learning',
				'controller' => 'bc_machine_learnings',
				'action' => 'index',
			],
		],
	];

	/**
	 * ページタイトル
	 *
	 * @var string
	 */
	public $pageTitle = '画像コンテンツ分析';

	/**
	 * 各種設定値
	 */
	public $blogContent;

	/**
	 * [Admin] index
	 *
	 */
	public function admin_index() {

		if (!empty($this->request->params['pass'][0])) {
			$blogContentId = $this->request->params['pass'][0];
		} else {
			$blogContentId = 1;
		}

		if ($this->request->data) {
			if (empty($this->request->data('BcMachineLearning.file.tmp_name'))) {
				$this->setMessage('ファイルのアップロードに失敗しました。', true);
			} else {
				$blogContentId = $this->request->data('BcMachineLearning.blog_content_id');
				$file = $this->request->data('BcMachineLearning.file');
				if ($this->analysisResultSave($blogContentId, $file)) {
					$this->setMessage('画像ファイルのアップロードに成功しました。');
					$this->redirect(['action' => 'index', $blogContentId]);
				} else {
					$this->setMessage('画像ファイルのアップロードに失敗しました。', true);
				}
			}
		}

		$contents = $this->Content->find('all', [
			'conditions' => [
				'Content.plugin' => 'Blog',
				'Content.type' => 'BlogContent',
				'OR' => [
					['Content.alias_id' => ''],
					['Content.alias_id' => NULL],
				],
			],
			'order' => [
				'Content.entity_id'
			],
		]);

		// 名サイトとサブサイトで同じ名称のブログが有る可能性があるので、
		// プルダウンにサイト名を付加
		$blogContents = [];
		foreach ($contents as $content) {
			if ($content['Site']['id']) {
				$siteName = $content['Site']['name'];
			} else {
				$bcSite = Configure::read('BcSite');
				$siteName = $bcSite['main_site_display_name'];
			}
			$blogContents[$content['Content']['entity_id']] = sprintf(
				'%s : %s',
				$siteName,
				$content['Content']['title']
			);
		}
		$this->set('blogContentId', $blogContentId);
		$this->set('blogContents', $blogContents);

	}

	/**
	 * [Admin] analysisResultSave
	 *
	 * @param $blogContentId
	 * @param $file
	 * @return bool
	 */
	private function analysisResultSave($blogContentId, $file) {

		clearAllCache();

		if (empty($file) || empty($blogContentId)) {
			return false;
		}

		// APIキー (TODO 設定ファイルから管理画面で設定するように変更したい）
		$apiKey = Configure::read('BcMachineLearning.ApiKey');
		$json = json_encode([
			"requests" => [
				[
					"image" => [
						"content" => base64_encode(file_get_contents($file['tmp_name'])),
					],
					"features" => [
						[
							"type" => "LABEL_DETECTION",
							"maxResults" => 8,
						],
					],
				],
			],
		]);

		// リクエストを実行
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://vision.googleapis.com/v1/images:annotate?key=" . $apiKey);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		if (isset($referer) && !empty($referer)) curl_setopt($curl, CURLOPT_REFERER, $referer);
		curl_setopt($curl, CURLOPT_TIMEOUT, 15);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
		$res1 = curl_exec($curl);
		$res2 = curl_getinfo($curl);
		curl_close($curl);

		// 取得したデータ
		$resultJson = substr($res1, $res2["header_size"]);      // 取得したJSON
		$header = substr($res1, 0, $res2["header_size"]); // レスポンスヘッダー

		// 解析結果を整形
		$resultStdClass = json_decode($resultJson);
		$resultData = json_decode(json_encode($resultStdClass), true);
		$detail = '<dl>';
		if (!empty($resultData['responses'][0]['labelAnnotations'])) {
			foreach ($resultData['responses'][0]['labelAnnotations'] as $result) {
				$detail .= '<dt>' . $result['description'] . '</dt>';
				$detail .= '<dd>' . ((float)$result['score'] * 100) . ' %</dd>';
			}
		}
		$detail = $detail . '</dl>';

		// 記事の作成
		$data = array();

		// 属するブログを設定
		$data['BlogPost']['blog_content_id'] = $blogContentId;

		// 記事のNOを設定
		$data['BlogPost']['no'] = $this->BlogPost->getMax('no', [
				'BlogPost.blog_content_id' => $blogContentId,
			]) + 1;

		// 記事タイトルを設定
		$data['BlogPost']['name'] = $file['name'];

		// 記事本文を設定
		$data['BlogPost']['detail'] = $detail;

		// 記事概要を設定
		$data['BlogPost']['content'] = nl2br($header . PHP_EOL . $resultJson);

		// 記事カテゴリを設定
		$data['BlogPost']['blog_category_id'] = null;

		// 投稿ユーザを設定
		$data['BlogPost']['user_id'] = 1;

		// 公開状態を設定：非公開は0、公開は1
		$data['BlogPost']['status'] = 1;

		// 投稿日を設定
		$data['BlogPost']['posts_date'] = date('Y-m-d H:i:s');

		// 本文下書きを設定
		$data['BlogPost']['content_draft'] = null;

		// 詳細下書きを設定
		$data['BlogPost']['detail_draft'] = null;

		// 表示開始日を設定
		$data['BlogPost']['publish_begin'] = null;

		// 表示終了日を設定
		$data['BlogPost']['publish_end'] = null;

		// 検索除外を設定
		$data['BlogPost']['exclude_search'] = 0;

		// アイキャッチを設定（アップした画像）
		$pathInfo = pathinfo($file['name']);
		$data['BlogPost']['eye_catch'] = $file;
		$data['BlogPost']['eye_catch_delete'] = 0;
		$data['BlogPost']['eye_catch_'] =
			date('Y/m/') .
			sprintf('%08d_eye_catch.%s', $data['BlogPost']['no'], $pathInfo['extension']);

		// 登録日を設定
		$data['BlogPost']['created'] = $data['BlogPost']['posts_date'];

		// 更新日を設定
		$data['BlogPost']['modified'] = $data['BlogPost']['posts_date'];

		// 保存
		$this->BlogPost->setupUpload($blogContentId);
		$ret = $this->BlogPost->saveAll($data);
		if (!$ret) {
			$this->log('解析結果データの保存に失敗しました。');
			$this->log($resultJson);
			$this->log($ret);
		}

		return $ret;
	}
}