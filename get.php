<?php
require_once 'goutte.phar';

use Goutte\Client;

// ユーザー名
$user = 'ko31';

// 将棋ウォーズの履歴URL
$history_url    = 'http://shogiwars.heroz.jp/users/history/' . $user . '?gtype=&start=';

// 履歴CSVファイル
$history_file   = 'history.csv';

// 最終対局日時データ
$lasttime_file  = 'lasttime.dat';

// スクレイピングする最大ページ
$max_page = 10;

$client = new Client();

$records = array();
$newest_time = 0;

for ($i=0; $i<$max_page; $i++) {
    
    $url = $history_url . ($i*10);
    echo "Get " . $url . "\n";
    
    $crawler = $client->request('GET', $url);
    
    $dom = $crawler->filter('div.contents');
    if (!count($dom)) {
        // 履歴データが見つからなくなったらおしまい
        break;
    }
    
    // 処理済みの最終対局日時を取得
    if (file_exists($lasttime_file)) {
        $last_time = trim(file_get_contents($lasttime_file));
    } else {
        $last_time = date('YmdHis');
    }
    
    $dom->each(function ($node) use (&$records, &$newest_time, $last_time) {
    
        // パーマリンク取得
        $permalink = '';
        $dom_link = $node->filter('div.short_btn1 a');
        $dom_link->each(function ($node_link) use (&$permalink) {
            $permalink = $node_link->attr('href');
        });
        $query_pos = strpos($permalink, '?');
        if ($query_pos === false) {
            $ending_time = str_replace('_', '', substr($permalink, -15));
        } else {
            $pos = -15 - (strlen($permalink) - $query_pos);
            $ending_time = str_replace('_', '', substr($permalink, $pos, 15));
        }
        if ($ending_time > $newest_time) {
            $newest_time = $ending_time;
        }
    
        // ユーザー取得
        $users = array();
        $dom_user = $node->filter('td');
        $dom_user->each(function ($node_user) use (&$users) {
            $_tmp = $node_user->text();
            if ($_tmp) {
                $_users = explode(' ', $_tmp);
                $users[] = $_users[0];  // ユーザー名
                $users[] = $_users[1];  // 級段
            }
        });
    
        // 勝敗取得
        $results = array();
        $dom_result = $node->filter('img.setting_title');
        $dom_result->each(function ($node_result) use (&$results) {
            $_tmp = $node_result->attr('alt');
            if ($_tmp) {
                $results[] = $_tmp;
            }
        });
    
    
        if ($last_time < $ending_time) {
            // CSV データ
            $records[] = array(
                $ending_time,
                $users[0],
                $users[1],
                $results[0],
                $users[2],
                $users[3],
                $results[1],
                $permalink,
            );
            echo "Save: $ending_time\n";
        } else {
            echo "Does not save: $ending_time\n";
        }
    });
}

// 履歴CSV出力
$fp = fopen($history_file, 'a');
foreach ($records as $rows) {
    fputcsv($fp, $rows);
}
fclose($fp);

// 処理済みの最終対局日時を更新
file_put_contents($lasttime_file, $newest_time);

echo "Save " . count($records) . " counts.\n";
