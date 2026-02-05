<?php
class db_connect {
    private $host = 'localhost';
    private $db   = 'library_app';
    private $user = 'user01';
    private $pass = 'user01';
    private $charset = 'utf8mb4';

    public $pdo;

    //データベースへ接続
    public function connect() {

        //DSNプレフィックス(どの種類のデータベースに接続するかを示す。接頭辞とも書かれる)はmysql
        //portがデフォルトでない場合は、;port=ポート番号　を追加する。今回はデフォルト（3306）なので不要
        $dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass);

        } catch (PDOException $e) {
            echo "データベース接続エラー：" . $e->getMessage(); //デバッグ用。あとで消す！
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        } catch (Exception $e) {
            echo "エラー：" . $e->getMessage(); //デバッグ用。あとで消す！
            throw new Exception($e->getMessage(), (int)$e->getCode());
        }
    }

    //データベース接続を閉じる
    public function close() {
        $this->pdo = null;
    }

    //Webアプリケーションなので、Javaのようにcloseメソッドは不要（ブラウザを閉じたら自動的に切断される）
}
?>