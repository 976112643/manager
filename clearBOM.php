<!--
将这个文件放在网站根目录下面运行可以去除所有文件的bom，只针对当前目录下面的所有文件和子目录
-->
<?php
$basedir = str_replace('/clearBOM.php', '', str_replace('\\', '/', dirname(__FILE__)));
$auto = 1;
checkdir($basedir);

function checkdir($basedir)
{
    if ($dh = opendir($basedir)) {
        while (($file = readdir($dh)) !== false) {
            if ($file != '.' && $file != '..') {
                if (! is_dir($basedir . '/' . $file)) {
                    $filename = $basedir . '/' . $file;
                    echo 'filename:' . $basedir . '/' . $file . checkBOM($filename) . '<br>';
                } else {
                    $dirname = $basedir . '/' . $file;
                    checkdir($dirname);
                }
            }
        }
        closedir($dh);
    }
}

function checkBOM($filename)
{
    global $auto;
    $contents = file_get_contents($filename);
    $charset[1] = substr($contents, 0, 1);
    $charset[2] = substr($contents, 1, 1);
    $charset[3] = substr($contents, 2, 1);
    if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) {
        if ($auto == 1) {
            $rest = substr($contents, 3);
            rewrite($filename, $rest);
            return '<font color=red>BOM found,automatically removed.</font>';
        } else {
            return '<font color=red>BOM found.</font>';
        }
    } else {
        return 'BOM Not Found.';
    }
}

function rewrite($filename, $data)
{
    $filenum = fopen($filename, 'w');
    flock($filenum, LOCK_EX);
    fwrite($filenum, $data);
    fclose($filenum);
}
?>