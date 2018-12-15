<?php
/**
 * [Config] BcMachineLearning
 *
 * @package BcMachineLearning
 */

/**
 * システムナビ
 */
$config['BcApp.adminNavi.bc_machine_learning'] = [
	'name' => '画像コンテンツ分析プラグイン',
	'contents' => [
		[
			'name' => '画像アップロード',
			'url' => [
				'admin' => true,
				'plugin' => 'bc_machine_learning',
				'controller' => 'bc_machine_learnings',
				'action' => 'index',
			],
		],
	],
];

/**
 * Google Cloud Platform APIキー
 */
$config['BcMachineLearning'] = [
	// ご自分で準備されたGoogle Cloud Platform の APIキーへ書き換えてください
	'ApiKey' => 'XXXXXXXXXXXXXXXXXXXXX',
];
