<?php
    require_once 'db_connect.php';

    //〇学校マスタデータ（主キーはAUTO_INCREMENTではなくINT型なので、明示的に指定する）
    //もし配送中であった場合、positionは0とし、現在地は配送中として扱う
    $school_master = [
        0 => ['school_name' => '配送中', 'has_library' => false],
        1 => ['school_name' => '第一小学校', 'has_library' => true],
        2 => ['school_name' => '第二小学校', 'has_library' => true],
        3 => ['school_name' => '第三小学校', 'has_library' => true],
        4 => ['school_name' => '第四小学校', 'has_library' => true],
        5 => ['school_name' => '第五小学校', 'has_library' => true],
        6 => ['school_name' => '第六小学校', 'has_library' => true],
        7 => ['school_name' => '第七小学校', 'has_library' => false],
        8 => ['school_name' => '第八小学校', 'has_library' => false],
        9 => ['school_name' => '第九中学校', 'has_library' => true],
        10 => ['school_name' => '第十中学校', 'has_library' => true]
    ];


    //〇学生マスタデータ(主キーはAUTO_INCREMENTなので指定しない。パスワードは、この配列では開発用の平文で保持し、挿入時にハッシュ化する)
    $student_master = [
        ['school_id' => 1, 'grade' => 6, 'class' => 'A', 'number' => 1, 'family_name' => '山田', 'first_name' => '太郎', 'password' => '1234'],
        ['school_id' => 1, 'grade' => 5, 'class' => 'B', 'number' => 3, 'family_name' => '佐藤', 'first_name' => '花子', 'password' => 'abcd'],
        ['school_id' => 2, 'grade' => 6, 'class' => '1', 'number' => 10, 'family_name' => '鈴木', 'first_name' => '一郎', 'password' => 'pass123'],
        ['school_id' => 3, 'grade' => 4, 'class' => '2', 'number' => 15, 'family_name' => '田中', 'first_name' => '次郎', 'password' => 'xyz789'],
        ['school_id' => 4, 'grade' => 5, 'class' => '1', 'number' => 8, 'family_name' => '高橋', 'first_name' => '愛', 'password' => 'qwerty'],
        ['school_id' => 5, 'grade' => 3, 'class' => "1", 'number' => 20, 'family_name' => "伊藤", 'first_name' => "健太", 'password' => 'zxcvbn'],
        ['school_id' => 6, 'grade' => 6, 'class' => "2", 'number' => 5, 'family_name' => "渡辺", 'first_name' => "美咲", 'password' => 'asdfgh'],
        ['school_id' => 7, 'grade' => 6, 'class' => "1", 'number' => 12, 'family_name' => "山本", 'first_name' => "図書無", 'password' => '0000'],
        ['school_id' => 8, 'grade' => 5, 'class' => "1", 'number' => 7, 'family_name' => "中村", 'first_name' => "遠方", 'password' => '1111'],
        ['school_id' => 9, 'grade' => 2, 'class' => "A", 'number' => 1, 'family_name' => "小林", 'first_name' => "中学", 'password' => 'password'],
        ['school_id' => 10, 'grade' => 1, 'class' => "1", 'number' => 30, 'family_name' => "吉田", 'first_name' => "新一", 'password' => '45gds'],
        ['school_id' => 9, 'grade' => 3, 'class' => "B", 'number' => 22, 'family_name' => "加藤", 'first_name' => "学", 'password' => 'lmno56'],
        ['school_id' => 10, 'grade' => 3, 'class' => "2", 'number' => 14, 'family_name' => "佐々木", 'first_name' => "受験", 'password' => 'abc123'],
        ['school_id' => 1, 'grade' => 6, 'class' => "A", 'number' => 2, 'family_name' => "松本", 'first_name' => "潤", 'password' => 'def456'],
        ['school_id' => 9, 'grade' => 2, 'class' => "A", 'number' => 2, 'family_name' => "井上", 'first_name' => "真央", 'password' => 'ghi789']
    ];


    //〇本の状態マスタデータ（主キーはAUTO_INCREMENTではなくINT型なので、明示的に指定する）
    $status_master = [
        1 => '貸出可能',
        2 => '貸出中',
        3 => '予約済み',
        4 => '予約受取待ち',
        5 => '配送待ち（往路）',
        6 => '配送中（往路）',
        7 => '配送予約受取待ち',
        8 => '配送中（復路）',
        9 => '配送待ち（復路）',
        10 => '貸出不可',
        11 => '紛失',
        12 => '修繕中',
        13 => '除籍'
    ];

    //予約状態マスタデータ（主キーはAUTO_INCREMENTではなくINT型なので、明示的に指定する）
    $reservation_status_master = [
        1 => '予約完了',
        2 => 'キャンセル済み',
        3 => '受取済み',
    ];

    // 配送状態マスタデータ（主キーはAUTO_INCREMENTではなくINT型なので、明示的に指定する）
    $delivery_status_master = [
        1 => '配送待ち',
        2 => '配送中',
        3 => '配送完了',
    ];

    // 配送タイプマスタデータ（主キーはAUTO_INCREMENTではなくINT型なので、明示的に指定する）
    $delivery_type_master = [
        1 => '貸出配送（往路）',
        2 => '返却配送（復路）'
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
    

    // 書籍マスタ
    // 貸出・予約状況に合わせて status_id と position を調整
    // 基本: status_id=1(可), position=所有校
    // 貸出中: status_id=2(貸出中), position=借出校
    // 配送中: status_id=6(配送中), position=0
    // 受取待: status_id=4(受取待), position=受取校(自校本なら自校、他校本なら到着済みで自校)
    $book_master = [
        // 1. こころ (Sch3, Sch9)
        // 913000101: 第八小(Sch8)へ貸出中 -> Pos:8, Status:2
        ['book_id' => '913000101', 'school_id' => 3, 'title' => 'こころ', 'author_name' => '夏目漱石', 'author_kana' => 'ナツメソウセキ', 'publisher' => '岩波書店', 'publication_year' => '1990-01-01', 'status_id' => 2, 'position' => 8],
        ['book_id' => '913000102', 'school_id' => 9, 'title' => 'こころ', 'author_name' => '夏目漱石', 'author_kana' => 'ナツメソウセキ', 'publisher' => '新潮社', 'publication_year' => '1995-05-01', 'status_id' => 1, 'position' => 9],
        
        // 2. 人間失格 (Sch8(図書室なし)->Sch2で管理と仮定, Sch?) -> Sch2とSch4に配置
        ['book_id' => '913000201', 'school_id' => 2, 'title' => '人間失格', 'author_name' => '太宰治', 'author_kana' => 'ダザイオサム', 'publisher' => '新潮社', 'publication_year' => '1992-04-01', 'status_id' => 1, 'position' => 2],
        ['book_id' => '913000202', 'school_id' => 4, 'title' => '人間失格', 'author_name' => '太宰治', 'author_kana' => 'ダザイオサム', 'publisher' => '角川書店', 'publication_year' => '2000-12-01', 'status_id' => 1, 'position' => 4],

        // 3. 羅生門 (Sch5, Sch7->Sch6と仮定)
        // 913000301: 自校(Sch5)貸出中 -> Pos:5, Status:2
        ['book_id' => '913000301', 'school_id' => 5, 'title' => '羅生門', 'author_name' => '芥川龍之介', 'author_kana' => 'アクタガワリュウノスケ', 'publisher' => '岩波書店', 'publication_year' => '1998-03-01', 'status_id' => 2, 'position' => 5],
        ['book_id' => '913000302', 'school_id' => 6, 'title' => '羅生門', 'author_name' => '芥川龍之介', 'author_kana' => 'アクタガワリュウノスケ', 'publisher' => '新潮社', 'publication_year' => '2005-08-01', 'status_id' => 1, 'position' => 6],

        // 4. 銀河鉄道の夜 (Sch9, Sch10)
        // 913000401: 返却済み -> Pos:9, Status:1
        // 913000402: 他校(Sch9の生徒)へ貸出中 -> Pos:9, Status:2
        ['book_id' => '913000401', 'school_id' => 9, 'title' => '銀河鉄道の夜', 'author_name' => '宮沢賢治', 'author_kana' => 'ミヤザワケンジ', 'publisher' => '角川書店', 'publication_year' => '1996-07-07', 'status_id' => 1, 'position' => 9],
        ['book_id' => '913000402', 'school_id' => 10, 'title' => '銀河鉄道の夜', 'author_name' => '宮沢賢治', 'author_kana' => 'ミヤザワケンジ', 'publisher' => '新潮社', 'publication_year' => '2001-11-01', 'status_id' => 2, 'position' => 9],

        // 5. 坊っちゃん (Sch1, Sch6)
        ['book_id' => '913000501', 'school_id' => 1, 'title' => '坊っちゃん', 'author_name' => '夏目漱石', 'author_kana' => 'ナツメソウセキ', 'publisher' => '新潮社', 'publication_year' => '1989-02-01', 'status_id' => 1, 'position' => 1],
        ['book_id' => '913000502', 'school_id' => 6, 'title' => '坊っちゃん', 'author_name' => '夏目漱石', 'author_kana' => 'ナツメソウセキ', 'publisher' => '岩波書店', 'publication_year' => '2010-04-01', 'status_id' => 1, 'position' => 6],

        // 6. 走れメロス (Sch4, Sch?)
        ['book_id' => '913000601', 'school_id' => 4, 'title' => '走れメロス', 'author_name' => '太宰治', 'author_kana' => 'ダザイオサム', 'publisher' => '新潮社', 'publication_year' => '1993-05-15', 'status_id' => 1, 'position' => 4],
        
        // 7. 雪国 (Sch2, Sch8->Sch3と仮定)
        // 913000701: ★配送中(予約No.9) -> Pos:0, Status:6
        ['book_id' => '913000701', 'school_id' => 2, 'title' => '雪国', 'author_name' => '川端康成', 'author_kana' => 'カワバタヤスナリ', 'publisher' => '新潮社', 'publication_year' => '1960-01-01', 'status_id' => 6, 'position' => 0],
        ['book_id' => '913000702', 'school_id' => 3, 'title' => '雪国', 'author_name' => '川端康成', 'author_kana' => 'カワバタヤスナリ', 'publisher' => '岩波書店', 'publication_year' => '1980-01-01', 'status_id' => 1, 'position' => 3],

        // 8. 伊豆の踊子 (Sch7->Sch1と仮定, Sch9)
        // 913000802: 自校(Sch9)貸出中 -> Pos:9, Status:2
        ['book_id' => '913000801', 'school_id' => 1, 'title' => '伊豆の踊子', 'author_name' => '川端康成', 'author_kana' => 'カワバタヤスナリ', 'publisher' => '新潮社', 'publication_year' => '1991-03-01', 'status_id' => 1, 'position' => 1],
        ['book_id' => '913000802', 'school_id' => 9, 'title' => '伊豆の踊子', 'author_name' => '川端康成', 'author_kana' => 'カワバタヤスナリ', 'publisher' => '角川書店', 'publication_year' => '2005-06-01', 'status_id' => 2, 'position' => 9],

        // 9. 金閣寺 (Sch10)
        ['book_id' => '913000901', 'school_id' => 10, 'title' => '金閣寺', 'author_name' => '三島由紀夫', 'author_kana' => 'ミシマユキオ', 'publisher' => '新潮社', 'publication_year' => '1970-01-01', 'status_id' => 1, 'position' => 10],

        // 10. 吾輩は猫である (Sch3, Sch5)
        ['book_id' => '913001001', 'school_id' => 3, 'title' => '吾輩は猫である', 'author_name' => '夏目漱石', 'author_kana' => 'ナツメソウセキ', 'publisher' => '岩波書店', 'publication_year' => '1988-10-01', 'status_id' => 1, 'position' => 3],
        ['book_id' => '913001002', 'school_id' => 5, 'title' => '吾輩は猫である', 'author_name' => '夏目漱石', 'author_kana' => 'ナツメソウセキ', 'publisher' => '講談社', 'publication_year' => '2000-01-01', 'status_id' => 1, 'position' => 5],

        // 11. 風の又三郎 (Sch1)
        // 913001101: ★配送中(予約No.8) -> Pos:0, Status:6
        ['book_id' => '913001101', 'school_id' => 1, 'title' => '風の又三郎', 'author_name' => '宮沢賢治', 'author_kana' => 'ミヤザワケンジ', 'publisher' => '岩波書店', 'publication_year' => '1995-11-01', 'status_id' => 6, 'position' => 0],

        // 12. 蜘蛛の糸 (Sch6, Sch8->Sch4と仮定)
        ['book_id' => '913001201', 'school_id' => 6, 'title' => '蜘蛛の糸', 'author_name' => '芥川龍之介', 'author_kana' => 'アクタガワリュウノスケ', 'publisher' => '新潮社', 'publication_year' => '1999-01-01', 'status_id' => 1, 'position' => 6],
        ['book_id' => '913001202', 'school_id' => 4, 'title' => '蜘蛛の糸', 'author_name' => '芥川龍之介', 'author_kana' => 'アクタガワリュウノスケ', 'publisher' => '角川書店', 'publication_year' => '2008-01-01', 'status_id' => 1, 'position' => 4],

        // 13. 斜陽 (Sch2, Sch4)
        ['book_id' => '913001301', 'school_id' => 2, 'title' => '斜陽', 'author_name' => '太宰治', 'author_kana' => 'ダザイオサム', 'publisher' => '新潮社', 'publication_year' => '1980-05-01', 'status_id' => 1, 'position' => 2],
        ['book_id' => '913001302', 'school_id' => 4, 'title' => '斜陽', 'author_name' => '太宰治', 'author_kana' => 'ダザイオサム', 'publisher' => '岩波書店', 'publication_year' => '1990-06-01', 'status_id' => 1, 'position' => 4],

        // 14. 細雪 (Sch9)
        ['book_id' => '913001401', 'school_id' => 9, 'title' => '細雪', 'author_name' => '谷崎潤一郎', 'author_kana' => 'タニザキジュンイチロウ', 'publisher' => '中公文庫', 'publication_year' => '1985-01-01', 'status_id' => 1, 'position' => 9],

        // 15. 山月記 (Sch5, Sch10)
        ['book_id' => '913001501', 'school_id' => 5, 'title' => '山月記', 'author_name' => '中島敦', 'author_kana' => 'ナカジマアツシ', 'publisher' => '岩波書店', 'publication_year' => '1994-02-01', 'status_id' => 1, 'position' => 5],
        ['book_id' => '913001502', 'school_id' => 10, 'title' => '山月記', 'author_name' => '中島敦', 'author_kana' => 'ナカジマアツシ', 'publisher' => '新潮社', 'publication_year' => '2003-07-01', 'status_id' => 1, 'position' => 10],

        // 16. 高瀬舟 (Sch3, Sch7->Sch1と仮定)
        // 913001602: 自校(Sch1)貸出中 -> Pos:1, Status:2
        ['book_id' => '913001601', 'school_id' => 3, 'title' => '高瀬舟', 'author_name' => '森鴎外', 'author_kana' => 'モリオウガイ', 'publisher' => '岩波書店', 'publication_year' => '1982-01-01', 'status_id' => 1, 'position' => 3],
        ['book_id' => '913001602', 'school_id' => 1, 'title' => '高瀬舟', 'author_name' => '森鴎外', 'author_kana' => 'モリオウガイ', 'publisher' => '新潮社', 'publication_year' => '1995-10-01', 'status_id' => 2, 'position' => 1],

        // 17. 舞姫 (Sch1, Sch8->Sch5と仮定)
        ['book_id' => '913001701', 'school_id' => 1, 'title' => '舞姫', 'author_name' => '森鴎外', 'author_kana' => 'モリオウガイ', 'publisher' => '岩波書店', 'publication_year' => '1988-04-01', 'status_id' => 1, 'position' => 1],
        ['book_id' => '913001702', 'school_id' => 5, 'title' => '舞姫', 'author_name' => '森鴎外', 'author_kana' => 'モリオウガイ', 'publisher' => '新潮社', 'publication_year' => '2001-01-01', 'status_id' => 1, 'position' => 5],

        // 18. 夜明け前 (Sch4, Sch6)
        // 913001802: 他校(Sch1)へ貸出中 -> Pos:1, Status:2
        ['book_id' => '913001801', 'school_id' => 4, 'title' => '夜明け前', 'author_name' => '島崎藤村', 'author_kana' => 'シマザキトウソン', 'publisher' => '岩波書店', 'publication_year' => '1975-01-01', 'status_id' => 1, 'position' => 4],
        ['book_id' => '913001802', 'school_id' => 6, 'title' => '夜明け前', 'author_name' => '島崎藤村', 'author_kana' => 'シマザキトウソン', 'publisher' => '新潮社', 'publication_year' => '1990-01-01', 'status_id' => 2, 'position' => 1],

        // --- 少し飛んで特徴的なデータ ---

        // 学問のすゝめ (No.22)
        // 913001902: 自校(Sch1)貸出中 -> Pos:1, Status:2
        ['book_id' => '913001901', 'school_id' => 3, 'title' => '学問のすゝめ', 'author_name' => '福沢諭吉', 'author_kana' => 'フクザワユキチ', 'publisher' => '岩波書店', 'publication_year' => '1980-01-01', 'status_id' => 1, 'position' => 3],
        ['book_id' => '913001902', 'school_id' => 1, 'title' => '学問のすゝめ', 'author_name' => '福沢諭吉', 'author_kana' => 'フクザワユキチ', 'publisher' => '講談社', 'publication_year' => '2005-01-01', 'status_id' => 2, 'position' => 1],

        // 武士道 (No.23)
        ['book_id' => '913002001', 'school_id' => 1, 'title' => '武士道', 'author_name' => '新渡戸稲造', 'author_kana' => 'ニトベイナゾウ', 'publisher' => '岩波書店', 'publication_year' => '1990-01-01', 'status_id' => 1, 'position' => 1],
        ['book_id' => '913002002', 'school_id' => 2, 'title' => '武士道', 'author_name' => '新渡戸稲造', 'author_kana' => 'ニトベイナゾウ', 'publisher' => '講談社', 'publication_year' => '2000-01-01', 'status_id' => 1, 'position' => 2],

        // 東海道中膝栗毛 (No.29)
        ['book_id' => '913002301', 'school_id' => 1, 'title' => '東海道中膝栗毛', 'author_name' => '十返舎一九', 'author_kana' => 'ジッペンシャイック', 'publisher' => '岩波書店', 'publication_year' => '1985-01-01', 'status_id' => 1, 'position' => 1],
        ['book_id' => '913002302', 'school_id' => 6, 'title' => '東海道中膝栗毛', 'author_name' => '十返舎一九', 'author_kana' => 'ジッペンシャイック', 'publisher' => '新潮社', 'publication_year' => '1995-01-01', 'status_id' => 1, 'position' => 6],
        
        // コンビニ人間 (No.158)
        // 913003002: 自校(Sch10)貸出中 -> Pos:10, Status:2
        ['book_id' => '913003001', 'school_id' => 10, 'title' => 'コンビニ人間', 'author_name' => '村田沙耶香', 'author_kana' => 'ムラタサヤカ', 'publisher' => '文藝春秋', 'publication_year' => '2016-07-01', 'status_id' => 1, 'position' => 10],
        ['book_id' => '913003002', 'school_id' => 10, 'title' => 'コンビニ人間', 'author_name' => '村田沙耶香', 'author_kana' => 'ムラタサヤカ', 'publisher' => '文藝春秋', 'publication_year' => '2016-07-01', 'status_id' => 2, 'position' => 10],

        // 1Q84 (No.?)
        // 913002601: 受取待ち -> Pos:1, Status:4
        ['book_id' => '913002601', 'school_id' => 1, 'title' => '1Q84', 'author_name' => '村上春樹', 'author_kana' => 'ムラカミハルキ', 'publisher' => '新潮社', 'publication_year' => '2009-05-01', 'status_id' => 4, 'position' => 1],

        // ハリー・ポッター (Foreign)
        // 933000201: 返却済み -> Pos:1, Status:1
        // 933000202: 予約あり貸出待ち(Sch6にある) -> Pos:6, Status:1
        ['book_id' => '933000201', 'school_id' => 1, 'title' => 'ハリー・ポッターと賢者の石', 'author_name' => 'J.K.ローリング', 'author_kana' => 'ローリング', 'publisher' => '静山社', 'publication_year' => '1999-12-01', 'status_id' => 1, 'position' => 1],
        ['book_id' => '933000202', 'school_id' => 6, 'title' => 'ハリー・ポッターと賢者の石', 'author_name' => 'J.K.ローリング', 'author_kana' => 'ローリング', 'publisher' => '静山社', 'publication_year' => '1999-12-01', 'status_id' => 1, 'position' => 6],

        // 星の王子さま (Foreign)
        // 953000102: 自校(Sch2)貸出中 -> Pos:2, Status:2
        ['book_id' => '953000101', 'school_id' => 2, 'title' => '星の王子さま', 'author_name' => 'サン=テグジュペリ', 'author_kana' => 'サンテグジュペリ', 'publisher' => '岩波書店', 'publication_year' => '2000-01-01', 'status_id' => 1, 'position' => 2],
        ['book_id' => '953000102', 'school_id' => 2, 'title' => '星の王子さま', 'author_name' => 'サン=テグジュペリ', 'author_kana' => 'サンテグジュペリ', 'publisher' => '新潮社', 'publication_year' => '2006-01-01', 'status_id' => 2, 'position' => 2],
        
        // 変身 (Foreign)
        // 943000102: 他校(Sch9)で延滞中 -> Pos:9, Status:2
        ['book_id' => '943000101', 'school_id' => 9, 'title' => '変身', 'author_name' => 'カフカ', 'author_kana' => 'カフカ', 'publisher' => '新潮社', 'publication_year' => '1952-01-01', 'status_id' => 1, 'position' => 9],
        ['book_id' => '943000102', 'school_id' => 2, 'title' => '変身', 'author_name' => 'カフカ', 'author_kana' => 'カフカ', 'publisher' => '岩波書店', 'publication_year' => '1960-01-01', 'status_id' => 2, 'position' => 9],

        // 老人と海
        // 933000102: 受取待ち(Sch10にある) -> Pos:10, Status:4
        ['book_id' => '933000102', 'school_id' => 9, 'title' => '老人と海', 'author_name' => 'ヘミングウェイ', 'author_kana' => 'ヘミングウェイ', 'publisher' => '新潮社', 'publication_year' => '1966-01-01', 'status_id' => 4, 'position' => 10],

        // 赤毛のアン
        ['book_id' => '933000301', 'school_id' => 3, 'title' => '赤毛のアン', 'author_name' => 'モンゴメリ', 'author_kana' => 'モンゴメリ', 'publisher' => '新潮社', 'publication_year' => '1955-01-01', 'status_id' => 1, 'position' => 3],
    ];

    // 配送データリスト
    // delivery_id は AUTO_INCREMENT のため省略
    // delivery_status: 1=配送待ち, 2=配送中, 3=配送完了
    // delivery_type:   1=貸出配送(往路), 2=返却配送(復路)

    $delivery_list = [
        // --- 【過去の履歴】貸出終了して戻ってきた本 ---
        
        // 1. ハリーポッター（Sch1所有）: 第一小(A) -> 第七小(B)へ貸出
        ['from_school_id' => 1, 'to_school_id' => 7, 'delivery_type' => 1, 'delivery_status' => 3, 'book_id' => '933000201', 'delivery_date' => '2024-04-28 10:00:00', 'arrival_date' => '2024-04-28 15:00:00'],
        
        // 2. ハリーポッター（Sch1所有）: 第七小(B) -> 第一小(A)へ返却
        ['from_school_id' => 7, 'to_school_id' => 1, 'delivery_type' => 2, 'delivery_status' => 3, 'book_id' => '933000201', 'delivery_date' => '2024-05-08 16:00:00', 'arrival_date' => '2024-05-09 09:00:00'],


        // --- 【過去の履歴】A→B→C→A の又貸しリレー（ご要望のケース） ---
        // 対象本: 蜘蛛の糸 (ID:913001202, 所有:第二小)
        
        // 3. 第二小(A) -> 第四小(B): 貸出配送（往路）
        ['from_school_id' => 2, 'to_school_id' => 4, 'delivery_type' => 1, 'delivery_status' => 3, 'book_id' => '913001202', 'delivery_date' => '2024-05-01 10:00:00', 'arrival_date' => '2024-05-01 14:00:00'],
        
        // 4. 第四小(B) -> 第六小(C): 貸出配送（往路） ※Bで返却されず、そのままCの予約に回った想定
        ['from_school_id' => 4, 'to_school_id' => 6, 'delivery_type' => 1, 'delivery_status' => 3, 'book_id' => '913001202', 'delivery_date' => '2024-05-14 11:00:00', 'arrival_date' => '2024-05-14 15:00:00'],
        
        // 5. 第六小(C) -> 第二小(A): 返却配送（復路） ※所有校に戻る
        ['from_school_id' => 6, 'to_school_id' => 2, 'delivery_type' => 2, 'delivery_status' => 3, 'book_id' => '913001202', 'delivery_date' => '2024-05-22 15:00:00', 'arrival_date' => '2024-05-23 10:00:00'],


        // --- 【現在貸出中】他校にある本（配送は完了している） ---

        // 6. こころ（Sch3所有）: 第三小 -> 第八小へ配送済み
        ['from_school_id' => 3, 'to_school_id' => 8, 'delivery_type' => 1, 'delivery_status' => 3, 'book_id' => '913000101', 'delivery_date' => '2024-06-01 09:00:00', 'arrival_date' => '2024-06-01 13:00:00'],

        // 7. 銀河鉄道の夜（Sch10所有）: 第十中 -> 第九中へ配送済み
        ['from_school_id' => 10, 'to_school_id' => 9, 'delivery_type' => 1, 'delivery_status' => 3, 'book_id' => '913000402', 'delivery_date' => '2024-06-05 09:00:00', 'arrival_date' => '2024-06-05 11:00:00'],

        // 8. 夜明け前（Sch4所有）: 第四小 -> 第一小へ配送済み
        ['from_school_id' => 4, 'to_school_id' => 1, 'delivery_type' => 1, 'delivery_status' => 3, 'book_id' => '913001802', 'delivery_date' => '2024-06-05 14:00:00', 'arrival_date' => '2024-06-06 09:00:00'],

        // 9. 変身（Sch2所有）: 第二小 -> 第九中へ配送済み（延滞中）
        ['from_school_id' => 2, 'to_school_id' => 9, 'delivery_type' => 1, 'delivery_status' => 3, 'book_id' => '943000102', 'delivery_date' => '2024-05-19 10:00:00', 'arrival_date' => '2024-05-19 14:00:00'],


        // --- 【現在進行中】配送中・受取待ちの本 ---

        // 10. ★配送中: 風の又三郎（Sch1所有）: 第一小 -> 第七小へ移動中
        // book_master の position が 0 になっているデータ
        ['from_school_id' => 1, 'to_school_id' => 7, 'delivery_type' => 1, 'delivery_status' => 2, 'book_id' => '913001101', 'delivery_date' => '2024-06-08 08:30:00', 'arrival_date' => null],

        // 11. ★配送中: 雪国（Sch2所有）: 第二小 -> 第八小へ移動中
        // book_master の position が 0 になっているデータ
        ['from_school_id' => 2, 'to_school_id' => 8, 'delivery_type' => 1, 'delivery_status' => 2, 'book_id' => '913000701', 'delivery_date' => '2024-06-07 14:00:00', 'arrival_date' => null],

        // 12. 受取待ち: 老人と海（Sch9所有）: 第九中 -> 第十中へ到着済み（カウンター保管中）
        // reservation_list で status_id が 1(予約完了) だが、現物は届いている状態
        ['from_school_id' => 9, 'to_school_id' => 10, 'delivery_type' => 1, 'delivery_status' => 3, 'book_id' => '933000102', 'delivery_date' => '2024-06-03 11:00:00', 'arrival_date' => '2024-06-03 15:00:00'],


        // --- 【未来の配送予定】配送待ち ---

        // 13. 配送待ち: 武士道（Sch1所有）: 第一小 -> 第二小へ配送準備中
        // 予約が入ったばかりで、まだ学校を出発していない
        ['from_school_id' => 1, 'to_school_id' => 2, 'delivery_type' => 1, 'delivery_status' => 1, 'book_id' => '913002002', 'delivery_date' => '2024-06-08 10:00:00', 'arrival_date' => null]
    ];


    $librarian_master = [
        // 第一小学校の司書
        ['school_id' => 1, 'login_id' => 'lib01', 'password' => 'pass1234', 'family_name' => '図書', 'first_name' => '管理郎'],
        // 第二小学校の司書
        ['school_id' => 2, 'login_id' => 'sato_sensei', 'password' => 'abcd5678', 'family_name' => '佐藤', 'first_name' => '司書郎'],
        // 同じ学校に2人いるケース（第一小学校）
        ['school_id' => 1, 'login_id' => 'lib02', 'password' => 'pass5678', 'family_name' => '鈴木', 'first_name' => '根郎'],
    ];


    if (isset($_POST['insert_tmpData'])) {
        $db = new db_connect();
        $db->connect();

        $tables = ['delivery', 'reservation', 'lending', 'book', 'book_status', 'reservation_status', 'delivery_status', 'delivery_type', 'student', 'school', 'librarian'];

        try {
            $db->pdo->beginTransaction();

            //各テーブルのデータを削除
            foreach ($tables as $table) {
                $db->pdo->exec("DELETE FROM {$table}");
            }

            //学校テーブルに学校データを挿入
            $stmt_school = $db->pdo->prepare("INSERT INTO school VALUES (:school_id, :school_name, :has_library)");

            foreach ($school_master as $key => $values) {
                $stmt_school->bindValue(':school_id', $key, PDO::PARAM_INT);
                
                $stmt_school->bindValue(':school_name', $values['school_name'], PDO::PARAM_STR);
                $stmt_school->bindValue(':has_library', $values['has_library'], PDO::PARAM_BOOL);
                
                $stmt_school->execute();
            }

            //学生テーブルに学生データを挿入
            $stmt_student = $db->pdo->prepare("INSERT INTO student (school_id, grade, class, number, family_name, first_name, password) VALUES (:school_id, :grade, :class, :number, :family_name, :first_name, :password)");

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
                
                $stmt_student->execute();
            }


            //書籍状態テーブルに書籍データを挿入
            $stmt_status = $db->pdo->prepare("INSERT INTO book_status VALUES(:status_id, :status_name)");

            foreach ($status_master as $key => $name) {
                $stmt_status->bindValue(':status_id', $key, PDO::PARAM_INT);
                $stmt_status->bindValue(':status_name', $name, PDO::PARAM_STR);
                
                $stmt_status->execute();
            }

            //予約状態テーブルに予約状態データを挿入
            $stmt_reservation_status = $db->pdo->prepare("INSERT INTO reservation_status VALUES(:status_id, :status_name)");

            foreach ($reservation_status_master as $key => $name) {
                $stmt_reservation_status->bindValue(':status_id', $key, PDO::PARAM_INT);
                $stmt_reservation_status->bindValue(':status_name', $name, PDO::PARAM_STR);

                $stmt_reservation_status->execute();
            }

            //配送状態テーブルに配送状態データを挿入
            $stmt_delivery_status = $db->pdo->prepare("INSERT INTO delivery_status VALUES(:status_id, :status_name)");

            foreach ($delivery_status_master as $key => $name) {
                $stmt_delivery_status->bindValue(':status_id', $key, PDO::PARAM_INT);
                $stmt_delivery_status->bindValue(':status_name', $name, PDO::PARAM_STR);

                $stmt_delivery_status->execute();
            }

            //配送タイプテーブルに配送タイプデータを挿入
            $stmt_delivery_type = $db->pdo->prepare("INSERT INTO delivery_type VALUES(:type_id, :type_name)");

            foreach ($delivery_type_master as $key => $name) {
                $stmt_delivery_type->bindValue(':type_id', $key, PDO::PARAM_INT);
                $stmt_delivery_type->bindValue(':type_name', $name, PDO::PARAM_STR);

                $stmt_delivery_type->execute();
            }

            //書籍テーブルに書籍データを挿入
            $stmt_book = $db->pdo->prepare("INSERT INTO book VALUES (:book_id, :school_id, :title, :author_name, :author_kana, :publisher, :publication_year, :status_id, :position);");

            foreach ($book_master as $book) {
                $stmt_book->bindValue(':book_id', $book['book_id'], PDO::PARAM_STR);
                $stmt_book->bindValue(':school_id', $book['school_id'], PDO::PARAM_INT);
                $stmt_book->bindValue(':title', $book['title'], PDO::PARAM_STR);
                $stmt_book->bindValue(':author_name', $book['author_name'], PDO::PARAM_STR);
                $stmt_book->bindValue(':author_kana', $book['author_kana'], PDO::PARAM_STR);
                $stmt_book->bindValue(':publisher', $book['publisher'], PDO::PARAM_STR);
                $stmt_book->bindValue(':publication_year', $book['publication_year'], PDO::PARAM_STR);
                $stmt_book->bindValue(':status_id', $book['status_id'], PDO::PARAM_STR);
                $stmt_book->bindValue(':position', $book['position'], PDO::PARAM_INT);
                
                $stmt_book->execute();
            }


            //予約テーブルに予約履歴を挿入
            $stmt_reservation = $db->pdo->prepare("INSERT INTO reservation(student_id, book_id, status_id, reservation_date) VALUES(:student_id, :book_id, :status_id, :reservation_date)");

            foreach($reservation_list as $reservation) {
                $stmt_reservation->bindValue(':student_id', $reservation['student_id'], PDO::PARAM_INT);
                $stmt_reservation->bindValue(':book_id', $reservation['book_id'], PDO::PARAM_STR);
                $stmt_reservation->bindValue(':status_id', $reservation['status_id'], PDO::PARAM_INT);
                $stmt_reservation->bindValue(':reservation_date', $reservation['reservation_date'], PDO::PARAM_STR);

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

            //配送テーブルに配送データを挿入
            $stmt_delivery = $db->pdo->prepare("INSERT INTO delivery(from_school_id, to_school_id, delivery_type, delivery_status, book_id, delivery_date, arrival_date) VALUES(:from_school_id, :to_school_id, :delivery_type, :delivery_status, :book_id, :delivery_date, :arrival_date)");

            foreach($delivery_list as $delivery) {
                $stmt_delivery->bindValue(':from_school_id', $delivery['from_school_id'], PDO::PARAM_INT);
                $stmt_delivery->bindValue(':to_school_id', $delivery['to_school_id'], PDO::PARAM_INT);
                $stmt_delivery->bindValue(':delivery_type', $delivery['delivery_type'], PDO::PARAM_INT);
                $stmt_delivery->bindValue(':delivery_status', $delivery['delivery_status'], PDO::PARAM_INT);
                $stmt_delivery->bindValue(':book_id', $delivery['book_id'], PDO::PARAM_STR);
                $stmt_delivery->bindValue(':delivery_date', $delivery['delivery_date'], PDO::PARAM_STR);
                $stmt_delivery->bindValue(':arrival_date', $delivery['arrival_date'], PDO::PARAM_STR);

                $stmt_delivery->execute();
            }

            //司書テーブルに司書データを挿入
            $stmt_librarian = $db->pdo->prepare("INSERT INTO librarian (school_id, login_id, password, family_name, first_name) VALUES (:school_id, :login_id, :password, :family_name, :first_name)");

            foreach($librarian_master as $librarian) {
                $stmt_librarian->bindValue(':school_id', $librarian['school_id'], PDO::PARAM_INT);
                $stmt_librarian->bindValue(':login_id', $librarian['login_id'], PDO::PARAM_STR);

                //パスワードは、元の配列で平文として入れられているので、ハッシュ化してから保存
                $password = password_hash($librarian['password'], PASSWORD_DEFAULT);
                $stmt_librarian->bindValue(':password', $password, PDO::PARAM_STR);

                $stmt_librarian->bindValue(':family_name', $librarian['family_name'], PDO::PARAM_STR);
                $stmt_librarian->bindValue(':first_name', $librarian['first_name'], PDO::PARAM_STR);

                $stmt_librarian->execute();
            }

            $db->pdo->commit();
            
            $error_message = "テーブルの再作成が完了しました。";
            echo "<script>alert('" . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . "');</script>";

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