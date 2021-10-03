<?php
//管理者用パスワード設定
define("PASSWORD", "adminPassword");

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

//もし、ログインボタンが押されたら
if (!empty($_POST["btn-submit"])) {
    if ($_POST["admin-password"] === PASSWORD) {
        $_SESSION["admin-login"] = true;
    } else {
        $error_message[] = "ログインに失敗しました。";
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
    <h1>管理者用ページ</h1>

    <!-- バリデーション表示 -->
    <?php if (!empty($error_message)): ?>
        <?php foreach ($error_message as $value): ?>
            <ul class="error_message">
                <li><?php echo $value ?></li>
            </ul>
        <?php endforeach;?>
    <?php endif;?>

    <!-- ログイン情報があれば、データたちを表示する。 -->
<?php if (!empty($_SESSION["admin-login"]) && $_SESSION["admin-login"] === true): ?>

    <form action="./download.php" method="get">
        <input type="submit" value="ダウンロード" name="btn-download">
    </form>

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

<?php else: ?>
    <form method="post">
        <div>
            <label for="admin-password">ログインパスワード</label>
            <input type="password" id="admin-password" name="admin-password" value="">
        </div>
            <input type="submit" value="ログイン" name="btn-submit">
    </form>

<?php endif;?>
</body>
</html>
