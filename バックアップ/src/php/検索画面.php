<?php
require_once '../db_connect.php';
    session_start();
    $title = $_POST["search-title"];
    $id = $_POST["search-id"];
    $rui = $_POST["genre-rui"];
    $mou = $_POST["genre-mou"];
    $me = $_POST["genre-me"];
    $publisher = $_POST["search-publisher"];
    $author = $_POST["search-author"];

    try{
        $db = new db_connect();
        $db->connect();
        //$select_sql = $db->pdo->prepare ("select * FROM book WHERE title = :title or id = :id");
        $select_sql = "select * FROM book WHERE 1 = 1";
        $params = [];

        if(!empty($_POST["search-title"])){
            $select_sql.="AND title LIKE ?";
            $params[] = "%".$_POST['search-title']."%";
        }

        if(!empty($_POST['search-id'])){
            $select_sql.="AND search-id = ?";
            $params[] = $_POST['search-id'];
        }

        if(!empty($_POST['search-publisher'])){
            $select_sql. = "AND search-publisher LIKE ?";
            $params[] = "%".$_POST['search-publisher']."%";
        }

        if(!empty($_POST['search-auther'])){
            $select_sql. = "AND search-auther LIKE ?";
            $params[] = "%".$_POST['search-suther']."%";
        }

        $stmt = $db->prepare($select_sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

    }catch(PDOException as e){
        echo"データベースエラー"
    }catch(Exception as e){
        echo "エラー"
    }finally {
        $db->close();
    }
