<?php
/**
 * Created by PhpStorm.
 * User: akira
 * Date: 2018-12-30
 * Time: 13:32
 */
header("Content-type:text/html;charset=utf-8");
if (preg_match("/Android|iPhone|IOS/i", $_SERVER['HTTP_USER_AGENT'])) {
    die('<p style="color:#fff;background:#ca0000;margin: 5px;padding: 5px;text-align: center;">禁止在移动设备使用该脚本</p>');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>歌词批量生成脚本</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            outline: none;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }
        .search {
            margin-bottom: 8px;
            padding: 0 5px 8px 5px;
            border-bottom: 2px solid #999;
        }
        .box {
            margin: 10px 0;
            padding: 5px;
        }
        input[type="text"] {
            height: 32px;
            border: 1px solid #999;
            text-indent: 0.5em;
            border-radius: 4px;
        }
        .api_url input[type="text"], .search_ipt input[type="text"], .opencc_path input[type="text"] {
            width: 100%;
        }
        .lyric_style input[type="radio"], .overwrite_already_exists_lyric input[type="radio"], .opencc_path input[type="radio"], .debug input[type="radio"] {
            vertical-align: top;
            margin-right: 5px;
            margin-top: 4px;
        }
        .search_precision input[type="text"]{
            width: 33%;
        }
        .submit {
            color: #fff;
            width: 120px;
            height: 40px;
            background: #000;
            font-size: 16px;
            cursor: pointer;
            border: none;
            -webkit-border-radius: 6px;
            -moz-border-radius: 6px;
            border-radius: 6px;
        }
    </style>
    <bady>
        <form action="" method="post" class="search">
            <fieldset class="box api_url">
                <legend>网易云音乐 API 地址</legend>
                <input type="text" name="api_url" placeholder="API 服务器地址，需要带 http 前缀" value="<?php echo isset($_POST['api_url']) ? $_POST['api_url'] : '' ?>">
            </fieldset>
            <fieldset class="box opencc_path">
                <legend>OpenCC 设置</legend>
                <input type="radio" name="opencc" value="opencc_on" <?php if(!isset($_POST['opencc']) || $_POST['opencc'] == 'opencc_on') echo 'checked="checked"'; ?>>启用
                <input type="radio" name="opencc" value="opencc_off" <?php if(isset($_POST['opencc']) && $_POST['opencc'] == 'opencc_off') echo 'checked="checked"'; ?>>不启用
                <input type="text" name="opencc_path" placeholder="如果为 Linux 或 Unix 则无需输入路径，默认为：t2s.json <-|-> 如果为 windows 系统则需手动指定 OpenCC json 的绝对路径，使用的 json 为：t2s.json，例如：D:/opencc/t2s.json" value="<?php echo isset($_POST['opencc_path']) ? $_POST['opencc_path'] : ''; ?>">
            </fieldset>
            <fieldset class="box search_ipt">
                <legend>音乐文件夹路径</legend>
                <input type="text" name="path" placeholder="请输入歌曲文件夹绝对路径，例如：D:/music/" value="<?php echo isset($_POST['path']) ? $_POST['path'] : ''; ?>">
            </fieldset>
            <fieldset class="box search_precision">
                <legend>匹配精度</legend>
                <input type="text" name="music_name_precision" maxlength="3" value="<?php echo isset($_POST['music_name_precision']) ? $_POST['music_name_precision'] : '' ?>" placeholder="歌曲名字，默认：80（0 ~ 100，单位为%）">
                <input type="text" name="artist_precision" maxlength="3" value="<?php echo isset($_POST['artist_precision']) ? $_POST['artist_precision'] : '' ?>" placeholder="艺术家名字，默认：80（0 ~ 100，单位为%）">
                <input type="text" name="duration_precision" maxlength="2" value="<?php echo isset($_POST['duration_precision']) ? $_POST['duration_precision'] : '' ?>" placeholder="歌曲时长偏移量，默认：正负5（0 ~ 30，单位为s）">
            </fieldset>
            <fieldset class="box lyric_style">
                <legend>歌词样式</legend>
                <input type="radio" name="style" value="style_1" <?php if(!isset($_POST['style']) || $_POST['style'] == 'style_1') echo 'checked="checked"'; ?>>样式一
                <input type="radio" name="style" value="style_2" <?php if(isset($_POST['style']) && $_POST['style'] == 'style_2') echo 'checked="checked"'; ?>>样式二
            </fieldset>
            <fieldset class="box overwrite_already_exists_lyric">
                <legend>是否覆盖已有歌词</legend>
                <input type="radio" name="overwrite" value="ow_y" <?php if(isset($_POST['overwrite']) && $_POST['overwrite'] == 'ow_y') echo 'checked="checked"'; ?>>是
                <input type="radio" name="overwrite" value="ow_n" <?php if(!isset($_POST['overwrite']) || $_POST['overwrite'] == 'ow_n') echo 'checked="checked"'; ?>>否
            </fieldset>
            <fieldset class="box debug">
                <legend>显示详细的匹配信息</legend>
                <input type="radio" name="debug" value="1" <?php if(isset($_POST['debug']) && $_POST['debug'] == '1') echo 'checked="checked"'; ?>>开启
                <input type="radio" name="debug" value="0" <?php if(!isset($_POST['debug']) || $_POST['debug'] == '0') echo 'checked="checked"'; ?>>关闭
            </fieldset>
            <input type="submit" name="make" value="生成 LRC 歌词" class="submit">
        </form>
    </bady>
</head>
<?php
// 初始限制
if (!isset($_POST['make'])) {
    die();
}
if (!isset($_POST['api_url']) || $_POST['api_url'] == null) {
    die('<p style="color:#fff;background:#ca0000;margin: 5px;padding: 5px;text-align: center;">API 地址不能为空</p>');
}
if (!isset($_POST['path']) || $_POST['path'] == null) {
    die('<p style="color:#fff;background:#ca0000;margin: 5px;padding: 5px;text-align: center;">路径不能为空</p>');
}
if (!isset($_POST['opencc_path']) || !isset($_POST['opencc']) || $_POST['opencc'] == null) {
    die('<p style="color:#fff;background:#ca0000;margin: 5px;padding: 5px;text-align: center;">OpenCC 参数错误</p>');
}
if (!isset($_POST['music_name_precision']) || !isset($_POST['artist_precision']) || !isset($_POST['duration_precision']) || !isset($_POST['style']) || $_POST['style'] == null || !isset($_POST['overwrite']) || $_POST['overwrite'] == null || !isset($_POST['opencc']) || $_POST['opencc'] == null || !isset($_POST['debug']) || $_POST['debug'] == null) {
    die('<p style="color:#fff;background:#ca0000;margin: 5px;padding: 5px;text-align: center;">参数错误</p>');
}
if ($_POST['music_name_precision'] != null) {
    if (!is_numeric($_POST['music_name_precision']) || $_POST['music_name_precision'] > 100 || $_POST['music_name_precision'] < 0) {
        die('<p style="color:#fff;background:#ca0000;margin: 5px;padding: 5px;text-align: center;">歌曲名，精度参数错误</p>');
    }
}
if ($_POST['artist_precision'] != null) {
    if (!is_numeric($_POST['artist_precision']) || $_POST['artist_precision'] > 100 || $_POST['artist_precision'] < 0) {
        die('<p style="color:#fff;background:#ca0000;margin: 5px;padding: 5px;text-align: center;">艺术家名字，精度参数错误</p>');
    }
}
if ($_POST['duration_precision'] != null) {
    if (!is_numeric($_POST['duration_precision']) || $_POST['duration_precision'] > 30 || $_POST['duration_precision'] < 0) {
        die('<p style="color:#fff;background:#ca0000;margin: 5px;padding: 5px;text-align: center;">歌曲时长，偏移参数错误</p>');
    }
}

// 音乐文件夹路径末端判断是否有斜杠
$path = mb_substr(trim($_POST['path']), -1) == '/' ? trim($_POST['path']) : trim($_POST['path']).'/';

// 根据 UA 判断设备类型
if (preg_match("/Mac/i", $_SERVER['HTTP_USER_AGENT'])) {
    // mac 路径转换
    if (preg_match("/\//i", $_SERVER['HTTP_USER_AGENT'])) {
        // 音乐文件夹路径格式化
        $path = str_replace("\\","",$path);
        // OpenCC 路径格式化
        $op_path = $_POST['opencc_path'] != null ? trim($_POST['opencc_path']) : 't2s.json';
        $op_path = str_replace("\\","",$op_path);
    }
} else {
    // windows 路径转换
    if (preg_match("/\//i", $path)) {
        // 音乐文件夹路径格式化
        $path = str_replace("\\","/",$path);
        // OpenCC 路径格式化
        $op_path = $_POST['opencc_path'] != null ? trim($_POST['opencc_path']) : 't2s.json';
        $op_path = str_replace("\\","/",$op_path);
    }
}

// API URL
$api_url = mb_substr(trim($_POST['api_url']), -1) == '/' ? mb_substr(trim($_POST['api_url']), 0, -1) : trim($_POST['api_url']);

// 扫描目录
if (!$dir = scandir($path)) {
    die('<p style="color:#fff;background:#ca0000;margin: 5px;padding: 5px;text-align: center;">目录打开失败</p>');
}

// 调用 getid3 获取歌曲信息
require_once('getid3/getid3.php');
$getID3 = new getID3();

// 调用 OpenCC 进行繁体转简体
if ($_POST['opencc'] == 'opencc_on') {
    if (!$oc = opencc_open($op_path)) {
        die('<p style="color:#fff;background:#ca0000;margin: 5px;padding: 5px;text-align: center;">OpenCC 调用异常</p>');
    }
}

// 精度
$music_name_precision = $_POST['music_name_precision'] == null ? 80 : (int)$_POST['music_name_precision'];
$artist_precision = $_POST['artist_precision'] == null ? 80 : (int)$_POST['artist_precision'];
$duration_precision = $_POST['duration_precision'] == null ? 5 : ((int)$_POST['duration_precision']);

// 开始运行！！！
for($i = 0;$i < count($dir);$i++) {
    if (!preg_match("/flac|wav|mp3/i", substr($dir[$i], -4))) {
        continue;
    }
    // 当有歌词文件时跳过
    if (in_array(preg_split("/.(?=[^.]*$)/", $dir[$i])[0] . '.lrc', $dir) && $_POST['overwrite'] == 'ow_n') {
        continue;
    }
    $search_flag = 1;
    $FileInfo = $getID3->analyze($path . $dir[$i]);
    $duration = floor($FileInfo['playtime_seconds']);
    $type = $FileInfo['audio']['dataformat'];
    $file_path = $FileInfo['filepath'];
    $file_name = $FileInfo['filename'];
    if (isset($FileInfo['tags'])) {
        if ($type == 'flac') {
            $music_name = $FileInfo['tags']['vorbiscomment']['title'][0];
            $album = $FileInfo['tags']['vorbiscomment']['album'][0];
            $artist = $FileInfo['tags']['vorbiscomment']['artist'][0];
        } elseif ($type == 'wav' || $type == 'mp3') {
            $music_name = $FileInfo['tags']['id3v2']['title'][0];
            $album = $FileInfo['tags']['id3v2']['album'][0];
            $artist = $FileInfo['tags']['id3v2']['artist'][0];
        }
    }
    if (!isset($music_name) || $music_name == null) {
        $music_name = preg_split("/.(?=[^.]*$)/", $file_name)[0];
        $music_name = explode('.', $music_name)[1];
        $music_name = trim($music_name);
    }
    $album = isset($album) ? $album : '';
    $artist = isset($artist) ? $artist : '';
    $opencc_tr = $_POST['opencc'] == 'opencc_on' ? opencc_convert($music_name, $oc) : '';
    $data_1 = musicKeywordApi($api_url, urlencode(trim($music_name . ' ' . $album)));
    $data_2 = musicKeywordApi($api_url, urlencode($music_name));
    if (matchMusic($data_1, $artist, $music_name, $duration, $api_url, $file_path, $file_name, $music_name_precision, $artist_precision, $duration_precision, $_POST['debug'])) {
    } elseif (matchMusic($data_2, $artist, $music_name, $duration, $api_url, $file_path, $file_name, $music_name_precision, $artist_precision, $duration_precision, $_POST['debug'])) {
    } elseif (matchMusic($data_1, $artist, $opencc_tr, $duration, $api_url, $file_path, $file_name, $music_name_precision, $artist_precision, $duration_precision, $_POST['debug'])) {
    } elseif (matchMusic($data_2, $artist, $opencc_tr, $duration, $api_url, $file_path, $file_name, $music_name_precision, $artist_precision, $duration_precision, $_POST['debug'])) {
    } else {
        echo '<p style="color:#fff;background:#ca0000;margin: 5px;padding: 5px;">歌词生成失败（无匹配项）：' . preg_split("/.(?=[^.]*$)/", $file_name)[0] . '.lrc</p>';
    }
}

// OpenCC 调用结束，关闭
if ($_POST['opencc'] == 'opencc_on') {
    opencc_close($oc);
}

/**
 * @param $data 从 API 获取到的一堆歌曲信息
 * @param $artist getis3 获取到的 艺术家 信息
 * @param $music_name getis3 获取到的 歌曲 名字
 * @param $path 歌词生成路径
 * @param $file_name 文件名（完整，带后缀）
 * @return string 是否成功匹配到了歌曲
 */
function matchMusic($data, $artist, $music_name, $duration, $url, $path, $file_name, $music_name_precision, $artist_precision, $duration_precision, $debug) {
    if ($data == null || $music_name == null) {
        return 0;
    }
    $song = $data->songs;
    for ($i = 0;$i < count($song);$i++) {
        $api_music_artist = $song[$i]->artists[0]->name;
        $api_music_name = $song[$i]->name;
        $api_music_id = $song[$i]->id;
        $api_music_duration = $song[$i]->duration;
        // 艺术家相似度
        similar_text($api_music_artist, $artist, $similar_artist);
        // 歌曲名相似度
        similar_text($api_music_name, $music_name, $similar_music_name);
        // 歌曲时长相似度
        $api_duration = floor($api_music_duration/1000);
        // 详细的匹配信息
        if ($debug) {
            echo '<div style="margin: 5px;padding: 5px;border: 1px solid #999;">';
            echo '<p style="font-size: 0.9em;line-height: 1.1em;"><span style="display: inline-block;width: 7em;">本地歌曲名：</span>'.$music_name.'</p>';
            echo '<p style="font-size: 0.9em;line-height: 1.1em;"><span style="display: inline-block;width: 7em;">API歌曲名：</span>'.$api_music_name.'</p>';
            echo '<p style="font-size: 0.9em;line-height: 1.1em;"><span style="display: inline-block;width: 7em;">本地艺术家：</span>'.$artist.'</p>';
            echo '<p style="font-size: 0.9em;line-height: 1.1em;"><span style="display: inline-block;width: 7em;">API艺术家：</span>'.$api_music_artist.'</p>';
            echo '<p style="font-size: 0.9em;line-height: 1.1em;"><span style="display: inline-block;width: 7em;">本地时长：</span>'.$duration.'</p>';
            echo '<p style="font-size: 0.9em;line-height: 1.1em;"><span style="display: inline-block;width: 7em;">API时长：</span>'.$api_duration.'</p>';
            echo '<p style="font-size: 0.9em;line-height: 1.1em;"><span style="display: inline-block;width: 7em;">歌曲名相似度：</span>'.$similar_music_name.'</p>';
            echo '<p style="font-size: 0.9em;line-height: 1.1em;"><span style="display: inline-block;width: 7em;">艺术家相似度：</span>'.$similar_artist.'</p>';
            echo '</div>';
        }
        // 匹配判断
        if ($similar_artist >= $artist_precision && $similar_music_name >= $music_name_precision) {
            musicLrcApi($url, $api_music_id, $path, $file_name);
            return 1;
        } elseif ($similar_music_name >= $music_name_precision && $duration >= $api_duration - $duration_precision && $duration <= $api_duration + $duration_precision) {
            musicLrcApi($url, $api_music_id, $path, $file_name);
            return 1;
        }
    }
    return 0;
}

/**
 * @param $str 需要被去除垃圾的歌词
 * @return string 干净的字符串
 */
function clean($str) {
    $temp_str_back = trim($str);
    $temp_str_start = mb_substr($temp_str_back, 0, 1);
    $temp_str_end = mb_substr($temp_str_back, -1);
    $temp_str_2 = $temp_str_start.$temp_str_end;
    $pattern = '【|】|〖|〗|「|」|『|』|（|）|(|)|\/';
    if (preg_match( '/'.$pattern.'/i', $temp_str_2)) {
        $str = cleanAdd_1($str, '【', '】');
        $str = cleanAdd_1($str, '〖', '〗');
        $str = cleanAdd_1($str, '「', '」');
        $str = cleanAdd_1($str, '『', '』');
        $str = cleanAdd_1($str, '（', '）');
        $str = cleanAdd_1($str, '\(', '\)');
        $str = cleanAdd_2($str, '/');
    }
    return $str;
}

/**
 * clean 的延伸
 * @param $str 去除时间轴的字符串
 * @param $t_str_1 目标字符一
 * @param $t_str_2 目标字符二
 * @return string 干净的字符串
 */
function cleanAdd_1($str, $t_str_1, $t_str_2) {
    $start = mb_substr($str, 0, 1);
    $end = mb_substr($str, -1);
    if ($start == $t_str_1 && $end == $t_str_2) {
        $str = mb_substr($str, 1);
        $str = mb_substr($str, 0, -1);
    } else if ($start == $t_str_1 && $end != $t_str_2 && !preg_match('/'.$t_str_2.'/i', $str)) {
        $str = mb_substr($str, 1);
    } else if ($start != $t_str_1 && $end == $t_str_2 && !preg_match('/'.$t_str_1.'/i', $str)) {
        $str = mb_substr($str, 0, -1);
    }
    return $str;
}

/**
 * clean 的延伸
 * @param $str 去除时间轴的字符串
 * @param $t_str 目标字符
 * @return string 干净的字符串
 */
function cleanAdd_2($str, $t_str) {
    $start = mb_substr($str, 0, 1);
    $end = mb_substr($str, -1);
    if ($start == $t_str) {
        $str = mb_substr($str, 1);
    }
    if ($end == $t_str) {
        $str = mb_substr($str, 0, -1);
    }
    return $str;
}

/**
 * @param $method 歌曲关键字
 * @return string 从 API 获取到的结果
 */
function musicKeywordApi($url, $method) {
    $url = $url.'/search?keywords='.$method;
    if (!$data = json_decode(file_get_contents($url))) {
        die('<p style="color:#fff;background:#ca0000;margin: 5px;padding: 5px;">API Error</p>');
    }
    if ($data->code == 200 && $data->result->songCount != 0) {
        return $data->result;
    } else {
        return null;
    }
}

/**
 * @param $id 歌曲 ID
 * @param $path 歌词生成路径
 * @param $file_name 文件名（完整，带后缀）
 */
function musicLrcApi($url, $id, $path, $file_name) {
    $url = $url.'/lyric?id='.$id;
    $data = json_decode(file_get_contents($url));
    $obj = null;
    if ($data->code != 200) {
        echo '<p style="color:#fff;background:#ca0000;margin: 5px;padding: 5px;">歌词生成失败（接口 err）：' . preg_split("/.(?=[^.]*$)/", $file_name)[0] . '.lrc</p>';
        return;
    }
    if (isset($data->lrc)) {
        if (isset($data->tlyric) && isset($data->tlyric->lyric)) {
            $obj['lrc'] = $data->lrc->lyric;
            $obj['translrc'] = $data->tlyric->lyric;
        } else {
            $obj['lrc'] = $data->lrc->lyric;
        }
        generateLrc($obj, $path, $file_name);
    } else {
        echo '<p style="color:#fff;background:#ca0000;margin: 5px;padding: 5px;">歌词生成失败（无歌词）：' . preg_split("/.(?=[^.]*$)/", $file_name)[0] . '.lrc</p>';
    }
}

/**
 * @param $obj 从 API 获取到的歌词
 * @param $path 歌词生成路径
 * @param $file_name 文件名（完整，带后缀）
 */
function generateLrc($obj, $path, $file_name) {
    // 清除原歌词中多余废话
    $lrc = explode("\n", $obj['lrc']);
    $lrc_flag = 1;
    for ($i = 0; $i < count($lrc); $i++) {
        $temp_str = mb_substr($lrc[$i], 1, 2);
        $lrc_str = preg_split("/\](?=[^\]]*$)/", $lrc[$i]);
        if (!is_numeric($temp_str) || preg_match("/:/i", $lrc_str[1]) || preg_match("/：/i", $lrc_str[1])) {
            unset($lrc[$i]);
            continue;
        } elseif ($lrc_str[1] == null && $lrc_flag) {
            unset($lrc[$i]);
            continue;
        } else {
            $lrc_flag = 0;
        }
        // 清空歌词首尾空格
        $lrc[$i] = $lrc_str[0].']'.trim($lrc_str[1]);
    }
    $lrc = array_values($lrc);  // 索引归零

    // 清除翻译中多余废话
    if (isset($obj['translrc'])) {
        $translrc = explode("\n", $obj['translrc']);
        for ($i = 0; $i < count($translrc); $i++) {
            $temp_str = mb_substr($translrc[$i], 1, 2);
            $lrc_str = preg_split("/\](?=[^\]]*$)/", $translrc[$i]);
            if (!is_numeric($temp_str) || preg_match("/:/i", $lrc_str[1]) || preg_match("/：/i", $lrc_str[1])) {
                unset($translrc[$i]);
                continue;
            } elseif ($lrc_str[1] == null) {
                unset($translrc[$i]);
                continue;
            }
            // 清空歌词首尾空格
            $translrc[$i] = $lrc_str[0].']'.trim($lrc_str[1]);
        }
        $translrc = array_values($translrc);  // 索引归零
    }

    // 最终歌词数组
    $final_lrc = [];

    // 原歌词与翻译拼接：样式一
    if ($_POST['style'] == 'style_1') {
        if (isset($obj['translrc'])) {
            for ($i = 0; $i < count($lrc); $i++) {
                $flag = 0;
                $lrc_temp = mb_substr($lrc[$i], 1, 8);
                for ($j = 0; $j < count($translrc); $j++) {
                    $translrc_temp = mb_substr($translrc[$j], 1, 8);
                    if ($lrc_temp == $translrc_temp  && strlen($translrc[$j]) > 10) {
                        $translrc_temp_2 = preg_split("/\](?=[^\]]*$)/", $translrc[$j])[1];
                        $translrc_temp_2 = clean($translrc_temp_2);
                        $flag = 1;
                        break;
                    }
                }
                if ($flag) {
                    if ($lrc[$i] != null) {
                        array_push($final_lrc,$lrc[$i]." 「".$translrc_temp_2."」\n");
                    }
                } else {
                    if ($lrc[$i] != null) {
                        array_push($final_lrc,$lrc[$i]."\n");
                    }
                }
            }
        } else if (isset($obj['lrc'])) {
            for ($i = 0; $i < count($lrc); $i++) {
                if ($lrc[$i] != null) {
                    array_push($final_lrc,$lrc[$i]."\n");
                }
            }
        }
    }

    // 原歌词与翻译拼接：样式二
    if ($_POST['style'] == 'style_2') {
        if (isset($obj['translrc'])) {
            for ($i = 0;$i < count($lrc);$i++) {
                if ($lrc[$i] != null) {
                    $lrc_temp = mb_substr($lrc[$i],1,8);
                    array_push($final_lrc, trim($lrc[$i])."\n");
                }
                for ($j = 0;$j < count($translrc);$j++) {
                    if ($translrc[$j] != null) {
                        $translrc_temp = mb_substr($translrc[$j],1,8);
                        if ($lrc_temp == $translrc_temp && $translrc[$j] != null) {
                            $translrc_temp_2 = preg_split("/\](?=[^\]]*$)/", $translrc[$j]);
                            $translrc_temp_2_back = clean($translrc_temp_2[1]);
                            array_push($final_lrc, ($translrc_temp_2[0].']'.$translrc_temp_2_back) . "\n");
                            break;
                        }
                    }
                }
            }
        } else if (isset($data['lrc'])) {
            for ($i = 0;$i < count($lrc);$i++) {
                if ($lrc[$i] != null) {
                    array_push($final_lrc, $lrc[$i]."\n");
                }
            }
        }
    }

    // 创建歌词
    $lrc_name = preg_split("/.(?=[^.]*$)/", $file_name)[0].'.lrc';
    if (!$file = fopen($path . '/' . $lrc_name, "w")) {
        echo '<p style="color:#fff;background:#ca0000;margin: 5px;padding: 5px;">歌词创建失败：'.$lrc_name.'</p>';
    }
    if (fwrite($file, implode($final_lrc))) {
        echo '<p style="color:#fff;background:#006d00;margin: 5px;padding: 5px;">歌词生成成功：'.$lrc_name.'</p>';
    }
    fclose($file);
}