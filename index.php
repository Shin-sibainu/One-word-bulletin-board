<?php
//データベース接続情報
define("DB_NAME", "board");
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");

//タイムゾーン設定
date_default_timezone_set("Asia/Tokyo");

//変数の初期化
$current_date = null;
$message = array();
$message_array = array();
$success_message = null;
$error_message = array();
$pdo = null;
$stmt = null;
$res = null;
$option = null;

//セッションのスタート
session_start();

//PDO(PHP Data Objects)
//データベースに接続（読み取りでも書き取りでも）
try {
    //セキュリティ対策用
    $option = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //接続時以外でもエラーを吐くようにする
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => false, //マルチクエリを不可にする
    );
    $pdo = new PDO("mysql:charset=UTF8;dbname=" . DB_NAME . ";host=" . DB_HOST, DB_USER, DB_PASS, $option);
} catch (PDOException $e) {
    //接続エラーのときエラー内容を表示する。
    $error_message[] = $e->getMessage();
}

//もし、書き込まれたら
if (!empty($_POST["btn-submit"])) {
    //空白除去
    $view_name = preg_replace('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['view-name-for-input-identify']);
    $message = preg_replace('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['message-for-textarea-identify']);

    //ここから👇はinputに文字が打ち込まれたかどうか、と、文字データのサニタイズ処理。
    //表示名のチェック
    if (empty($view_name)) {
        $error_message[] = "表示名を入力してください。";
    } else {
        $_SESSION["view-name"] = $view_name;
    }

    if (empty($message)) {
        $error_message[] = "メッセージを入力してください。";
    }

    //入力された文字情報にエラーがなければ、、
    if (empty($error_message)) {

        //データベースに登録する。
        $current_date = date("Y-m-d H:i:s");

        //トランザクション開始(処理を１つにまとめる。まとめると途中でエラーが出ても前まで戻せる。)
        $pdo->beginTransaction();

        try {
            //SQL作成(SQL文：SQLにデータを登録してね！っていう命令文)
            $stmt = $pdo->prepare("INSERT INTO message (view_name, message, post_date) VALUES ( :view_name, :message, :current_date)"); //PDOStatementオブジェクト

            //値をセット（そのSQL文の中に変数をバインド、結びつける。バインド変数。）
            $stmt->bindParam(":view_name", $view_name, PDO::PARAM_STR);
            $stmt->bindParam(":message", $message, PDO::PARAM_STR);
            $stmt->bindParam(":current_date", $current_date, PDO::PARAM_STR);

            //SQLクエリの実行
            $stmt->execute();

            //コミット(ここで初めて登録される)
            $res = $pdo->commit();
        } catch (Exception $e) {
            //エラーならロールバックする
            $pdo->rollBack();
        }

        if ($res) {
            $success_message = "メッセージを書き込みました。";
        } else {
            $error_message[] = "書き込みに失敗しました。";
        }

        //プリペアードステートメントを削除
        $stmt = null;
    }
}

//データベースからデータを取得する
if (empty($error_message)) {
    $spl = "SELECT view_name, message, post_date FROM message ORDER BY post_date DESC";
    $message_array = $pdo->query($spl);
}

//データベース接続を閉じる
$pdo = null;
?>



<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ひと言掲示板</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>ひと言掲示板</h1>

    <!-- メッセージ成功用 -->
    <?php if (!empty($success_message)): ?>
        <p class="success_message"><?php echo $success_message ?></p>
    <?php endif;?>

    <!-- バリデーション表示 -->
    <?php if (!empty($error_message)): ?>
        <?php foreach ($error_message as $value): ?>
            <ul class="error_message">
                <li><?php echo $value ?></li>
            </ul>
        <?php endforeach;?>
    <?php endif;?>

    <!-- form送信用 -->
    <form method="post" action="">
        <div>
            <label for="view-name">表示名</label>
            <input type="text" id="view-name" name="view-name-for-input-identify" value=<?php
                if(!empty($_SESSION["view-name"])) {
                    echo htmlspecialchars($_SESSION["view-name"], ENT_QUOTES, "UTF-8");
                }
            ?>>
        </div>
        <div>
            <label for="message">ひと言メッセージ</label>
            <textarea name="message-for-textarea-identify" id="message"></textarea>
        </div>
        <input type="submit" value="書き込む" name="btn-submit">
    </form>



    <hr>


    <!-- ひと言表示用 -->
    <section>
        <?php if (!empty($message_array)): ?>
            <!-- HTMLに表示するデータを配列から１つずつ取り出し、表示する。 -->
            <?php foreach ($message_array as $value): ?>
                <article>
                    <div class="info">
                        <h2><?php echo htmlspecialchars($value["view_name"]) ?></h2>
                        <time><?php echo date("Y年m月d日 H:i", strtotime($value["post_date"])) ?></time>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars($value['message'])) ?></p>
                </article>
            <?php endforeach;?>
            <?php endif;?>
    </section>
</body>
</html>
