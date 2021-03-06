<?php
// 履歴CSVファイル
$history_file   = 'history.csv';

// CSVレコードの並び順が一定になってないので日時でソート
$records = array();
if ($fp = fopen($history_file, "r")) {
    while (($row = fgetcsv($fp)) !== false) {
        $records[$row[0]] = $row;
    }
}
rsort($records);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>View</title>
<style>
table {
    width: 100%;
    border-collapse: collapse;
    border: solid 1px #222;
}
th {
    padding: 5px;
    background-color: #eee;
    border: 1px solid #222;
}
td {
    padding: 5px;
    border: 1px solid #222;
}
</style>
</head>
<body>
<p><a href="get.php">対局履歴を更新</a></p>
<table>
    <tr>
        <th>対局日時</th>
        <th>ユーザー名（先手）</th>
        <th>段級位（先手）</th>
        <th>勝敗（先手）</th>
        <th>ユーザー名（後手）</th>
        <th>段級位（後手）</th>
        <th>勝敗（後手）</th>
        <th>棋譜</th>
    </tr>
<?php foreach ($records as $row):?>
    <tr>
        <td><?php echo date('Y/m/d H:i:s', strtotime($row[0]));?></td>
        <td><?php echo $row[1];?></td>
        <td><?php echo $row[2];?></td>
        <td><?php echo $row[3];?></td>
        <td><?php echo $row[4];?></td>
        <td><?php echo $row[5];?></td>
        <td><?php echo $row[6];?></td>
        <td><a href="<?php echo $row[7];?>">見る</a></td>
    </tr>
<?php endforeach;?>
</table>
</body>
</html>
