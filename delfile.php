<?php

/* 删除缓存文件 */
function deldir($dir)
{
    $dh = opendir($dir);
    echo 'Folder:&nbsp;' . $dir . '&nbsp;start cleaning...<br/>';
    while ($file = readdir($dh)) {
        if ($file != "." && $file != "..") {
            $fullpath = $dir . "/" . $file;
            if (! is_dir($fullpath)) {
                unlink($fullpath);
                echo htmlspecialchars_decode('filename:&nbsp;' . $fullpath . '&nbsp;has been deleted.<br/>');
            } else {
                deldir($fullpath);
            }
        }
    }
    closedir($dh);
}
deldir('Runtime');