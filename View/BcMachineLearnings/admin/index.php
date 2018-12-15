<?php
/**
 * [ADMIN] 入力
 *
 * @package BcMachineLearning
 */
?>
<h4>画像コンテンツ分析</h4>

<?php echo $this->BcForm->create('BcMachineLearning', array('type' => 'file')) ?>

	<table cellpadding="0" cellspacing="0" class="list-table">
		<tbody>
			<tr>
				<th class="col-head" width="25%"><?php echo $this->BcForm->label('BcMachineLearning.blog_content_id', 'ブログの指定') ?></th>
				<td class="col-input">
					<?php echo $this->BcForm->input('BcMachineLearning.blog_content_id', array('type' => 'select', 'options' => $blogContents, 'selected' => $blogContentId)) ?>
					<?php echo $this->BcForm->error('BcMachineLearning.blog_content_id') ?>
					<br /><small>結果の保存先のブログを指定できます</small>
				</td>
			</tr>
			<tr>
				<th class="col-head" width="25%"><?php echo $this->BcForm->label('BcMachineLearning.file', 'アップロード') ?></th>
				<td class="col-input">
					<?php echo $this->BcForm->file('BcMachineLearning.file', array('type' => 'file')) ?>
					<?php echo $this->BcForm->error('BcMachineLearning.file') ?>
					<br /><small>解析したい画像ファイルを選択してください。</small>
				</td>
			</tr>
		</tbody>
	</table>

	<?php echo $this->BcForm->submit('アップロード', array('class' => 'button')) ?>

<?php echo $this->BcForm->end() ?>
