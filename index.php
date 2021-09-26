<?php
/* ページ表示だけなのか、それとも書き込みなのかの判定。 */
if(!empty($_POST["btn-submit"]))
 var_dump($_POST)
?>

<!-- action属性は情報を処理するプログラムのURI（今回は空でこのファイルのURIをデフォルトで指定している。だから上のphpが実行されている。） -->
<form method="post" action="">
	<div>
		<label for="view-name">表示名</label>
		<input type="text" id="view-name" name="view-name-for-input-identify">
	</div>
	<div>
		<label for="message">ひと言メッセージ</label>
		<textarea name="message-for-textarea-identify" id="message"></textarea>
	</div>
	<input type="submit" value="書き込む" name="btn-submit">
</form>
