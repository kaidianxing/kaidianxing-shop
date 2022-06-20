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
 * 日期时间助手类
 * Class DateTime
 * @package shopstar\helpers
 * @author 青岛开店星信息技术有限公司
 */
class DateTimeHelper
{
    /**
     * 默认时间
     */
    public const DEFAULT_DATE_TIME = '0000-00-00 00:00:00';

    /**
     * 获取当前时间
     * @param bool $time
     * @return false|string
     * @author likexin
     */
    public static function now(bool $time = true)
    {
        return $time ? date('Y-m-d H:i:s') : date('Y-m-d');
    }

    /**
     * 时间转日期
     * @param int $time 时间
     * @return false|string
     * @author likexin
     */
    public static function time2date(int $time)
    {
        return date('Y-m-d H:i:s', $time);
    }

    /**
     * 获取年份
     * @param int $long
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    static function getYears(int $long = 10): array
    {

        $years = [];
        $thisyear = intval(date('Y'));
        for ($i = $thisyear;$i >= $thisyear - $long;$i--) {
            $years[] = $i;
        }
        return $years;

    }

    /**
     * 获取月份
     * @param bool $full 是否补足0
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMonths(bool $full): array
    {
        $months = [];
        for ($i = 1;$i <= 12;$i++) {
            $months[] = ($full && strlen($i) <= 1 ? '0' : '') . $i;
        }
        return $months;
    }

    /**
     * 获取小时
     * @param bool $full 是否补足0
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getHours(bool $full): array
    {
        $hours = [];
        for ($i = 0;$i <= 23;$i++) {
            $hours[] = ($full && strlen($i) <= 1 ? '0' : '') . $i;
        }
        return $hours;
    }

    /**
     * 获取分钟
     * @param bool $full 是否补足0
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMintues(bool $full): array
    {
        $mintues = [];
        for ($i = 0;$i <= 59;$i++) {
            $mintues[] = ($full && strlen($i) <= 1 ? '0' : '') . $i;
        }
        return $mintues;
    }

    /**
     * 时间戳转换时间格式
     * @param int $timestamp 时间戳
     * @param string $tag d 只日期 m 到分钟 s 到秒
     * @return string 时间
     * @author 青岛开店星信息技术有限公司
     */
    static function getString(int $timestamp = 0, string $tag = 'd'): string
    {
        if (empty($timestamp)) {
            return '';
        }
        if ($tag == 'd') {
            return date("Y-m-d", $timestamp);
        } else if ($tag == 'i') {
            return date("Y-m-d H:i", $timestamp);
        } else {
            return date("Y-m-d H:i:s", $timestamp);
        }
    }

    /**
     * 两个时间相差几天
     * @param string $date1 时间字符串1
     * @param string $date2 时间字符串2
     * @return int 天数
     * @author 青岛开店星信息技术有限公司
     */
    static function days(string $date1, string $date2): int
    {
        return abs(ceil((strtotime($date1) - strtotime($date2)) / 86400));
    }

    /**
     * 时间差 几天前 几分钟前
     * @param string $time 时间timestamp
     * @return string 相差的时间文字描述
     * @author 青岛开店星信息技术有限公司
     */
    static function before(string $time): string
    {
        $now_time = time();
        $dur = $now_time - (strstr($time, '-') ? strtotime($time) : $time);
        if ($dur < 0) {
            return $time;
        } else {
            if ($dur < 60) {
                return '刚刚';
            } else {
                if ($dur < 3600) {
                    return floor($dur / 60) . '分钟前';
                } else {
                    if ($dur < 86400) {
                        return floor($dur / 3600) . '小时前';
                    } else {
                        if ($dur < 259200) {//3天内
                            return floor($dur / 86400) . '天前';
                        } else {
                            return date('m-d', $time);
                        }
                    }
                }
            }
        }
    }

    /**
     * 获取月份的最大天数
     * @param string $year
     * @param string $month
     * @return false|string
     * * @author 青岛开店星信息技术有限公司
     */
    static function getMaxDay(string $year, string $month)
    {
        return date('t', strtotime("{$year}-{$month} -1"));
    }

    /**
     * 获取某个周的开始日期结束日期
     * @param $year
     * @param int $week 0 获取所有周
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    static function getWeek($year, $week = 0): array
    {

        $year_start = $year . "-01-01";
        $year_end = $year . "-12-31";
        $startday = strtotime($year_start);
        if (intval(date('N', $startday)) != '1') {
            $startday = strtotime("next monday", strtotime($year_start)); //获取年第一周的日期
        }
        $year_mondy = date("Y-m-d", $startday); //获取年第一周的日期

        $endday = strtotime($year_end);
        if (intval(date('W', $endday)) == '7') {
            $endday = strtotime("last sunday", strtotime($year_end));
        }

        if ($week > 0) {
            $j = $week - 1;
            $start = date("Y-m-d", strtotime("$year_mondy $j week "));
            $end = date("Y-m-d", strtotime("$start +6 day"));
            return [$start, $end];
        }

        $num = intval(date('W', $endday));
        for ($i = 1;$i <= $num;$i++) {
            $j = $i - 1;

            $start = date('Y-m-d', strtotime("$year_mondy $j week "));
            $end = date("Y-m-d", strtotime("$start +6 day"));
            $week_array[$i] = [$start, $end];
        }
        return $week_array;

    }

    /**
     * 获取某个周的开始日期结束日期
     * @param $year
     * @param $week_num
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getWeekDate($year, $week_num): array
    {
        $first_day_of_year = mktime(0, 0, 0, 1, 1, $year);
        $first_week_day = date('N', $first_day_of_year);
        $first_week_num = date('W', $first_day_of_year);
        if ($first_week_num == 1) {
            $day = (1 - ($first_week_day - 1)) + 7 * ($week_num - 1);
            $start_date = date('Y-m-d', mktime(0, 0, 0, 1, $day, $year));
            $end_date = date('Y-m-d', mktime(0, 0, 0, 1, $day + 6, $year));
        } else {
            $day = (9 - $first_week_day) + 7 * ($week_num - 1);
            $start_date = date('Y-m-d', mktime(0, 0, 0, 1, $day, $year));
            $end_date = date('Y-m-d', mktime(0, 0, 0, 1, $day + 6, $year));
        }

        return array ($start_date, $end_date);
    }

    /**
     *  加上多少时间
     * @param int/string $startTime 开始时间
     * @param int $timeStamp 增加的时间戳
     * @param bool $isTimeStamp 是否返回时间戳
     * @return false|int|string
     * @author 青岛开店星信息技术有限公司
     */
    static function after($startTime, int $timeStamp, bool $isTimeStamp)
    {
        if (!is_numeric($startTime)) {
            $startTime = strtotime($startTime);
        }
        $result = $startTime + $timeStamp;
        return $isTimeStamp ? $result : date('Y-m-d H:i:s', $result);
    }

    /**
     * 时间差 几天后 几小时后 几分钟后
     * @param string $createTime
     * @param string $theTime
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    static function afterFormat(string $createTime, string $theTime): string
    {
        if (empty($theTime)) {
            return '';
        }

        $dur = (StringHelper::exists($theTime, '-') ? strtotime($theTime) : $theTime) - strtotime($createTime);
        if ($dur < 0) {
            return "";
        }

        if ($dur < 60) {
            return $dur . '秒';
        } else {
            if ($dur < 3600) {
                return floor($dur / 60) . '分钟';
            } else {
                if ($dur < 86400) {
                    $h = floor($dur / 3600);
                    $i = min(floor(($dur - $h * 3600) / 60), 60);
                    return $h . '小时' . $i . '分钟';
                } else {
                    $d = floor($dur / 86400);
                    $h = min(floor(($dur - $d * 86400) / 3600), 24);
                    $i = min(floor(($dur - ($d * 86400 + $h * 60)) / 60), 60);

                    return $d . '天' . $h . '小时' . $i . '分钟';
                }
            }
        }
    }

    /**
     * 获取当前毫秒时间戳
     * @return float
     * @author 青岛开店星信息技术有限公司
     */
    static function microtime(): float
    {
        list($msec, $sec) = explode(' ', microtime());

        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);

        return $msectime;
    }

    /**
     * 获取指定日期段内每一天的日期
     * @param string $startDate
     * @param string $endDate
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getDateRange(string $startDate, string $endDate): array
    {
        $sTimestamp = strtotime($startDate);
        $eTimestamp = strtotime($endDate);
        $days = ($eTimestamp - $sTimestamp) / 86400 + 1;
        $date = array ();
        for ($i = 0;$i < $days;$i++) {
            $date[] = date('Y-m-d', $sTimestamp + (86400 * $i));
        }
        return $date;
    }

    /**
     * 获取自然月开始结束时间
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMonthDate(): array
    {
        $time = time();
        $startTime = mktime(0, 0, 0, date("m", $time), 1, date("Y", $time));
        $endTime = mktime(0, 0, 0, date("m", $time), date("t", $startTime) + 1, date("Y", $time));

        return [date('Y-m-d H:i:s', $startTime), date('Y-m-d H:i:s', $endTime)];
    }

    /**
     * 获取随机日期
     * @author 青岛开店星信息技术有限公司
     */
    public static function getRandDate(string $startTime, string $endTime)
    {
        // 转换成时间戳格式
        $start = strtotime($startTime);
        $end = strtotime($endTime);

        // 计算时间间隔
        $difference = $end - $start;
        // 随机
        $rand = rand(0, $difference);

        return date('Y-m-d H:i:s', $start + $rand);
    }

    /**
     * 获取日期(年月日)带分页
     * @param int $page
     * @param int $timeType
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getDateAsPage(int $page = 0, int $timeType = 1): array
    {

        //算出来一年的时间
        $now = date('Y-m-d', time());

        $nextyear = mktime(0, 0, 0, date("m"), date("d"), date("Y") + 1);

        $next = date('Y-m-d', $nextyear);

        $allDate = DateTimeHelper::getDateRange($now, $next);

        //判断可售日期
        $showDates = [];
        if ($timeType == 1) {

            $showDates = $allDate;
        }


        //定义今天
        $today = date('Y-m-d', time());
        //定义明天的日期
        $afterDay = date('Y-m-d', DateTimeHelper::after(DateTimeHelper::now(), 86400, true));
        //分页，每次七条
        $pageSize = 7;

        $page = max(1, $page);

        $pageCount = ceil(count($showDates) / 7);
        //根据页数切割出需要返回的七条数据
        $showDates = array_slice($showDates, ($page - 1) * $pageSize, $pageSize);

        $weekMap = [
            '1' => '周一',
            '2' => '周二',
            '3' => '周三',
            '4' => '周四',
            '5' => '周五',
            '6' => '周六',
            '0' => '周日',
        ];
        $dateInfo = [];
        foreach ($showDates as $k => $v) {
            if ($v == $today) {

                $text = '今天';

            } elseif ($v == $afterDay) {
                $text = '明天';
            } else {
                $text = $weekMap[date('w', strtotime($v))];
            }

            $dateInfo[] = [
                'text' => $text,
                'date' => date('m-d', strtotime($v)),
                'timestemp' => date('Y-m-d', strtotime($v)),
            ];

        }

        return ['date' => $dateInfo, 'page' => $pageCount, 'time' => DateTimeHelper::now()];
    }

}
