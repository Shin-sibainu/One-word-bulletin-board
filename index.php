<?php
//メッセージを保存するファイルパスを指定
define("FILENAME", "./message.txt");

//タイムゾーン設定
date_default_timezone_set("Asia/Tokyo");

//変数の初期化
$current_date = null;
$data = null;
$file_handle = null;
$split_data = null;
$message = array();
$message_array = array();

//ページ表示だけなのか、それとも書き込みなのかの判定。
if (!empty($_POST["btn-submit"])) {
//ファイルかURLをオープンする。（書き込みモード）
    if ($file_handle = fopen(FILENAME, "a")) {

        //書き込んだ日時を取得
        $current_date = date("Y-m-d H:i:s");

        //書き込むデータの作成
        $data = "'" . $_POST["view-name-for-input-identify"] . "'" . "," . "'" . $_POST["message-for-textarea-identify"] . "'" . "," . "'" . $current_date . "'" . "\n";

        //データを書き込む。第一引数にはファイルポインターリソースが必要。
        fwrite($file_handle, $data);

        //安全に閉じる。
        fclose($file_handle);
    }
}

//送信ボタンを押さずともこのif文はファイルさえ存在すれば通る。
//データが書かれたファイルを読み込む
if ($file_handle = fopen(FILENAME, "r")) {
    //ファイルに書いてるデータを１行ずつ取得する。できたらtrueを返す。
    while ($data = fgets($file_handle)) {
        echo $data . "<br>";
        $split_data = preg_split('/\'/', $data);

        $message = array(
            "view_name" => $split_data[1], //表示名
            "message" => $split_data[3], //一言メッセージ
            //"post_date" => $split_data[5], //投稿日時
        );
    }
    //ファイルを閉じる
    fclose($file_handle);
}
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
