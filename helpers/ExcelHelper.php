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

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Yii;
use yii\base\Exception;

/**
 * Excel处理助手
 * Class ExcelHelper
 * @package shopstar\helpers
 */
class ExcelHelper
{
    protected static function column_str($key)
    {
        $array = array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
            'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ',
            'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ',
            'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN', 'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ',
            'EA', 'EB', 'EC', 'ED', 'EE', 'EF', 'EG', 'EH', 'EI', 'EJ', 'EK', 'EL', 'EM', 'EN', 'EO', 'EP', 'EQ', 'ER', 'ES', 'ET', 'EU', 'EV', 'EW', 'EX', 'EY', 'EZ'

        );
        return $array[$key];
    }

    protected static function column($key, $columnnum = 1)
    {
        return self::column_str($key) . $columnnum;
    }

    /**
     * 导出Excel
     * @param $list
     * @param array $columns
     * @param string $title
     * @param string $path
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception|null
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function export($list, $columns = [], $title = '', $path = '')
    {
        if (PHP_SAPI == 'cli') {
            throw new Exception('Excel::export should only be run from a Web Browser');
        }

        $excel = new Spreadsheet();
        $excel->getProperties()->setTitle("Office 2007 XLSX Test Document")->setSubject("Office 2007 XLSX Test Document")->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")->setKeywords("office 2007 openxml php")->setCategory("report file");
        $sheet = $excel->setActiveSheetIndex(0);

        //行数
        $rownum = 1;
        //标题
        foreach ($columns as $key => $column) {
            $sheet->setCellValue(self::column($key, $rownum), $column['title']);
            if (!empty($column['width'])) {
                $sheet->getColumnDimension(self::column_str($key))->setWidth($column['width']);
            }
        }
        $rownum++;
        //数据
        $len = count($columns);
        foreach ($list as $row) {

            for ($i = 0; $i < $len; $i++) {
                $value = isset($row[$columns[$i]['field']]) ? $row[$columns[$i]['field']] : '';
                $value = str_replace('=', "", $value);
                $sheet->setCellValue(self::column($i, $rownum), $value);
            }
            $rownum++;
        }

        $excel->getActiveSheet()->setTitle($title);

        $filename = date('Y-m-d-H-i-s', time());
        if (!empty($title)) {
            $filename = urlencode($title) . '-' . $filename;
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($excel, 'Xlsx');
        // 有path为本地保存,无path为直接下载
        if (!empty($path)) {
            $basePath = Yii::getAlias('@app/public/');
            $savepath = ltrim(rtrim($path, '/'), '/') . DIRECTORY_SEPARATOR . md5($filename) . '.xlsx';
            $filepath = $basePath . $savepath;
            $writer->save($filepath);
            return [
                'filename' => $filename . '.csv',
                'filepath' => $savepath
            ];
        } else {
            $writer->save('php://output');
        }
        //删除清空：
        $excel->disconnectWorksheets();
        unset($excel);
        exit;
    }

    /**
     * @param $list
     * @param array $columns
     * @param string $title
     * @param string $path
     * @param string $appName
     * @return array
     * @throws null
     * @author 青岛开店星信息技术有限公司
     */
    public static function exportCSV($list, $columns = array(), $title = '', $path = '', $appName = '')
    {
        if (empty($path) && PHP_SAPI == 'cli') {
            throw new Exception('Excel::exportCSV  should only be run from a Web Browser');
        }

        $tableheader = [];
        foreach ($columns as $col) {
            $tableheader[] = $col['title'];
        }
        $tableheader_str = '"' . implode('","', $tableheader) . '"' . "\n";
        $html = iconv('UTF-8', 'GBK//TRANSLIT', $tableheader_str);
        foreach ($list as $value) {
            foreach ($columns as $col) {
                $type = '';
                if (isset($col['type']) && $col['type'] === 'string') {
                    $type = "\t";
                }
                $html .= '"' . iconv('UTF-8', 'GBK//TRANSLIT', $value[$col['field']]) . "{$type}\",";
            }
            $html .= "\n";
        }
        $filename = date('YmdHis', time());
        if (!empty($title)) {
            $filename = urlencode($title) . '-' . $filename;
        }
        //保存到本地
        if (!empty($path)) {
            $basePath = Yii::getAlias('@app/');
            $savepath = ltrim(rtrim($path, '/'), '/') . DIRECTORY_SEPARATOR . md5($filename) . '.csv';
            $filepath = $basePath . $savepath;
            $bytes = FileHelper::write($filepath, $html);
            if ($bytes) {
                return [
                    'filename' => $filename . '.csv',
                    'filepath' => $savepath
                ];
            }
            exit();
        }
        header("Content-type:text/csv");
        header("Content-Disposition:attachment; filename={$filename}.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $html;
        exit();
    }

    /**
     * 导出列表过滤重复字段
     * @param array $list
     * @param array $diffFileds 需要保留的字段
     * @param string $pk 主键
     * @return array
     * @author Jackie
     */
    public static function exportFilter(array $list, array $diffFileds, string $pk = 'id')
    {
        $data = $pkPool = [];
        foreach ($list as $order) {
            if (!in_array($order[$pk], $pkPool)) {
                $data[] = $order;
                $pkPool[] = $order[$pk];
            } else {
                $tmp = [];
                foreach ($order as $k => $v) {
                    $tmp[$k] = in_array($k, $diffFileds) ? $v : '';
                }
                $data[] = $tmp;
            }
        }

        return $data;
    }

    /**
     * 导入CSV文件解析
     * @param $excefile
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function importCsv($excefile)
    {
        $file = fopen($excefile, "r");

        $csv_array = [];
        while (!feof($file)) {
            $string = fgetcsv($file);
            if (empty($string)) {
                break;
            }
            foreach ($string as &$item) {
                $item = iconv('gbk', 'utf-8//TRANSLIT', $item);
                $item = trim($item);
            }
            unset($item);
            $csv_array[] = $string;

        }
        fclose($file);
        if (!empty($csv_array)) {
            $frist = array_shift($csv_array);
            $frist = array_map('strtolower', $frist);
            if (!empty($csv_array)) {
                array_walk($csv_array, function (&$val) use ($frist) {
                    $val = array_combine($frist, $val);
                });
            }
        }
        return $csv_array;
    }

    /**
     * 导入Excel 文件
     * @param $excefile
     * @return array
     */
    public static function import($excefile, $startRow = 2 ,$tmpPath = 'tmp/')
    {
        $tmpPath = Yii::getAlias('@webroot') . '/' . $tmpPath;
        if (!isset($_FILES[$excefile])) {
            return error('请选择要上传的Excel文件!');
        }

        $filename = $_FILES[$excefile]['name'];
        $tmpname = $_FILES[$excefile]['tmp_name'];
        if (empty($tmpname)) {
            return error('请选择要上传的Excel文件!');
        }
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext != 'xlsx' && $ext != 'xls') {
            return error('请上传 xls 或 xlsx 格式的Excel文件!');
        }

        $file = StringHelper::random(32, true) . "." . $ext;
        // 如果文件夹不存在 则创建
        if(!is_dir($tmpPath)){
            mkdir($tmpPath);
        }
        FileHelper::createDirectory($tmpPath);
        $uploadfile = $tmpPath . $file;
        $result = move_uploaded_file($tmpname, $uploadfile);
        if (!$result) {
            return error('上传Excel 文件失败, 请重新上传!');
        }

        return self::read($uploadfile, $ext, $startRow);
    }

    /**
     * 读取已上传文件
     * @param string $uploadfile
     * @param string $ext
     * @param bool $count
     * @return array|int
     * @author 青岛开店星信息技术有限公司
     */
    public static function read($uploadfile = '', $ext = '', $startRow = 2, $count = false)
    {
        if (!is_file($uploadfile)) {
            return error('文件不存在');
        }

        if (empty($ext)) {
            $ext = FileHelper::getExtension($uploadfile);
        }


        $reader = IOFactory::createReader($ext == 'xls' ? 'Xls' : 'Xlsx');//use excel2007 for 2007 format
        $excel = $reader->load($uploadfile);
        $sheet = $excel->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnCount = Coordinate::columnIndexFromString($highestColumn);
        $values = [];
        for ($row = $startRow; $row <= $highestRow; $row++) {
            $rowValue = [];
            for ($col = 1; $col <= $highestColumnCount; $col++) {
                $res = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                if (is_object($res)) {
                    $res = $res->__toString();
                }
                $rowValue[] = $res;

            }

            $values[] = $rowValue;
        }

        @unlink($uploadfile);

        if ($count) {
            return count($values);
        }

        return $values;
    }


    /**
     * 生成模板文件Excel
     * @param $title
     * @param array $columns
     * @param string $format
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public static function template($title, $columns = array(), string $format = '')
    {
        if (PHP_SAPI == 'cli') {
            throw new Exception('This example should only be run from a Web Browser');
        }

        $excel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $excel->getProperties()->setTitle("Office 2007 XLSX Test Document")->setSubject("Office 2007 XLSX Test Document")->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")->setKeywords("office 2007 openxml php")->setCategory("report file");
        $sheet = $excel->setActiveSheetIndex(0);
        //行数
        $rownum = 1;

        foreach ($columns as $key => $column) {
            $sheet->setCellValue(self::column($key, $rownum), $column['title']);
            if (!empty($column['width'])) {
                $sheet->getColumnDimension(self::column_str($key))->setWidth($column['width']);
            }

            //设置单元格格式
            if($format){
                $excel->getActiveSheet()->getStyle(self::column_str($key))->getNumberFormat()
                    ->setFormatCode($format);
            }

        }
        $rownum++;
        //数据
        $len = count($columns);;
        for ($k = 1; $k <= 5000; $k++) {
            for ($i = 0; $i < $len; $i++) {
                $sheet->setCellValue(self::column($i, $rownum), "");
            }
            $rownum++;
        }
        $excel->getActiveSheet()->setTitle($title);



        $filename = urlencode($title);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($excel, 'Xls');
        $writer->save('php://output');
        exit;
    }
}
