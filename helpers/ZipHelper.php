<?php

/**
 * 开店星新零售管理系统
 * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开
 * @author 青岛开店星信息技术有限公司
 * @link https://www.kaidianxing.com
 * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.
 * @copyright 版权归青岛开店星信息技术有限公司所有
 * @warning Unauthorized deletion of copyright information is prohibited.
 * @warning 未经许可禁止私自删除版权信息
 */

namespace shopstar\helpers;

/**
 * ZIP助手类
 * Class ZipHelper
 * @package shopstar\helpers
 * @author 青岛开店星信息技术有限公司
 */
class ZipHelper {
    protected static $ctrl_dir = [];
    protected static $datasec = [];
    protected static $fileList = array();
    protected static $old_offset = 0;
    protected static $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";
    
    protected static function visitFile($path) {
        global $fileList;
        $path = str_replace("\\", "/", $path);
        $fdir = dir($path);
        
        while (($file = $fdir->read()) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            
            $pathSub = preg_replace("*/{2,}*", "/", $path . "/" . $file);  // 替换多个反斜杠
            $fileList[] = is_dir($pathSub) ? $pathSub . "/" : $pathSub;
            if (is_dir($pathSub)) {
                self::visitFile($pathSub);
            }
        }
        $fdir->close();
        return $fileList;
    }
    
    protected static function unix2DosTime($unixtime = 0) {
        $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);
        
        if ($timearray['year'] < 1980) {
            $timearray['year'] = 1980;
            $timearray['mon'] = 1;
            $timearray['mday'] = 1;
            $timearray['hours'] = 0;
            $timearray['minutes'] = 0;
            $timearray['seconds'] = 0;
        }
        
        return (($timearray['year'] - 1980) << 25)
            | ($timearray['mon'] << 21)
            | ($timearray['mday'] << 16)
            | ($timearray['hours'] << 11)
            | ($timearray['minutes'] << 5)
            | ($timearray['seconds'] >> 1);
    }
    
    protected static function addFile($data, $filename, $time = 0) {
        $filename = str_replace('\\', '/', $filename);
        
        $dtime = dechex(self::unix2DosTime($time));
        $hexdtime = '\x' . $dtime[6] . $dtime[7]
            . '\x' . $dtime[4] . $dtime[5]
            . '\x' . $dtime[2] . $dtime[3]
            . '\x' . $dtime[0] . $dtime[1];
        eval('$hexdtime = "' . $hexdtime . '";');
        
        $fr = "\x50\x4b\x03\x04";
        $fr .= "\x14\x00";
        $fr .= "\x00\x00";
        $fr .= "\x08\x00";
        $fr .= $hexdtime;
        $unc_len = strlen($data);
        $crc = crc32($data);
        $zdata = gzcompress($data);
        $c_len = strlen($zdata);
        $zdata = substr(substr($zdata, 0, strlen($zdata) - 4), 2);
        $fr .= pack('V', $crc);
        $fr .= pack('V', $c_len);
        $fr .= pack('V', $unc_len);
        $fr .= pack('v', strlen($filename));
        $fr .= pack('v', 0);
        $fr .= $filename;
        
        $fr .= $zdata;
        
        $fr .= pack('V', $crc);
        $fr .= pack('V', $c_len);
        $fr .= pack('V', $unc_len);
        
        self::$datasec[] = $fr;
        $new_offset = strlen(implode('', self::$datasec));
        
        $cdrec = "\x50\x4b\x01\x02";
        $cdrec .= "\x00\x00";
        $cdrec .= "\x14\x00";
        $cdrec .= "\x00\x00";
        $cdrec .= "\x08\x00";
        $cdrec .= $hexdtime;
        $cdrec .= pack('V', $crc);
        $cdrec .= pack('V', $c_len);
        $cdrec .= pack('V', $unc_len);
        $cdrec .= pack('v', strlen($filename));
        $cdrec .= pack('v', 0);
        $cdrec .= pack('v', 0);
        $cdrec .= pack('v', 0);
        $cdrec .= pack('v', 0);
        $cdrec .= pack('V', 32);
        
        $cdrec .= pack('V', self::$old_offset);
        self::$old_offset = $new_offset;
        
        $cdrec .= $filename;
        self::$ctrl_dir[] = $cdrec;
    }
    
    protected static function file() {
        $data = implode('', self::$datasec);
        $ctrldir = implode('', self::$ctrl_dir);
        
        return $data
            . $ctrldir
            . self::$eof_ctrl_dir
            . pack('v', sizeof(self::$ctrl_dir))
            . pack('v', sizeof(self::$ctrl_dir))
            . pack('V', strlen($ctrldir))
            . pack('V', strlen($data))
            . "\x00\x00";
    }
    
    protected static function readCentralDir($zip, $zipfile) {
        $size = filesize($zipfile);
        $max_size = ($size < 277) ? $size : 277;
        
        @fseek($zip, $size - $max_size);
        $pos = ftell($zip);
        $bytes = 0x00000000;
        
        while ($pos < $size) {
            $byte = @fread($zip, 1);
            $bytes = ($bytes << 8) | Ord($byte);
            $pos++;
            // if ($bytes == 0x504b0506) {
            if (substr(dechex($bytes), -8, 8) == '504b0506') {
                break;
            }
        }
        
        $data = unpack('vdisk/vdisk_start/vdisk_entries/ventries/Vsize/Voffset/vcomment_size', fread($zip, 18));
        
        $centd['comment'] = ($data['comment_size'] != 0) ? fread($zip, $data['comment_size']) : '';  // 注释
        $centd['entries'] = $data['entries'];
        $centd['disk_entries'] = $data['disk_entries'];
        $centd['offset'] = $data['offset'];
        $centd['disk_start'] = $data['disk_start'];
        $centd['size'] = $data['size'];
        $centd['disk'] = $data['disk'];
        return $centd;
    }
    
    protected static function readCentralFileHeaders($zip) {
        $binary_data = fread($zip, 46);
        $header = unpack('vchkid/vid/vversion/vversion_extracted/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len/vcomment_len/vdisk/vinternal/Vexternal/Voffset', $binary_data);
        
        $header['filename'] = ($header['filename_len'] != 0) ? fread($zip, $header['filename_len']) : '';
        $header['extra'] = ($header['extra_len'] != 0) ? fread($zip, $header['extra_len']) : '';
        $header['comment'] = ($header['comment_len'] != 0) ? fread($zip, $header['comment_len']) : '';
        
        
        if ($header['mdate'] && $header['mtime']) {
            $hour = ($header['mtime'] & 0xF800) >> 11;
            $minute = ($header['mtime'] & 0x07E0) >> 5;
            $seconde = ($header['mtime'] & 0x001F) * 2;
            $year = (($header['mdate'] & 0xFE00) >> 9) + 1980;
            $month = ($header['mdate'] & 0x01E0) >> 5;
            $day = $header['mdate'] & 0x001F;
            $header['mtime'] = mktime($hour, $minute, $seconde, $month, $day, $year);
        } else {
            $header['mtime'] = time();
        }
        $header['stored_filename'] = $header['filename'];
        $header['status'] = 'ok';
        if (substr($header['filename'], -1) == '/') {
            $header['external'] = 0x41FF0010;
        }  // 判断是否文件夹
        return $header;
    }
    
    protected static function readFileHeader($zip) {
        $binary_data = fread($zip, 30);
        $data = unpack('vchk/vid/vversion/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len', $binary_data);
        
        $header['filename'] = fread($zip, $data['filename_len']);
        $header['extra'] = ($data['extra_len'] != 0) ? fread($zip, $data['extra_len']) : '';
        $header['compression'] = $data['compression'];
        $header['size'] = $data['size'];
        $header['compressed_size'] = $data['compressed_size'];
        $header['crc'] = $data['crc'];
        $header['flag'] = $data['flag'];
        $header['mdate'] = $data['mdate'];
        $header['mtime'] = $data['mtime'];
        
        if ($header['mdate'] && $header['mtime']) {
            $hour = ($header['mtime'] & 0xF800) >> 11;
            $minute = ($header['mtime'] & 0x07E0) >> 5;
            $seconde = ($header['mtime'] & 0x001F) * 2;
            $year = (($header['mdate'] & 0xFE00) >> 9) + 1980;
            $month = ($header['mdate'] & 0x01E0) >> 5;
            $day = $header['mdate'] & 0x001F;
            $header['mtime'] = mktime($hour, $minute, $seconde, $month, $day, $year);
        } else {
            $header['mtime'] = time();
        }
        
        $header['stored_filename'] = $header['filename'];
        $header['status'] = "ok";
        return $header;
    }
    
    protected static function extractFile($header, $to, $zip) {
        $header = self::readfileheader($zip);
        
        if (substr($to, -1) != "/") {
            $to .= "/";
        }
        if (!@is_dir($to)) {
            @mkdir($to, 0777);
        }
        $pthss = '';
        $pth = explode("/", dirname($header['filename']));
        for ($i = 0; isset($pth[$i]); $i++) {
            if (!$pth[$i]) {
                continue;
            }
            $pthss .= $pth[$i] . "/";
            if (!is_dir($to . $pthss)) {
                @mkdir($to . $pthss, 0777);
            }
        }
        
        if (!($header['external'] == 0x41FF0010) && !($header['external'] == 16)) {
            if ($header['compression'] == 0) {
                $fp = @fopen($to . $header['filename'], 'wb');
                if (!$fp) {
                    return (-1);
                }
                $size = $header['compressed_size'];
                
                while ($size != 0) {
                    $read_size = ($size < 2048 ? $size : 2048);
                    $buffer = fread($zip, $read_size);
                    $binary_data = pack('a' . $read_size, $buffer);
                    @fwrite($fp, $binary_data, $read_size);
                    $size -= $read_size;
                }
                fclose($fp);
                touch($to . $header['filename'], $header['mtime']);
                
            } else {
                
                $fp = @fopen($to . $header['filename'] . '.gz', 'wb');
                if (!$fp) {
                    return (-1);
                }
                $binary_data = pack('va1a1Va1a1', 0x8b1f, Chr($header['compression']), Chr(0x00), time(), Chr(0x00), Chr(3));
                
                fwrite($fp, $binary_data, 10);
                $size = $header['compressed_size'];
                
                while ($size != 0) {
                    $read_size = ($size < 1024 ? $size : 1024);
                    $buffer = fread($zip, $read_size);
                    $binary_data = pack('a' . $read_size, $buffer);
                    @fwrite($fp, $binary_data, $read_size);
                    $size -= $read_size;
                }
                
                $binary_data = pack('VV', $header['crc'], $header['size']);
                fwrite($fp, $binary_data, 8);
                fclose($fp);
                
                $gzp = @gzopen($to . $header['filename'] . '.gz', 'rb') or die("Cette archive est compress!");
                
                if (!$gzp) {
                    return (-2);
                }
                $fp = @fopen($to . $header['filename'], 'wb');
                if (!$fp) {
                    return (-1);
                }
                $size = $header['size'];
                
                while ($size != 0) {
                    $read_size = ($size < 2048 ? $size : 2048);
                    $buffer = gzread($gzp, $read_size);
                    $binary_data = pack('a' . $read_size, $buffer);
                    @fwrite($fp, $binary_data, $read_size);
                    $size -= $read_size;
                }
                fclose($fp);
                gzclose($gzp);
                
                touch($to . $header['filename'], $header['mtime']);
                @unlink($to . $header['filename'] . '.gz');
            }
        }
        return true;
    }
    
    /**
     * 解压缩
     * @param $zipfile
     * @param $to
     * @param array $index
     * @return int
     * @author likexin
     */
    public static function unCompress($zipfile, $to, $index = array(-1)) {
        $ok = 0;
        $zip = @fopen($zipfile, 'rb');
        if (!$zip) {
            return (-1);
        }
        
        $cdir = self::readCentralDir($zip, $zipfile);
        $pos_entry = $cdir['offset'];
        
        if (!is_array($index)) {
            $index = array($index);
        }
        for ($i = 0; $index[$i]; $i++) {
            if (intval($index[$i]) != $index[$i] || $index[$i] > $cdir['entries']) {
                return (-1);
            }
        }
        
        $stat = [];
        
        for ($i = 0; $i < $cdir['entries']; $i++) {
            @fseek($zip, $pos_entry);
            $header = self::readCentralFileHeaders($zip);
            $header['index'] = $i;
            $pos_entry = ftell($zip);
            @rewind($zip);
            fseek($zip, $header['offset']);
            if (in_array("-1", $index) || in_array($i, $index)) {
                $stat[$header['filename']] = self::extractFile($header, $to, $zip);
            }
        }
        fclose($zip);
        
        return $stat;
    }
    
    /**
     * 压缩
     * @param $dir
     * @param $saveName
     * @author likexin
     */
    public static function compress($dir, $saveName) {
        if (@!function_exists('gzcompress')) {
            return;
        }
        
        ob_end_clean();
        $filelist = self::visitFile($dir);
        if (count($filelist) == 0) {
            return;
        }
        
        foreach ($filelist as $file) {
            if (!file_exists($file) || !is_file($file)) {
                continue;
            }
            
            $fd = fopen($file, "rb");
            $content = @fread($fd, filesize($file));
            fclose($fd);
            
            // 1.删除$dir的字符(./folder/file.txt删除./folder/)
            // 2.如果存在/就删除(/file.txt删除/)
            $file = substr($file, strlen($dir));
            if (substr($file, 0, 1) == "\\" || substr($file, 0, 1) == "/") {
                $file = substr($file, 1);
            }
            
            self::addFile($content, $file);
        }
        $out = self::file();
        
        $fp = fopen($saveName, "wb");
        fwrite($fp, $out, strlen($out));
        fclose($fp);
    }
    
    /**
     * @param string $dir
     * @param string $filename
     * @param bool $delete
     * @author 青岛开店星信息技术有限公司
     */
    public static function compressAndDowload(string $dir, string $filename = '', bool $delete = false) {
        if (@!function_exists('gzcompress')) {
            return;
        }
        
        ob_end_clean();
        $filelist = self::visitFile($dir);
        if (count($filelist) == 0) {
            return;
        }
        
        foreach ($filelist as $file) {
            if (!file_exists($file) || !is_file($file)) {
                continue;
            }
            
            $fd = fopen($file, "rb");
            $content = @fread($fd, filesize($file));
            fclose($fd);
            
            // 1.删除$dir的字符(./folder/file.txt删除./folder/)
            // 2.如果存在/就删除(/file.txt删除/)
            $file = substr($file, strlen($dir));
            if (substr($file, 0, 1) == "\\" || substr($file, 0, 1) == "/") {
                $file = substr($file, 1);
            }
            self::addFile($content, $file);
        }
        $out = self::file();
        
        $filename = empty($filename) ? date("YmdHis", time()) : $filename;
        
        @header('Content-Encoding: none');
        @header('Content-Type: application/zip');
        @header('Content-Disposition: attachment ; filename=' . $filename . '.zip');
        @header('Pragma: no-cache');
        @header('Expires: 0');
        
        // 下载完删除打包目录
        if ($delete) {
            FileHelper::removeDirectory($dir);
        }
        
        print($out);
    }
    
    /**
     * 获取被压缩文件的信息
     * @param $zipfile
     * @return array|int
     */
    public function getInfo($zipfile) {
        $zip = @fopen($zipfile, 'rb');
        if (!$zip) {
            return (0);
        }
        $centd = $this->ReadCentralDir($zip, $zipfile);
        
        @rewind($zip);
        @fseek($zip, $centd['offset']);
        $ret = array();
        
        for ($i = 0; $i < $centd['entries']; $i++) {
            $header = $this->ReadCentralFileHeaders($zip);
            $header['index'] = $i;
            $info = array(
                'filename' => $header['filename'],                   // 文件名
                'stored_filename' => $header['stored_filename'],            // 压缩后文件名
                'size' => $header['size'],                       // 大小
                'compressed_size' => $header['compressed_size'],            // 压缩后大小
                'crc' => strtoupper(dechex($header['crc'])),    // CRC32
                'mtime' => date("Y-m-d H:i:s", $header['mtime']),  // 文件修改时间
                'comment' => $header['comment'],                    // 注释
                'folder' => ($header['external'] == 0x41FF0010 || $header['external'] == 16) ? 1 : 0,  // 是否为文件夹
                'index' => $header['index'],                      // 文件索引
                'status' => $header['status']                      // 状态
            );
            $ret[] = $info;
            unset($header);
        }
        fclose($zip);
        return $ret;
    }
    
    /**
     * 获取压缩文件的注释
     * @param $zipfile
     * @return int
     */
    public static function getComment($zipfile) {
        $zip = @fopen($zipfile, 'rb');
        if (!$zip) {
            return (0);
        }
        $centd = self::readCentralDir($zip, $zipfile);
        fclose($zip);
        
        return $centd['comment'];
    }
    
    /**
     * 下载
     * @param $name
     * @param $path
     * @return void
     * @author 青岛开店星信息技术有限公司
     *
     */
    public static function getZip($name, $path) {
        //下载zip
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename=' . $name); //文件名
        header("Content-Type: application/zip"); //zip格式的
        header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
        header('Content-Length: ' . filesize($path)); //告诉浏览器，文件大小
        readfile($path);
        exit();
    }
}