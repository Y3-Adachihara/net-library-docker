<?php
    require_once 'db_connect.php';

    //〇学校マスタデータ（主キーはAUTO_INCREMENTではなくINT型なので、明示的に指定する）
    //もし配送中であった場合、positionは0とし、現在地は配送中として扱う
    $school_master = [
        0 => ['school_name' => '配送中', 'has_library' => false, 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        1 => ['school_name' => '第一小学校', 'has_library' => true, 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        2 => ['school_name' => '第二小学校', 'has_library' => true, 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        3 => ['school_name' => '第三小学校', 'has_library' => true, 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        4 => ['school_name' => '第四小学校', 'has_library' => true, 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        5 => ['school_name' => '第五小学校', 'has_library' => true, 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        6 => ['school_name' => '第六小学校', 'has_library' => true, 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        7 => ['school_name' => '第七小学校', 'has_library' => false, 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        8 => ['school_name' => '第八小学校', 'has_library' => false, 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        9 => ['school_name' => '第九中学校', 'has_library' => true, 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        10 => ['school_name' => '第十中学校', 'has_library' => true, 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00']
    ];

    $roll_master = [
        1 => ['role_name' => '学生', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        2 => ['role_name' => '図書係', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00']
    ];


    $student_master = [
        // --- 既存データ (ID: 1~15) ---
        ['school_id' => 1, 'grade' => 6, 'class' => 'A', 'number' => 1, 'family_name' => '山田', 'first_name' => '太郎', 'password' => '1234', 'created_at' => '2020-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 1, 'grade' => 5, 'class' => 'B', 'number' => 3, 'family_name' => '佐藤', 'first_name' => '花子', 'password' => 'abcd', 'created_at' => '2021-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 2, 'grade' => 6, 'class' => '1', 'number' => 10, 'family_name' => '鈴木', 'first_name' => '一郎', 'password' => 'pass123', 'created_at' => '2020-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 3, 'grade' => 4, 'class' => '2', 'number' => 15, 'family_name' => '田中', 'first_name' => '次郎', 'password' => 'xyz789', 'created_at' => '2022-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 4, 'grade' => 5, 'class' => '1', 'number' => 8, 'family_name' => '高橋', 'first_name' => '愛', 'password' => 'qwerty', 'created_at' => '2021-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 5, 'grade' => 3, 'class' => '1', 'number' => 20, 'family_name' => '伊藤', 'first_name' => '健太', 'password' => 'zxcvbn', 'created_at' => '2023-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 6, 'grade' => 6, 'class' => '2', 'number' => 5, 'family_name' => '渡辺', 'first_name' => '美咲', 'password' => 'asdfgh', 'created_at' => '2020-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 7, 'grade' => 6, 'class' => '1', 'number' => 12, 'family_name' => '山本', 'first_name' => '図書無', 'password' => '0000', 'created_at' => '2020-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 8, 'grade' => 5, 'class' => '1', 'number' => 7, 'family_name' => '中村', 'first_name' => '遠方', 'password' => '1111', 'created_at' => '2021-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 9, 'grade' => 2, 'class' => 'A', 'number' => 1, 'family_name' => '小林', 'first_name' => '中学', 'password' => 'password', 'created_at' => '2024-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 10, 'grade' => 1, 'class' => '1', 'number' => 30, 'family_name' => '吉田', 'first_name' => '新一', 'password' => '45gds', 'created_at' => '2025-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 9, 'grade' => 3, 'class' => 'B', 'number' => 22, 'family_name' => '加藤', 'first_name' => '学', 'password' => 'lmno56', 'created_at' => '2023-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 10, 'grade' => 3, 'class' => '2', 'number' => 14, 'family_name' => '佐々木', 'first_name' => '受験', 'password' => 'abc123', 'created_at' => '2023-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 1, 'grade' => 6, 'class' => 'A', 'number' => 2, 'family_name' => '松本', 'first_name' => '潤', 'password' => 'def456', 'created_at' => '2020-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 9, 'grade' => 2, 'class' => 'A', 'number' => 2, 'family_name' => '井上', 'first_name' => '真央', 'password' => 'ghi789', 'created_at' => '2024-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],

        // --- 新規追加データ (ID: 16~) ---

        // 1小 (ID:1) 追加
        ['school_id' => 1, 'grade' => 1, 'class' => 'A', 'number' => 5, 'family_name' => '青木', 'first_name' => '涼', 'password' => 'ao123', 'created_at' => '2025-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 1, 'grade' => 3, 'class' => 'B', 'number' => 11, 'family_name' => '石井', 'first_name' => '優子', 'password' => 'ishi55', 'created_at' => '2023-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 1, 'grade' => 4, 'class' => 'A', 'number' => 8, 'family_name' => '上田', 'first_name' => '翔太', 'password' => 'ue888', 'created_at' => '2022-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],

        // 2小 (ID:2) 追加
        ['school_id' => 2, 'grade' => 2, 'class' => '1', 'number' => 2, 'family_name' => '江藤', 'first_name' => '美月', 'password' => 'eto22', 'created_at' => '2024-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 2, 'grade' => 5, 'class' => '2', 'number' => 18, 'family_name' => '太田', 'first_name' => '拓海', 'password' => 'ota55', 'created_at' => '2021-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 2, 'grade' => 1, 'class' => '1', 'number' => 9, 'family_name' => '菊池', 'first_name' => '風磨', 'password' => 'sexy5', 'created_at' => '2025-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],

        // 3小 (ID:3) 追加
        ['school_id' => 3, 'grade' => 6, 'class' => '1', 'number' => 3, 'family_name' => '木村', 'first_name' => '拓哉', 'password' => 'smap1', 'created_at' => '2020-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 3, 'grade' => 3, 'class' => '2', 'number' => 21, 'family_name' => '久保', 'first_name' => '建英', 'password' => 'socc10', 'created_at' => '2023-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 3, 'grade' => 2, 'class' => '1', 'number' => 7, 'family_name' => '工藤', 'first_name' => '静香', 'password' => 'kudo8', 'created_at' => '2024-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],

        // 4小 (ID:4) 追加
        ['school_id' => 4, 'grade' => 4, 'class' => '1', 'number' => 4, 'family_name' => '近藤', 'first_name' => '真彦', 'password' => 'matchy', 'created_at' => '2022-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 4, 'grade' => 1, 'class' => '2', 'number' => 11, 'family_name' => '斉藤', 'first_name' => '由貴', 'password' => 'ske44', 'created_at' => '2025-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 4, 'grade' => 5, 'class' => '1', 'number' => 25, 'family_name' => '島田', 'first_name' => '紳助', 'password' => 'hex00', 'created_at' => '2021-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],

        // 5小 (ID:5) 追加
        ['school_id' => 5, 'grade' => 6, 'class' => '2', 'number' => 1, 'family_name' => '杉山', 'first_name' => '愛', 'password' => 'tenn1s', 'created_at' => '2020-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 5, 'grade' => 2, 'class' => '1', 'number' => 19, 'family_name' => '関口', 'first_name' => 'メンディー', 'password' => 'gene7', 'created_at' => '2024-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 5, 'grade' => 4, 'class' => '1', 'number' => 6, 'family_name' => '高田', 'first_name' => '純次', 'password' => 'junji1', 'created_at' => '2022-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],

        // 6小 (ID:6) 追加
        ['school_id' => 6, 'grade' => 5, 'class' => '2', 'number' => 16, 'family_name' => '千葉', 'first_name' => '雄大', 'password' => 'chiba7', 'created_at' => '2021-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 6, 'grade' => 1, 'class' => '1', 'number' => 3, 'family_name' => '土屋', 'first_name' => '太鳳', 'password' => 'tao228', 'created_at' => '2025-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 6, 'grade' => 3, 'class' => '1', 'number' => 28, 'family_name' => '富田', 'first_name' => '鈴花', 'password' => 'hina46', 'created_at' => '2023-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],

        // 7小 (ID:7) 追加
        ['school_id' => 7, 'grade' => 2, 'class' => '2', 'number' => 13, 'family_name' => '中島', 'first_name' => '健人', 'password' => 'sexy1', 'created_at' => '2024-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 7, 'grade' => 4, 'class' => '1', 'number' => 22, 'family_name' => '西野', 'first_name' => '七瀬', 'password' => 'nana7', 'created_at' => '2022-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 7, 'grade' => 6, 'class' => '1', 'number' => 5, 'family_name' => '野口', 'first_name' => '英世', 'password' => '1000y', 'created_at' => '2020-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],

        // 8小 (ID:8) 追加
        ['school_id' => 8, 'grade' => 3, 'class' => '1', 'number' => 8, 'family_name' => '橋本', 'first_name' => '環奈', 'password' => 'kanna0', 'created_at' => '2023-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 8, 'grade' => 1, 'class' => '2', 'number' => 17, 'family_name' => '平野', 'first_name' => '紫耀', 'password' => 'king01', 'created_at' => '2025-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 8, 'grade' => 5, 'class' => '1', 'number' => 30, 'family_name' => '福山', 'first_name' => '雅治', 'password' => 'masha1', 'created_at' => '2021-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],

        // 9中 (ID:9) 追加
        ['school_id' => 9, 'grade' => 1, 'class' => 'A', 'number' => 5, 'family_name' => '藤井', 'first_name' => '風', 'password' => 'kaze00', 'created_at' => '2025-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 9, 'grade' => 3, 'class' => 'B', 'number' => 12, 'family_name' => '星野', 'first_name' => '源', 'password' => 'gen123', 'created_at' => '2023-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 9, 'grade' => 2, 'class' => 'C', 'number' => 20, 'family_name' => '松田', 'first_name' => '聖子', 'password' => 'seiko1', 'created_at' => '2024-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 9, 'grade' => 1, 'class' => 'B', 'number' => 8, 'family_name' => '水卜', 'first_name' => '麻美', 'password' => 'miura1', 'created_at' => '2025-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],

        // 10中 (ID:10) 追加
        ['school_id' => 10, 'grade' => 2, 'class' => '1', 'number' => 15, 'family_name' => '森田', 'first_name' => '一義', 'password' => 'tamori', 'created_at' => '2024-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 10, 'grade' => 3, 'class' => '2', 'number' => 4, 'family_name' => '山下', 'first_name' => '智久', 'password' => 'yamaP', 'created_at' => '2023-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 10, 'grade' => 1, 'class' => '1', 'number' => 23, 'family_name' => '横浜', 'first_name' => '流星', 'password' => 'ryusei', 'created_at' => '2025-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
        ['school_id' => 10, 'grade' => 2, 'class' => '3', 'number' => 10, 'family_name' => '和田', 'first_name' => 'アキ子', 'password' => 'akiko1', 'created_at' => '2024-04-01 10:00:00', 'updated_at' => '2025-04-01 10:00:00'],
    ];


    $book_status_master = [
        ['status_id' => 1, 'status_name' => '貸出可能', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        ['status_id' => 2, 'status_name' => '貸出中', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        ['status_id' => 3, 'status_name' => '予約済み', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        ['status_id' => 4, 'status_name' => '予約受取待ち', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        ['status_id' => 5, 'status_name' => '配送待ち（往路）', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        ['status_id' => 6, 'status_name' => '配送中（往路）', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        ['status_id' => 7, 'status_name' => '配送予約受取待ち', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        ['status_id' => 8, 'status_name' => '配送待ち（復路）', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        ['status_id' => 9, 'status_name' => '配送中（復路）', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        ['status_id' => 10, 'status_name' => '貸出不可', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        ['status_id' => 11, 'status_name' => '紛失', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        ['status_id' => 12, 'status_name' => '修繕中', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        ['status_id' => 13, 'status_name' => '除籍', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00']
    ];

    $reservation_status_master = [
        ['status_id' => 1, 'status_name' => '予約完了', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        ['status_id' => 2, 'status_name' => 'キャンセル済み', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        ['status_id' => 3, 'status_name' => '受取済み', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00']
    ];

    $delivery_status_master = [
        ['status_id' => 1, 'status_name' => '配送待ち', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        ['status_id' => 2, 'status_name' => '配送中', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        ['status_id' => 3, 'status_name' => '配送完了', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00']
    ];

    $delivery_type_master = [
        ['type_id' => 1, 'type_name' => '予約配送', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        ['type_id' => 2, 'type_name' => '返却配送', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00']
    ];



    //テーブル構成修正後の貸出リスト (20件)
    $lending_list = [
        // --- 過去の貸出（返却済み） ---
        ['student_id' => 1, 'book_id' => '913000501', 'lending_date' => '2024-04-10 10:00:00', 'return_date' => '2024-04-17 12:30:00'],
        ['student_id' => 2, 'book_id' => '913000702', 'lending_date' => '2024-04-11 15:00:00', 'return_date' => '2024-04-18 09:00:00'],
        ['student_id' => 3, 'book_id' => '913000201', 'lending_date' => '2024-04-15 11:00:00', 'return_date' => '2024-04-22 11:35:56'],
        ['student_id' => 4, 'book_id' => '913000101', 'lending_date' => '2024-04-20 13:00:00', 'return_date' => '2024-04-25 10:00:00'],
        // 他校貸出の返却済み
        ['student_id' => 8, 'book_id' => '933000201', 'lending_date' => '2024-05-01 10:00:00', 'return_date' => '2024-05-08 16:00:00'],
        ['student_id' => 10, 'book_id' => '913000401', 'lending_date' => '2024-05-05 12:00:00', 'return_date' => '2024-05-12 12:00:00'],
        ['student_id' => 11, 'book_id' => '913001502', 'lending_date' => '2024-05-10 14:00:00', 'return_date' => '2024-05-17 09:30:00'],
        ['student_id' => 7, 'book_id' => '913001202', 'lending_date' => '2024-05-15 11:30:00', 'return_date' => '2024-05-22 15:00:00'],
        ['student_id' => 14, 'book_id' => '913000801', 'lending_date' => '2024-05-20 10:00:00', 'return_date' => '2024-05-21 10:00:00'],
        ['student_id' => 5, 'book_id' => '913000601', 'lending_date' => '2024-05-25 15:30:00', 'return_date' => '2024-06-01 09:00:00'],

        // --- 現在貸出中（これらは book_master の position と status に影響する） ---
        // 11. 自校貸出: 学問のすゝめ
        ['student_id' => 1, 'book_id' => '913002002', 'lending_date' => '2024-06-01 10:00:00', 'return_date' => null],
        // 12. 自校貸出: 高瀬舟
        ['student_id' => 2, 'book_id' => '913001602', 'lending_date' => '2024-06-02 12:00:00', 'return_date' => null],
        // 13. 自校貸出: 星の王子さま
        ['student_id' => 3, 'book_id' => '953000102', 'lending_date' => '2024-06-03 14:00:00', 'return_date' => null],
        // 14. 他校貸出(取り寄せ): こころ
        ['student_id' => 9, 'book_id' => '913000101', 'lending_date' => '2024-06-04 11:00:00', 'return_date' => null],
        // 15. 他校貸出(取り寄せ): 銀河鉄道の夜
        ['student_id' => 12, 'book_id' => '913000402', 'lending_date' => '2024-06-05 09:00:00', 'return_date' => null],
        // 16. 自校貸出: コンビニ人間
        ['student_id' => 13, 'book_id' => '913003002', 'lending_date' => '2024-06-06 16:00:00', 'return_date' => null],
        // 17. 自校貸出: 羅生門
        ['student_id' => 6, 'book_id' => '913000301', 'lending_date' => '2024-06-06 16:30:00', 'return_date' => null],
        // 18. 他校貸出(取り寄せ): 夜明け前
        ['student_id' => 14, 'book_id' => '913001802', 'lending_date' => '2024-06-07 10:00:00', 'return_date' => null],
        // 19. 自校貸出: 伊豆の踊子
        ['student_id' => 15, 'book_id' => '913000802', 'lending_date' => '2024-06-07 12:30:00', 'return_date' => null],
        // 20. 延滞中(他校): 変身
        ['student_id' => 10, 'book_id' => '943000102', 'lending_date' => '2024-05-20 10:00:00', 'return_date' => null]
    ];


    // テーブル構成変更後の予約リスト (13件)
    // status_id: 1=予約完了(進行中), 2=キャンセル, 3=受取済み(貸出に移行済み)
    $reservation_list = [
        // --- 貸出に移行済みの予約 (status_id = 3) ---
        // 他校から借りている本は基本的に予約経由
        ['student_id' => 8, 'book_id' => '933000201', 'status_id' => 3, 'reservation_date' => '2024-04-28 10:00:00'],
        ['student_id' => 9, 'book_id' => '913000101', 'status_id' => 3, 'reservation_date' => '2024-06-01 11:00:00'],
        ['student_id' => 14, 'book_id' => '913001802', 'status_id' => 3, 'reservation_date' => '2024-06-05 10:00:00'],

        // --- キャンセルされた予約 (status_id = 2) ---
        ['student_id' => 3, 'book_id' => '913000501', 'status_id' => 2, 'reservation_date' => '2024-04-12 09:00:00'],

        // --- 現在進行中の予約 (status_id = 1) ---
        // 5. 貸出待ち: 貸出中(No.14)の「こころ」を待っている
        ['student_id' => 4, 'book_id' => '913000101', 'status_id' => 1, 'reservation_date' => '2024-06-05 10:00:00'],
        
        // 6. 貸出待ち: 貸出中(No.17)の「羅生門」を待っている
        ['student_id' => 6, 'book_id' => '913000301', 'status_id' => 1, 'reservation_date' => '2024-06-06 12:00:00'],

        // 7. 貸出待ち: 「羅生門」の2人目の待ち
        ['student_id' => 3, 'book_id' => '913000301', 'status_id' => 1, 'reservation_date' => '2024-06-07 09:00:00'],

        // 8. ★配送中 (Position=0になるケース): 第七小(図書室なし)が、第一小の「風の又三郎」を予約し、移動中
        ['student_id' => 8, 'book_id' => '913001101', 'status_id' => 1, 'reservation_date' => '2024-06-08 08:30:00'],

        // 9. ★配送中 (Position=0): 第八小が、第二小の「雪国」を予約し、移動中
        ['student_id' => 9, 'book_id' => '913000701', 'status_id' => 1, 'reservation_date' => '2024-06-07 14:00:00'],

        // 10. 受取待ち: 自校の「1Q84」が確保されカウンターにある
        ['student_id' => 1, 'book_id' => '913002601', 'status_id' => 1, 'reservation_date' => '2024-06-01 10:00:00'],

        // 11. 受取待ち: 他校(第九中)から届いた「老人と海」が第十中のカウンターにある
        ['student_id' => 13, 'book_id' => '933000102', 'status_id' => 1, 'reservation_date' => '2024-06-03 11:00:00'],

        // 12. 貸出待ち: 他校貸出中の本を予約
        ['student_id' => 14, 'book_id' => '913002002', 'status_id' => 1, 'reservation_date' => '2024-06-08 10:00:00'],

        // 13. 貸出待ち
        ['student_id' => 15, 'book_id' => '933000202', 'status_id' => 1, 'reservation_date' => '2024-06-08 11:00:00']
    ];


    $book_info = [
        ['isbn' => '978-4-00-310101-8', 'title' => 'こころ', 'author_name' => '夏目漱石', 'author_kana' => 'ナツメソウセキ', 'publisher' => '岩波書店', 'publication_year' => '1990-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-101003-7', 'title' => 'こころ', 'author_name' => '夏目漱石', 'author_kana' => 'ナツメソウセキ', 'publisher' => '新潮社', 'publication_year' => '1995-05-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-100605-4', 'title' => '人間失格', 'author_name' => '太宰治', 'author_kana' => 'ダザイオサム', 'publisher' => '新潮社', 'publication_year' => '1992-04-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-04-109900-1', 'title' => '人間失格', 'author_name' => '太宰治', 'author_kana' => 'ダザイオサム', 'publisher' => '角川書店', 'publication_year' => '2000-12-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-00-310051-6', 'title' => '羅生門', 'author_name' => '芥川龍之介', 'author_kana' => 'アクタガワリュウノスケ', 'publisher' => '岩波書店', 'publication_year' => '1998-03-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-102501-7', 'title' => '羅生門', 'author_name' => '芥川龍之介', 'author_kana' => 'アクタガワリュウノスケ', 'publisher' => '新潮社', 'publication_year' => '2005-08-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-04-104003-4', 'title' => '銀河鉄道の夜', 'author_name' => '宮沢賢治', 'author_kana' => 'ミヤザワケンジ', 'publisher' => '角川書店', 'publication_year' => '1996-07-07', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-109205-5', 'title' => '銀河鉄道の夜', 'author_name' => '宮沢賢治', 'author_kana' => 'ミヤザワケンジ', 'publisher' => '新潮社', 'publication_year' => '2001-11-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-101001-3', 'title' => '坊っちゃん', 'author_name' => '夏目漱石', 'author_kana' => 'ナツメソウセキ', 'publisher' => '新潮社', 'publication_year' => '1989-02-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-00-310103-2', 'title' => '坊っちゃん', 'author_name' => '夏目漱石', 'author_kana' => 'ナツメソウセキ', 'publisher' => '岩波書店', 'publication_year' => '2010-04-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-100601-6', 'title' => '走れメロス', 'author_name' => '太宰治', 'author_kana' => 'ダザイオサム', 'publisher' => '新潮社', 'publication_year' => '1993-05-15', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-100101-1', 'title' => '雪国', 'author_name' => '川端康成', 'author_kana' => 'カワバタヤスナリ', 'publisher' => '新潮社', 'publication_year' => '1960-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-00-310813-0', 'title' => '雪国', 'author_name' => '川端康成', 'author_kana' => 'カワバタヤスナリ', 'publisher' => '岩波書店', 'publication_year' => '1980-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-100102-8', 'title' => '伊豆の踊子', 'author_name' => '川端康成', 'author_kana' => 'カワバタヤスナリ', 'publisher' => '新潮社', 'publication_year' => '1991-03-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-04-100506-4', 'title' => '伊豆の踊子', 'author_name' => '川端康成', 'author_kana' => 'カワバタヤスナリ', 'publisher' => '角川書店', 'publication_year' => '2005-06-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-105008-6', 'title' => '金閣寺', 'author_name' => '三島由紀夫', 'author_kana' => 'ミシマユキオ', 'publisher' => '新潮社', 'publication_year' => '1970-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-00-310102-5', 'title' => '吾輩は猫である', 'author_name' => '夏目漱石', 'author_kana' => 'ナツメソウセキ', 'publisher' => '岩波書店', 'publication_year' => '1988-10-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-06-196009-1', 'title' => '吾輩は猫である', 'author_name' => '夏目漱石', 'author_kana' => 'ナツメソウセキ', 'publisher' => '講談社', 'publication_year' => '2000-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-00-310702-7', 'title' => '風の又三郎', 'author_name' => '宮沢賢治', 'author_kana' => 'ミヤザワケンジ', 'publisher' => '岩波書店', 'publication_year' => '1995-11-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-102502-4', 'title' => '蜘蛛の糸', 'author_name' => '芥川龍之介', 'author_kana' => 'アクタガワリュウノスケ', 'publisher' => '新潮社', 'publication_year' => '1999-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-04-100307-7', 'title' => '蜘蛛の糸', 'author_name' => '芥川龍之介', 'author_kana' => 'アクタガワリュウノスケ', 'publisher' => '角川書店', 'publication_year' => '2008-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-100604-7', 'title' => '斜陽', 'author_name' => '太宰治', 'author_kana' => 'ダザイオサム', 'publisher' => '新潮社', 'publication_year' => '1980-05-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-00-310904-5', 'title' => '斜陽', 'author_name' => '太宰治', 'author_kana' => 'ダザイオサム', 'publisher' => '岩波書店', 'publication_year' => '1990-06-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-12-200994-9', 'title' => '細雪', 'author_name' => '谷崎潤一郎', 'author_kana' => 'タニザキジュンイチロウ', 'publisher' => '中公文庫', 'publication_year' => '1985-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-00-311451-3', 'title' => '山月記', 'author_name' => '中島敦', 'author_kana' => 'ナカジマアツシ', 'publisher' => '岩波書店', 'publication_year' => '1994-02-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-106401-4', 'title' => '山月記', 'author_name' => '中島敦', 'author_kana' => 'ナカジマアツシ', 'publisher' => '新潮社', 'publication_year' => '2003-07-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-00-310065-3', 'title' => '高瀬舟', 'author_name' => '森鴎外', 'author_kana' => 'モリオウガイ', 'publisher' => '岩波書店', 'publication_year' => '1982-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-102004-3', 'title' => '高瀬舟', 'author_name' => '森鴎外', 'author_kana' => 'モリオウガイ', 'publisher' => '新潮社', 'publication_year' => '1995-10-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-00-310060-8', 'title' => '舞姫', 'author_name' => '森鴎外', 'author_kana' => 'モリオウガイ', 'publisher' => '岩波書店', 'publication_year' => '1988-04-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-102002-9', 'title' => '舞姫', 'author_name' => '森鴎外', 'author_kana' => 'モリオウガイ', 'publisher' => '新潮社', 'publication_year' => '2001-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-00-310351-8', 'title' => '夜明け前', 'author_name' => '島崎藤村', 'author_kana' => 'シマザキトウソン', 'publisher' => '岩波書店', 'publication_year' => '1975-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-105505-0', 'title' => '夜明け前', 'author_name' => '島崎藤村', 'author_kana' => 'シマザキトウソン', 'publisher' => '新潮社', 'publication_year' => '1990-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-00-331021-2', 'title' => '学問のすゝめ', 'author_name' => '福沢諭吉', 'author_kana' => 'フクザワユキチ', 'publisher' => '岩波書店', 'publication_year' => '1980-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-06-158498-3', 'title' => '学問のすゝめ', 'author_name' => '福沢諭吉', 'author_kana' => 'フクザワユキチ', 'publisher' => '講談社', 'publication_year' => '2005-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-00-331181-3', 'title' => '武士道', 'author_name' => '新渡戸稲造', 'author_kana' => 'ニトベイナゾウ', 'publisher' => '岩波書店', 'publication_year' => '1990-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-06-292100-8', 'title' => '武士道', 'author_name' => '新渡戸稲造', 'author_kana' => 'ニトベイナゾウ', 'publisher' => '講談社', 'publication_year' => '2000-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-00-302301-3', 'title' => '東海道中膝栗毛', 'author_name' => '十返舎一九', 'author_kana' => 'ジッペンシャイック', 'publisher' => '岩波書店', 'publication_year' => '1985-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-107001-5', 'title' => '東海道中膝栗毛', 'author_name' => '十返舎一九', 'author_kana' => 'ジッペンシャイック', 'publisher' => '新潮社', 'publication_year' => '1995-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-16-390618-8', 'title' => 'コンビニ人間', 'author_name' => '村田沙耶香', 'author_kana' => 'ムラタサヤカ', 'publisher' => '文藝春秋', 'publication_year' => '2016-07-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-353422-8', 'title' => '1Q84', 'author_name' => '村上春樹', 'author_kana' => 'ムラカミハルキ', 'publisher' => '新潮社', 'publication_year' => '2009-05-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-915512-37-7', 'title' => 'ハリー・ポッターと賢者の石', 'author_name' => 'J.K.ローリング', 'author_kana' => 'ローリング', 'publisher' => '静山社', 'publication_year' => '1999-12-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-00-325326-7', 'title' => '星の王子さま', 'author_name' => 'サン=テグジュペリ', 'author_kana' => 'サンテグジュペリ', 'publisher' => '岩波書店', 'publication_year' => '2000-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-212204-4', 'title' => '星の王子さま', 'author_name' => 'サン=テグジュペリ', 'author_kana' => 'サンテグジュペリ', 'publisher' => '新潮社', 'publication_year' => '2006-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-207101-4', 'title' => '変身', 'author_name' => 'カフカ', 'author_kana' => 'カフカ', 'publisher' => '新潮社', 'publication_year' => '1952-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-00-324541-5', 'title' => '変身', 'author_name' => 'カフカ', 'author_kana' => 'カフカ', 'publisher' => '岩波書店', 'publication_year' => '1960-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-210001-1', 'title' => '老人と海', 'author_name' => 'ヘミングウェイ', 'author_kana' => 'ヘミングウェイ', 'publisher' => '新潮社', 'publication_year' => '1966-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['isbn' => '978-4-10-211301-1', 'title' => '赤毛のアン', 'author_name' => 'モンゴメリ', 'author_kana' => 'モンゴメリ', 'publisher' => '新潮社', 'publication_year' => '1955-01-01', 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00']
    ];


    $book_stack = [
        // --- 夏目漱石 ---
        ['stack_id' => '913000101', 'isbn' => '978-4-00-310101-8', 'school_id' => 3, 'status_id' => 1, 'position' => 3, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913000102', 'isbn' => '978-4-10-101003-7', 'school_id' => 9, 'status_id' => 1, 'position' => 9, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913000501', 'isbn' => '978-4-10-101001-3', 'school_id' => 1, 'status_id' => 1, 'position' => 1, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2025-09-15 12:25:00'], // 貸出返却
        ['stack_id' => '913000502', 'isbn' => '978-4-00-310103-2', 'school_id' => 6, 'status_id' => 1, 'position' => 6, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913001001', 'isbn' => '978-4-00-310102-5', 'school_id' => 3, 'status_id' => 1, 'position' => 3, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913001002', 'isbn' => '978-4-06-196009-1', 'school_id' => 5, 'status_id' => 1, 'position' => 5, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],

        // --- 太宰治 ---
        ['stack_id' => '913000201', 'isbn' => '978-4-10-100605-4', 'school_id' => 2, 'status_id' => 1, 'position' => 2, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913000202', 'isbn' => '978-4-04-109900-1', 'school_id' => 4, 'status_id' => 1, 'position' => 4, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913000601', 'isbn' => '978-4-10-100601-6', 'school_id' => 4, 'status_id' => 1, 'position' => 4, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913001301', 'isbn' => '978-4-10-100604-7', 'school_id' => 2, 'status_id' => 1, 'position' => 2, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913001302', 'isbn' => '978-4-00-310904-5', 'school_id' => 4, 'status_id' => 1, 'position' => 4, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],

        // --- 芥川龍之介 ---
        ['stack_id' => '913000301', 'isbn' => '978-4-00-310051-6', 'school_id' => 5, 'status_id' => 1, 'position' => 5, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913000302', 'isbn' => '978-4-10-102501-7', 'school_id' => 6, 'status_id' => 1, 'position' => 6, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913001201', 'isbn' => '978-4-10-102502-4', 'school_id' => 6, 'status_id' => 1, 'position' => 6, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913001202', 'isbn' => '978-4-04-100307-7', 'school_id' => 4, 'status_id' => 1, 'position' => 4, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],

        // --- 宮沢賢治 ---
        ['stack_id' => '913000401', 'isbn' => '978-4-04-104003-4', 'school_id' => 9, 'status_id' => 1, 'position' => 9, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913000402', 'isbn' => '978-4-10-109205-5', 'school_id' => 10, 'status_id' => 1, 'position' => 10, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2025-10-13 12:25:00'], // 配送(往)→貸出→配送(復)→貸出返却
        ['stack_id' => '913001101', 'isbn' => '978-4-00-310702-7', 'school_id' => 1, 'status_id' => 1, 'position' => 1, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],

        // --- 川端康成 ---
        ['stack_id' => '913000701', 'isbn' => '978-4-10-100101-1', 'school_id' => 2, 'status_id' => 1, 'position' => 2, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913000702', 'isbn' => '978-4-00-310813-0', 'school_id' => 3, 'status_id' => 1, 'position' => 3, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913000801', 'isbn' => '978-4-10-100102-8', 'school_id' => 1, 'status_id' => 1, 'position' => 1, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913000802', 'isbn' => '978-4-04-100506-4', 'school_id' => 9, 'status_id' => 1, 'position' => 9, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],

        // --- 三島由紀夫 ---
        ['stack_id' => '913000901', 'isbn' => '978-4-10-105008-6', 'school_id' => 10, 'status_id' => 1, 'position' => 10, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],

        // --- 谷崎潤一郎 ---
        ['stack_id' => '913001401', 'isbn' => '978-4-12-200994-9', 'school_id' => 9, 'status_id' => 1, 'position' => 9, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],

        // --- 中島敦 ---
        ['stack_id' => '913001501', 'isbn' => '978-4-00-311451-3', 'school_id' => 5, 'status_id' => 1, 'position' => 5, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913001502', 'isbn' => '978-4-10-106401-4', 'school_id' => 10, 'status_id' => 1, 'position' => 10, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],

        // --- 森鴎外 ---
        ['stack_id' => '913001601', 'isbn' => '978-4-00-310065-3', 'school_id' => 3, 'status_id' => 1, 'position' => 3, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913001602', 'isbn' => '978-4-10-102004-3', 'school_id' => 1, 'status_id' => 1, 'position' => 1, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913001701', 'isbn' => '978-4-00-310060-8', 'school_id' => 1, 'status_id' => 1, 'position' => 1, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2025-10-02 12:25:00'], // 予約→貸出返却
        ['stack_id' => '913001702', 'isbn' => '978-4-10-102002-9', 'school_id' => 5, 'status_id' => 1, 'position' => 5, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],

        // --- 島崎藤村 ---
        ['stack_id' => '913001801', 'isbn' => '978-4-00-310351-8', 'school_id' => 4, 'status_id' => 1, 'position' => 4, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913001802', 'isbn' => '978-4-10-105505-0', 'school_id' => 6, 'status_id' => 1, 'position' => 6, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2025-09-17 12:30:00'], // 貸出返却

        // --- 福沢諭吉 ---
        ['stack_id' => '913001901', 'isbn' => '978-4-00-331021-2', 'school_id' => 3, 'status_id' => 1, 'position' => 3, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913001902', 'isbn' => '978-4-06-158498-3', 'school_id' => 1, 'status_id' => 1, 'position' => 1, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],

        // --- 新渡戸稲造 ---
        ['stack_id' => '913002001', 'isbn' => '978-4-00-331181-3', 'school_id' => 1, 'status_id' => 1, 'position' => 1, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913002002', 'isbn' => '978-4-06-292100-8', 'school_id' => 2, 'status_id' => 1, 'position' => 2, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],

        // --- 十返舎一九 ---
        ['stack_id' => '913002301', 'isbn' => '978-4-00-302301-3', 'school_id' => 1, 'status_id' => 1, 'position' => 1, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913002302', 'isbn' => '978-4-10-107001-5', 'school_id' => 6, 'status_id' => 1, 'position' => 6, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2025-10-21 10:00:00'], // 配送(往復)→貸出→配送(往復) ※最後の配送完了日時

        // --- 村田沙耶香 ---
        ['stack_id' => '913003001', 'isbn' => '978-4-16-390618-8', 'school_id' => 10, 'status_id' => 1, 'position' => 10, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '913003002', 'isbn' => '978-4-16-390618-8', 'school_id' => 10, 'status_id' => 1, 'position' => 10, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2025-10-08 09:40:00'], // 配送(往)→貸出返却→配送(復)完了

        // --- 村上春樹 ---
        ['stack_id' => '913002601', 'isbn' => '978-4-10-353422-8', 'school_id' => 1, 'status_id' => 1, 'position' => 1, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],

        // --- J.K.ローリング ---
        ['stack_id' => '933000201', 'isbn' => '978-4-915512-37-7', 'school_id' => 1, 'status_id' => 1, 'position' => 1, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '933000202', 'isbn' => '978-4-915512-37-7', 'school_id' => 6, 'status_id' => 1, 'position' => 6, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],

        // --- サン=テグジュペリ ---
        ['stack_id' => '953000101', 'isbn' => '978-4-00-325326-7', 'school_id' => 2, 'status_id' => 1, 'position' => 2, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '953000102', 'isbn' => '978-4-10-212204-4', 'school_id' => 2, 'status_id' => 1, 'position' => 2, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],

        // --- カフカ ---
        ['stack_id' => '943000101', 'isbn' => '978-4-10-207101-4', 'school_id' => 9, 'status_id' => 1, 'position' => 9, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
        ['stack_id' => '943000102', 'isbn' => '978-4-00-324541-5', 'school_id' => 2, 'status_id' => 1, 'position' => 2, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],

        // --- ヘミングウェイ ---
        ['stack_id' => '933000102', 'isbn' => '978-4-10-210001-1', 'school_id' => 9, 'status_id' => 1, 'position' => 9, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],

        // --- モンゴメリ ---
        ['stack_id' => '933000301', 'isbn' => '978-4-10-211301-1', 'school_id' => 3, 'status_id' => 1, 'position' => 3, 'registered_at' => '2017-04-01 10:00:00', 'updated_at' => '2017-04-01 10:00:00'],
    ];


    $lending_list = [
        // 1. 2025-09-10: ストレート貸出（自校本）
        [
            'student_id'   => 1, 
            'book_id'      => '913000501', 
            'lending_date' => '2025-09-10 12:30:00', 
            'return_date'  => '2025-09-15 12:25:00'
        ],

        // 2. 2025-09-12: ストレート貸出（自校本）
        [
            'student_id'   => 7, 
            'book_id'      => '913001802', 
            'lending_date' => '2025-09-12 12:20:00', 
            'return_date'  => '2025-09-17 12:30:00'
        ],

        // 3. 2025-09-18: 予約あり・1人目（自校本）
        [
            'student_id'   => 14, 
            'book_id'      => '913001701', 
            'lending_date' => '2025-09-18 12:30:00', 
            'return_date'  => '2025-09-25 12:25:00'
        ],

        // 4. 2025-09-26: 予約あり・2人目（自校本）
        [
            'student_id'   => 2, 
            'book_id'      => '913001701', 
            'lending_date' => '2025-09-26 12:30:00', 
            'return_date'  => '2025-10-02 12:25:00'
        ],

        // 5. 2025-09-29: 予約あり・1人目（他校配送分）
        [
            'student_id'   => 6, 
            'book_id'      => '913000402', 
            'lending_date' => '2025-09-29 12:30:00', 
            'return_date'  => '2025-10-06 12:25:00'
        ],

        // 6. 2025-10-01: ストレート貸出（他校配送分）
        [
            'student_id'   => 4, 
            'book_id'      => '913003002', 
            'lending_date' => '2025-10-01 12:30:00', 
            'return_date'  => '2025-10-07 12:30:00'
        ],

        // 7. 2025-10-03: 予約連続・1人目（他校配送分）
        [
            'student_id'   => 15, 
            'book_id'      => '913002302', 
            'lending_date' => '2025-10-03 12:30:00', 
            'return_date'  => '2025-10-08 12:30:00'
        ],

        // 8. 2025-10-07: 予約あり・2人目（他校配送分）
        [
            'student_id'   => 13, 
            'book_id'      => '913000402', 
            'lending_date' => '2025-10-07 12:30:00', 
            'return_date'  => '2025-10-13 12:25:00'
        ],

        // 9. 2025-10-09: 予約連続・2人目（他校配送分）
        [
            'student_id'   => 7, 
            'book_id'      => '913002302', 
            'lending_date' => '2025-10-09 12:00:00', 
            'return_date'  => '2025-10-13 12:30:00'
        ],

        // 10. 2025-10-14: 予約連続・3人目（他校配送分）
        [
            'student_id'   => 12, 
            'book_id'      => '913002302', 
            'lending_date' => '2025-10-14 12:30:00', 
            'return_date'  => '2025-10-20 12:30:00'
        ],

        // [10/22] 3小・木村拓哉 / 夏目漱石『こころ』(未貸出)
        ['student_id' => 22, 'book_id' => '913000101', 'lending_date' => '2025-10-22 12:30:00', 'return_date' => '2025-10-28 12:25:00'],

        // [10/25] 10中・吉田新一 / 村田沙耶香『コンビニ人間』(StackId:01 未貸出)
        ['student_id' => 44, 'book_id' => '913003001', 'lending_date' => '2025-10-25 12:30:00', 'return_date' => '2025-10-31 12:30:00'],

        // [10/28] 2小・江藤美月 / サン=テグジュペリ『星の王子さま』(StackId:01 未貸出)
        ['student_id' => 19, 'book_id' => '953000101', 'lending_date' => '2025-10-28 12:20:00', 'return_date' => '2025-11-03 12:25:00'],

        // [11/01] 6小・千葉雄大 / J.K.ローリング『ハリー・ポッター』(StackId:02 未貸出)
        ['student_id' => 31, 'book_id' => '933000202', 'lending_date' => '2025-11-01 12:30:00', 'return_date' => '2025-11-07 12:25:00'],

        // [11/04] 1小・青木涼 / 宮沢賢治『銀河鉄道の夜』(StackId:01 未貸出)
        ['student_id' => 16, 'book_id' => '913001101', 'lending_date' => '2025-11-04 12:30:00', 'return_date' => '2025-11-10 12:25:00'],

        // [11/05] 9中・藤井風 / カフカ『変身』(StackId:01 未貸出)
        ['student_id' => 40, 'book_id' => '943000101', 'lending_date' => '2025-11-05 12:30:00', 'return_date' => '2025-11-11 12:30:00'],

        // [11/10] 4小・近藤真彦 / 太宰治『人間失格』(StackId:01 未貸出)
        ['student_id' => 25, 'book_id' => '913000601', 'lending_date' => '2025-11-10 12:25:00', 'return_date' => '2025-11-16 12:25:00'],

        // [11/12] 5小・杉山愛 / 中島敦『山月記』(StackId:01 未貸出)
        ['student_id' => 28, 'book_id' => '913001501', 'lending_date' => '2025-11-12 12:30:00', 'return_date' => '2025-11-18 12:30:00'],

        // [11/15] 10中・山下智久 / 三島由紀夫『金閣寺』(StackId:01 未貸出)
        ['student_id' => 45, 'book_id' => '913000901', 'lending_date' => '2025-11-15 12:30:00', 'return_date' => '2025-11-21 12:30:00'],

        // [11/18] 1小・石井優子 / J.K.ローリング『ハリー・ポッター』(StackId:01 未貸出)
        ['student_id' => 17, 'book_id' => '933000201', 'lending_date' => '2025-11-18 12:30:00', 'return_date' => '2025-11-24 12:25:00'],

        // [11/20] 2小・太田拓海 / 川端康成『雪国』(StackId:01 未貸出)
        ['student_id' => 20, 'book_id' => '913000701', 'lending_date' => '2025-11-20 12:20:00', 'return_date' => '2025-11-26 12:25:00'],

        // [11/22] 6小・土屋太鳳 / 夏目漱石『三四郎』(StackId:02 未貸出)
        ['student_id' => 32, 'book_id' => '913000502', 'lending_date' => '2025-11-22 12:30:00', 'return_date' => '2025-11-28 12:25:00'],

        // [11/25] 9中・星野源 / 谷崎潤一郎『春琴抄』(StackId:01 未貸出)
        ['student_id' => 41, 'book_id' => '913001401', 'lending_date' => '2025-11-25 12:30:00', 'return_date' => '2025-12-01 12:30:00'],

        // [11/28] 3小・久保建英 / モンゴメリ『赤毛のアン』(StackId:01 未貸出)
        ['student_id' => 23, 'book_id' => '933000301', 'lending_date' => '2025-11-28 12:30:00', 'return_date' => '2025-12-04 12:25:00'],

        // [12/02] 1小・上田翔太 / 村上春樹『海辺のカフカ』(StackId:01 未貸出)
        ['student_id' => 18, 'book_id' => '913002601', 'lending_date' => '2025-12-02 12:30:00', 'return_date' => '2025-12-08 12:25:00'],

        // [12/05] 4小・斉藤由貴 / 島崎藤村『破戒』(StackId:01 未貸出)
        ['student_id' => 26, 'book_id' => '913001801', 'lending_date' => '2025-12-05 12:25:00', 'return_date' => '2025-12-11 12:25:00'],

        // [12/08] 5小・関口メンディー / 森鴎外『舞姫』(StackId:02 未貸出)
        ['student_id' => 29, 'book_id' => '913001702', 'lending_date' => '2025-12-08 12:30:00', 'return_date' => '2025-12-14 12:30:00'],

        // [12/10] 2小・菊池風磨 / 太宰治『斜陽』(StackId:01 未貸出)
        ['student_id' => 21, 'book_id' => '913000201', 'lending_date' => '2025-12-10 12:20:00', 'return_date' => '2025-12-16 12:25:00'],

        // [12/12] 9中・松田聖子 / 宮沢賢治『注文の多い料理店』(StackId:01 未貸出)
        ['student_id' => 42, 'book_id' => '913000401', 'lending_date' => '2025-12-12 12:30:00', 'return_date' => '2025-12-18 12:30:00'],

        // [12/14] 6小・富田鈴花 / 芥川龍之介『蜘蛛の糸』(StackId:01 未貸出)
        ['student_id' => 33, 'book_id' => '913001201', 'lending_date' => '2025-12-14 12:30:00', 'return_date' => '2025-12-20 12:25:00']
    ];

    $reservation_list = [
        // 2. 予約→貸出完了（自校内）
        [
            'reservation_number' => '4812', 
            'student_id'         => 2, 
            'book_id'            => '913001701', 
            'status_id'          => 3, 
            'reservation_date'   => '2025-09-19 10:00:00', 
            'updated_at'         => '2025-09-26 12:30:01'
        ],

        // 3. 他校配送（10中 → 5小）受取完了
        [
            'reservation_number' => '0593', 
            'student_id'         => 6, 
            'book_id'            => '913000402', 
            'status_id'          => 3, 
            'reservation_date'   => '2025-09-28 10:00:00', 
            'updated_at'         => '2025-09-29 12:30:00'
        ],
        
        // 3. 他校配送（5小 → 10中）受取完了
        [
            'reservation_number' => '9201', 
            'student_id'         => 13, 
            'book_id'            => '913000402', 
            'status_id'          => 3, 
            'reservation_date'   => '2025-09-29 10:00:00', 
            'updated_at'         => '2025-10-07 12:30:00'
        ],

        // 4. 他校配送（3小 → 10中）受取完了
        [
            'reservation_number' => '0074', 
            'student_id'         => 4, 
            'book_id'            => '913003002', 
            'status_id'          => 3, 
            'reservation_date'   => '2025-09-30 13:00:00', 
            'updated_at'         => '2025-10-01 10:20:00'
        ],

        // 6. 予約連続（9中生 → 6小本）受取完了
        [
            'reservation_number' => '3380', 
            'student_id'         => 15, 
            'book_id'            => '913002302', 
            'status_id'          => 3, 
            'reservation_date'   => '2025-10-02 17:00:00', 
            'updated_at'         => '2025-10-03 12:30:00'
        ],

        // 6. 予約連続（6小生 → 6小本・貸出中）受取完了
        [
            'reservation_number' => '1955', 
            'student_id'         => 7, 
            'book_id'            => '913002302', 
            'status_id'          => 3, 
            'reservation_date'   => '2025-10-06 14:00:00', 
            'updated_at'         => '2025-10-09 12:00:00'
        ],

        // 6. 予約連続（9中生 → 6小本・貸出中）受取完了
        [
            'reservation_number' => '0821', 
            'student_id'         => 12, 
            'book_id'            => '913002302', 
            'status_id'          => 3, 
            'reservation_date'   => '2025-10-10 16:00:00', 
            'updated_at'         => '2025-10-14 12:30:00'
        ],
    ];

    $deliverer_master = [
        [
            'login_id'    => 'driver01',
            'password'    => 'truck888',
            'family_name' => '車田',
            'first_name'  => '剛',
            'created_at'  => '2015-04-01 10:00:00',
            'updated_at'  => '2015-04-01 10:00:00'
        ],

        [
            'login_id'    => 'driver02',
            'password'    => 'route66g',
            'family_name' => '配野',
            'first_name'  => '達也',
            'created_at'  => '2015-04-01 10:00:00',
            'updated_at'  => '2015-04-01 10:00:00'
        ],

        [
            'login_id'    => 'dispatch_mgr',
            'password'    => 'box_safe',
            'family_name' => '箱崎',
            'first_name'  => '守',
            'created_at'  => '2015-04-01 10:00:00',
            'updated_at'  => '2015-04-01 10:00:00'
        ]
    ];

    $delivery_list = [

        // --- 3. 10中と5小のやり取り ---

        // 3-3. 10中 → 5小（予約配送：往路）
        [
            'deliverer_id'    => 1, // 車田
            'from_school_id'  => 10,
            'to_school_id'    => 5,
            'delivery_type'   => 1, // 予約配送
            'delivery_status' => 3, // 配送完了
            'book_id'         => '913000402',
            'delivery_date'   => '2025-09-29 10:00:00',
            'arrival_date'    => '2025-09-29 10:40:00'
        ],

        // 3-4. 5小 → 10中（返却配送：復路）
        [
            'deliverer_id'    => 2, // 配野
            'from_school_id'  => 5,
            'to_school_id'    => 10,
            'delivery_type'   => 2, // 返却配送
            'delivery_status' => 3, // 配送完了
            'book_id'         => '913000402',
            'delivery_date'   => '2025-10-07 10:00:00',
            'arrival_date'    => '2025-10-07 11:00:00'
        ],


        // --- 4. 10中と3小のやり取り ---

        // 4. 10中 → 3小（予約配送：往路）
        [
            'deliverer_id'    => 3, // 箱崎
            'from_school_id'  => 10,
            'to_school_id'    => 3,
            'delivery_type'   => 1, // 予約配送
            'delivery_status' => 3, // 配送完了
            'book_id'         => '913003002',
            'delivery_date'   => '2025-10-01 09:55:00',
            'arrival_date'    => '2025-10-01 10:20:00'
        ],

        // 4. 3小 → 10中（返却配送：復路）
        [
            'deliverer_id'    => 1, // 車田
            'from_school_id'  => 3,
            'to_school_id'    => 10,
            'delivery_type'   => 2, // 返却配送
            'delivery_status' => 3, // 配送完了
            'book_id'         => '913003002',
            'delivery_date'   => '2025-10-08 09:00:00',
            'arrival_date'    => '2025-10-08 09:40:00'
        ],


        // --- 6. 6小本を巡る連続予約の配送 ---
        // ※ 配送タイプ順序: 1(予約) -> 1(予約) -> 1(予約) -> 2(返却)

        // 6-1. 6小 → 9中（予約配送：往路1）
        // 9中の生徒(ID:15)が予約
        [
            'deliverer_id'    => 2, // 配野
            'from_school_id'  => 6,
            'to_school_id'    => 9,
            'delivery_type'   => 1, // 予約配送
            'delivery_status' => 3, // 配送完了
            'book_id'         => '913002302',
            'delivery_date'   => '2025-10-03 09:00:00',
            'arrival_date'    => '2025-10-03 10:00:00'
        ],

        // 6-2. 9中 → 6小（予約配送：往路2 ※実質的な戻りだが次が予約のためType1）
        // 6小の生徒(ID:7)が予約したため、自校へ戻る
        [
            'deliverer_id'    => 3, // 箱崎
            'from_school_id'  => 9,
            'to_school_id'    => 6,
            'delivery_type'   => 1, // 予約配送
            'delivery_status' => 3, // 配送完了
            'book_id'         => '913002302',
            'delivery_date'   => '2025-10-09 09:00:00',
            'arrival_date'    => '2025-10-09 10:00:00' // 日付修正箇所
        ],

        // 6-3. 6小 → 9中（予約配送：往路3）
        // 9中の生徒(ID:12)が再度予約
        [
            'deliverer_id'    => 1, // 車田
            'from_school_id'  => 6,
            'to_school_id'    => 9,
            'delivery_type'   => 1, // 予約配送
            'delivery_status' => 3, // 配送完了
            'book_id'         => '913002302',
            'delivery_date'   => '2025-10-14 09:00:00',
            'arrival_date'    => '2025-10-14 10:00:00'
        ],

        // 6-4. 9中 → 6小（返却配送：復路・完全在庫戻し）
        // 予約が途切れたため、持ち主の学校へ返却
        [
            'deliverer_id'    => 2, // 配野
            'from_school_id'  => 9,
            'to_school_id'    => 6,
            'delivery_type'   => 2, // 返却配送
            'delivery_status' => 3, // 配送完了
            'book_id'         => '913002302',
            'delivery_date'   => '2025-10-21 09:00:00',
            'arrival_date'    => '2025-10-21 10:00:00'
        ]
    ];


    $librarian_master = [
        // 第一小学校の司書
        ['school_id' => 1, 'login_id' => 'lib01', 'password' => 'pass1234', 'family_name' => '図書', 'first_name' => '管理郎', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        // 第二小学校の司書
        ['school_id' => 2, 'login_id' => 'sato_sensei', 'password' => 'abcd5678', 'family_name' => '佐藤', 'first_name' => '司書郎', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00'],
        // 同じ学校に2人いるケース（第一小学校）
        ['school_id' => 1, 'login_id' => 'lib02', 'password' => 'pass5678', 'family_name' => '鈴木', 'first_name' => '根郎', 'created_at' => '2015-04-01 10:00:00', 'updated_at' => '2015-04-01 10:00:00']
    ];


    if (isset($_POST['insert_tmpData'])) {
        $db = new db_connect();
        $db->connect();

        $tables = ['delivery', 'deliverer', 'librarian', 'lending', 'reservation', 'book_stack', 'book_info', 'delivery_type', 'delivery_status', 'reservation_status', 'book_status', 'student', 'user_role', 'school'];

        try {
            $db->pdo->beginTransaction();

            //各テーブルのデータを削除
            foreach ($tables as $table) {
                $db->pdo->exec("DELETE FROM {$table}");
            }

            //学校テーブルに学校データを挿入
            $stmt_school = $db->pdo->prepare("INSERT INTO school VALUES (:school_id, :school_name, :has_library, :created_at, :updated_at)");

            foreach ($school_master as $key => $values) {
                $stmt_school->bindValue(':school_id', $key, PDO::PARAM_INT);
                
                $stmt_school->bindValue(':school_name', $values['school_name'], PDO::PARAM_STR);
                $stmt_school->bindValue(':has_library', $values['has_library'], PDO::PARAM_BOOL);
                $stmt_school->bindValue(':created_at', $values['created_at'], PDO::PARAM_STR);
                $stmt_school->bindValue(':updated_at', $values['updated_at'], PDO::PARAM_STR);
                
                $stmt_school->execute();
            }


            $stmt_role = $db->pdo->prepare("INSERT INTO user_role VALUES (:role_id, :role_name, :created_at, :updated_at)");

            foreach ($roll_master as $key => $values) {
                $stmt_role->bindValue(':role_id', $key, PDO::PARAM_INT);

                $stmt_role->bindValue(':role_name', $values['role_name'], PDO::PARAM_STR);
                $stmt_role->bindValue(':created_at', $values['created_at'], PDO::PARAM_STR);
                $stmt_role->bindValue(':updated_at', $values['updated_at'], PDO::PARAM_STR);

                $stmt_role->execute();
            }

            //学生テーブルに学生データを挿入
            $stmt_student = $db->pdo->prepare("INSERT INTO student (school_id, grade, class, number, family_name, first_name, password, created_at, updated_at) VALUES (:school_id, :grade, :class, :number, :family_name, :first_name, :password, :created_at, :updated_at)");

            foreach ($student_master as $student) {
                $stmt_student->bindValue(':school_id', $student['school_id'], PDO::PARAM_INT);
                $stmt_student->bindValue(':grade', $student['grade'], PDO::PARAM_INT);
                $stmt_student->bindValue(':class', $student['class'], PDO::PARAM_STR);
                $stmt_student->bindValue(':number', $student['number'], PDO::PARAM_INT);
                $stmt_student->bindValue(':family_name', $student['family_name'], PDO::PARAM_STR);
                $stmt_student->bindValue(':first_name', $student['first_name'], PDO::PARAM_STR);
                
                //パスワードは、元の配列で平文として入れられているので、ハッシュ化してから保存
                $password = password_hash($student['password'], PASSWORD_DEFAULT);
                $stmt_student->bindValue(':password', $password, PDO::PARAM_STR);

                $stmt_student->bindValue(':created_at', $student['created_at'], PDO::PARAM_STR);
                $stmt_student->bindValue(':updated_at', $student['updated_at'], PDO::PARAM_STR);
                
                $stmt_student->execute();
            }


            //書籍状態テーブルに書籍データを挿入
            $stmt_book_status = $db->pdo->prepare("INSERT INTO book_status VALUES(:status_id, :status_name, :created_at, :updated_at)");

            foreach ($book_status_master as $b_status) {
                $stmt_book_status->bindValue(':status_id', $b_status['status_id'], PDO::PARAM_INT);
                $stmt_book_status->bindValue(':status_name', $b_status['status_name'], PDO::PARAM_STR);
                $stmt_book_status->bindValue(':created_at', $b_status['created_at'], PDO::PARAM_STR);
                $stmt_book_status->bindValue(':updated_at', $b_status['updated_at'], PDO::PARAM_STR);
                
                $stmt_book_status->execute();
            }

            //予約状態テーブルに予約状態データを挿入
            $stmt_reservation_status = $db->pdo->prepare("INSERT INTO reservation_status VALUES(:status_id, :status_name, :created_at, :updated_at)");

            foreach ($reservation_status_master as $r_status) {

                $stmt_reservation_status->bindValue(':status_id', $r_status['status_id'], PDO::PARAM_INT);
                $stmt_reservation_status->bindValue(':status_name', $r_status['status_name'], PDO::PARAM_STR);
                $stmt_reservation_status->bindValue(':created_at', $r_status['created_at'], PDO::PARAM_STR);
                $stmt_reservation_status->bindValue(':updated_at', $r_status['updated_at'], PDO::PARAM_STR);

                $stmt_reservation_status->execute();
            }

            //配送状態テーブルに配送状態データを挿入
            $stmt_delivery_status = $db->pdo->prepare("INSERT INTO delivery_status VALUES(:status_id, :status_name, :created_at, :updated_at)");

            foreach ($delivery_status_master as $d_status) {
                $stmt_delivery_status->bindValue(':status_id', $d_status['status_id'], PDO::PARAM_INT);
                $stmt_delivery_status->bindValue(':status_name', $d_status['status_name'], PDO::PARAM_STR);
                $stmt_delivery_status->bindValue(':created_at', $d_status['created_at'], PDO::PARAM_STR);
                $stmt_delivery_status->bindValue(':updated_at', $d_status['updated_at'], PDO::PARAM_STR);

                $stmt_delivery_status->execute();
            }

            //配送タイプテーブルに配送タイプデータを挿入
            $stmt_delivery_type = $db->pdo->prepare("INSERT INTO delivery_type VALUES(:type_id, :type_name, :created_at, :updated_at)");

            foreach ($delivery_type_master as $d_type) {
                $stmt_delivery_type->bindValue(':type_id', $d_type['type_id'], PDO::PARAM_INT);
                $stmt_delivery_type->bindValue(':type_name', $d_type['type_name'], PDO::PARAM_STR);
                $stmt_delivery_type->bindValue(':created_at', $d_type['created_at'], PDO::PARAM_STR);
                $stmt_delivery_type->bindValue(':updated_at', $d_type['updated_at'], PDO::PARAM_STR);

                $stmt_delivery_type->execute();
            }

            //書籍テーブルに書籍データを挿入
            $stmt_book_info = $db->pdo->prepare("INSERT INTO book_info VALUES (:isbn, :title, :author_name, :author_kana, :publisher, :publication_year, :registered_at, :updated_at)");
            
            //(:book_id, :school_id, :title, :author_name, :author_kana, :publisher, :publication_year, :status_id, :position);");

            foreach ($book_info as $b_i) {
                $stmt_book_info->bindValue(':isbn', $b_i['isbn'], PDO::PARAM_STR);
                $stmt_book_info->bindValue(':title', $b_i['title'], PDO::PARAM_STR);
                $stmt_book_info->bindValue(':author_name', $b_i['author_name'], PDO::PARAM_STR);
                $stmt_book_info->bindValue(':author_kana', $b_i['author_kana'], PDO::PARAM_STR);
                $stmt_book_info->bindValue(':publisher', $b_i['publisher'], PDO::PARAM_STR);
                $stmt_book_info->bindValue(':publication_year', $b_i['publication_year'], PDO::PARAM_STR);
                $stmt_book_info->bindValue(':registered_at', $b_i['registered_at'], PDO::PARAM_STR);
                $stmt_book_info->bindValue(':updated_at', $b_i['updated_at'], PDO::PARAM_STR);
                
                $stmt_book_info->execute();
            }


            // 書籍所蔵テーブルに書籍所蔵データを挿入
            $stmt_book_stack = $db->pdo->prepare("INSERT INTO book_stack VALUES (:book_id, :isbn, :school_id, :status_id, :position, :registered_at, :updated_at)");

            foreach ($book_stack as $b_s) {
                $stmt_book_stack->bindValue(':book_id', $b_s['stack_id'], PDO::PARAM_STR);
                $stmt_book_stack->bindValue(':isbn', $b_s['isbn'], PDO::PARAM_STR);
                $stmt_book_stack->bindValue(':school_id', $b_s['school_id'], PDO::PARAM_INT);
                $stmt_book_stack->bindValue(':status_id', $b_s['status_id'], PDO::PARAM_INT);
                $stmt_book_stack->bindValue(':position', $b_s['position'], PDO::PARAM_INT);
                $stmt_book_stack->bindValue(':registered_at', $b_s['registered_at'], PDO::PARAM_STR);
                $stmt_book_stack->bindValue(':updated_at', $b_s['updated_at'], PDO::PARAM_STR);
                
                $stmt_book_stack->execute();
            }


            //予約テーブルに予約履歴を挿入
            $stmt_reservation = $db->pdo->prepare("INSERT INTO reservation(reservation_number, student_id, book_id, status_id, reservation_date, updated_at) VALUES(:reservation_number, :student_id, :book_id, :status_id, :reservation_date, :updated_at)");

            foreach($reservation_list as $reservation) {
                $stmt_reservation->bindValue(':reservation_number', $reservation['reservation_number'], PDO::PARAM_STR);
                $stmt_reservation->bindValue(':student_id', $reservation['student_id'], PDO::PARAM_INT);
                $stmt_reservation->bindValue(':book_id', $reservation['book_id'], PDO::PARAM_STR);
                $stmt_reservation->bindValue(':status_id', $reservation['status_id'], PDO::PARAM_INT);
                $stmt_reservation->bindValue(':reservation_date', $reservation['reservation_date'], PDO::PARAM_STR);
                $stmt_reservation->bindValue(':updated_at', $reservation['updated_at'], PDO::PARAM_STR);

                $stmt_reservation->execute();
            }


            //貸出テーブルに貸出履歴を挿入
            $stmt_lending = $db->pdo->prepare("INSERT INTO lending(student_id, book_id, lending_date, return_date) VALUES(:student_id, :book_id, :lending_date, :return_date)");

            foreach($lending_list as $lending) {
                $stmt_lending->bindValue(':student_id', $lending['student_id'], PDO::PARAM_INT);
                $stmt_lending->bindValue(':book_id', $lending['book_id'], PDO::PARAM_STR);
                $stmt_lending->bindValue(':lending_date', $lending['lending_date'], PDO::PARAM_STR);
                $stmt_lending->bindValue(':return_date', $lending['return_date'], PDO::PARAM_STR);
                $stmt_lending->execute();
            }


            //司書テーブルに司書データを挿入
            $stmt_librarian = $db->pdo->prepare("INSERT INTO librarian (school_id, login_id, password, family_name, first_name, created_at, updated_at) VALUES (:school_id, :login_id, :password, :family_name, :first_name, :created_at, :updated_at)");

            foreach($librarian_master as $librarian) {
                $stmt_librarian->bindValue(':school_id', $librarian['school_id'], PDO::PARAM_INT);
                $stmt_librarian->bindValue(':login_id', $librarian['login_id'], PDO::PARAM_STR);

                //パスワードは、元の配列で平文として入れられているので、ハッシュ化してから保存
                $password = password_hash($librarian['password'], PASSWORD_DEFAULT);
                $stmt_librarian->bindValue(':password', $password, PDO::PARAM_STR);

                $stmt_librarian->bindValue(':family_name', $librarian['family_name'], PDO::PARAM_STR);
                $stmt_librarian->bindValue(':first_name', $librarian['first_name'], PDO::PARAM_STR);
                $stmt_librarian->bindValue(':created_at', $librarian['created_at'], PDO::PARAM_STR);
                $stmt_librarian->bindValue(':updated_at', $librarian['updated_at'], PDO::PARAM_STR);

                $stmt_librarian->execute();
            }


            $stmt_deliverer = $db->pdo->prepare("INSERT INTO deliverer (login_id, password, family_name, first_name, created_at, updated_at) VALUES (:login_id, :password, :family_name, :first_name, :created_at, :updated_at)");

            foreach($deliverer_master as $deliverer) {
                $stmt_deliverer->bindValue(':login_id', $deliverer['login_id'], PDO::PARAM_STR);

                //パスワードは、元の配列で平文として入れられているので、ハッシュ化してから保存
                $password = password_hash($deliverer['password'], PASSWORD_DEFAULT);
                $stmt_deliverer->bindValue(':password', $password, PDO::PARAM_STR);

                $stmt_deliverer->bindValue(':family_name', $deliverer['family_name'], PDO::PARAM_STR);
                $stmt_deliverer->bindValue(':first_name', $deliverer['first_name'], PDO::PARAM_STR);
                $stmt_deliverer->bindValue(':created_at', $deliverer['created_at'], PDO::PARAM_STR);
                $stmt_deliverer->bindValue(':updated_at', $deliverer['updated_at'], PDO::PARAM_STR);

                $stmt_deliverer->execute();
            }

            //配送テーブルに配送データを挿入
            $stmt_delivery = $db->pdo->prepare("INSERT INTO delivery(deliverer_id, from_school_id, to_school_id, delivery_type, delivery_status, book_id, delivery_date, arrival_date) VALUES(:deliverer_id, :from_school_id, :to_school_id, :delivery_type, :delivery_status, :book_id, :delivery_date, :arrival_date)");

            foreach($delivery_list as $delivery) {
                $stmt_delivery->bindValue(':deliverer_id', $delivery['deliverer_id'], PDO::PARAM_INT);
                $stmt_delivery->bindValue(':from_school_id', $delivery['from_school_id'], PDO::PARAM_INT);
                $stmt_delivery->bindValue(':to_school_id', $delivery['to_school_id'], PDO::PARAM_INT);
                $stmt_delivery->bindValue(':delivery_type', $delivery['delivery_type'], PDO::PARAM_INT);
                $stmt_delivery->bindValue(':delivery_status', $delivery['delivery_status'], PDO::PARAM_INT);
                $stmt_delivery->bindValue(':book_id', $delivery['book_id'], PDO::PARAM_STR);
                $stmt_delivery->bindValue(':delivery_date', $delivery['delivery_date'], PDO::PARAM_STR);
                $stmt_delivery->bindValue(':arrival_date', $delivery['arrival_date'], PDO::PARAM_STR);

                $stmt_delivery->execute();
            }

            $db->pdo->commit();
            
            $error_message = "テーブルの再作成が完了しました。";
            echo "<script>alert('" . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . "');</script>";

        } catch (PDOException $pe) {
            $db->pdo->rollBack();
            echo "<p>エラーが発生しました: " . htmlspecialchars($e->getMessage()) . "</p>";
        } catch (Exception $e) {
            $db->pdo->rollBack();
            echo "<p>エラーが発生しました: " . htmlspecialchars($e->getMessage()) . "</p>";
        } finally {
            $db->close();
        }
    }
?>

<html>
<head>
    <meta charset="UTF-8">
    <title>仮データ挿入</title>
</head>
<body>
    <h1>仮データ挿入ページ</h1>
    <h2>insert.phpを実行</h2>
    <p>注意：以下のボタンを押すと、すでに挿入されている仮データが削除され、新しい仮データが挿入される<br></p>
    <form method="post" onsubmit="return confirm('本当に仮データを再挿入しますか？ 今までのデータはすべて失われます。チームメンバーに確認取った？ダイジョブか？？');">
        <button type = "submit" name = "insert_tmpData">仮データを再度挿入</button>
    </form>
    <a href="create_table.php">テーブル作成ページへ移動</a>
</body>
</html>