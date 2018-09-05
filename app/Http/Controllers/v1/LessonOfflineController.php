<?php
namespace App\Http\Controllers\v1;

use App\Http\Models\LessonOffline;
use App\Http\Models\Subject;
use chineseFuture\ExceptionMessage;
use chineseFuture\ServerController;
use Illuminate\Http\Request;

class LessonOfflineController extends ServerController
{
    /**
     * 本方法用于添加课程至指定科目下
     * @access public
     * @author 杨磊<40486453@qq.com>
     * @param \Illuminate\Http\Request $request 请求组件 实际参数为:
     * int $userId 用户ID
     * string $token token
     * int $subjectId 科目ID
     * string $titles 课程标题集合 格式: 课程标题1,课程标题2,...课程标题N
     * string $teacherIds 教师ID集合 格式: 教师id1,教师id2,...教师idN
     * string $prices 课程价格集合 格式: 课程价格1,课程价格2,...课程价格N
     * string $startTimes 上课时间集合 格式:上课时间1,上课时间2,...上课时间N
     * string $endTimes 下课时间集合 格式:下课时间1,下课时间2,...下课时间N
     * string $descs 课程简介集合 格式:课程简介1,课程简介2,...课程简介N
     * @return string $resultJson
    */
    public function addLessonOffline(Request $request)
    {
        // step1. 接收参数 验证空值 鉴权 start
        $userId = (int)$request->post('userId');
        $token = $request->post('token');
        $subjectId = $request->post('subjectId');
        $titles = $request->post('titles');
        $teacherIds = $request->post('teacherIds');
        $prices = $request->post('prices');
        $startTimes = $request->post('startTimes');
        $endTimes = $request->post('endTimes');
        $descs = $request->post('descs');

        $paramIsNull = parent::checkParamIsNull($userId, $token, $subjectId, $titles,
            $teacherIds, $prices, $startTimes, $endTimes, $descs);
        if(!$paramIsNull)
        {
            $resultJson = ExceptionMessage::generateParamNullJson();
            return $resultJson;
        }

        $checkTokenResult = parent::checkUserIdAndToken($userId, $token);
        if(!$checkTokenResult)
        {
            $resultJson = ExceptionMessage::generateTokenInvalidJson();
            return $resultJson;
        }
        // step1. 接收参数 验证空值 鉴权 end

        // step2. 将集合类信息转化为数组 start
        $collectionArr = self::convertMultiStringToCollectionArr($titles, $teacherIds, $prices, $startTimes, $endTimes, $descs);
        if(is_null($collectionArr))
        {
            $resultJson = ExceptionMessage::generateConvertStringToArrFailJson();
            return $resultJson;
        }
        list($titleArr, $teacherIdArr, $priceArr, $startTimeArr, $endTimeArr, $descArr) = array_values($collectionArr);
        // step2. 将集合类信息转化为数组 end

        // step3. 检测标题数组 教师ID数组 价格数组 上下课时间数组中 是否存在空值 start
        $emptyStrIsExist = parent::checkEmptyStringIsExistInArr($titleArr, $teacherIdArr, $priceArr, $startTimeArr, $endTimeArr);
        if(!$emptyStrIsExist)
        {
            $resultJson = ExceptionMessage::generateEmptyStringExistInCollectionJson();
            return $resultJson;
        }
        // step3. 检测标题数组 教师ID数组 价格数组 上下课时间数组中 是否存在空值 end

        // step4. 查询课程信息 start
        $lessonNumAndSubjectPrice = self::findLessonNumAndPrice($subjectId);
        if(is_null($lessonNumAndSubjectPrice))
        {
            $resultJson = ExceptionMessage::generateSubjectNotExistJson();
            return $resultJson;
        }
        list($lessonNum, $subjectPrice) = array_values($lessonNumAndSubjectPrice);
        // step4. 查询课程信息 end

        // step5. 检查课程的各项信息数组长度是否等于科目下的课程数量 start
        $lengthIsValid = parent::checkMultiArrayLength($lessonNum, $titleArr, $teacherIdArr, $priceArr, $startTimeArr, $endTimeArr, $descArr);
        if(!$lengthIsValid)
        {
            $resultJson = ExceptionMessage::generateElementNumberIncorrectJson();
            return $resultJson;
        }
        // step5. 检查课程的各项信息数组长度是否等于科目下的课程数量 end

        // step6. 检测各个课程的价格之和是否等于科目价格 start
        $sumLessonPriceIsEqualSubjectPrice = self::checkSumLessonPriceIsEqualSubjectPrice($priceArr, $subjectPrice);
        if(!$sumLessonPriceIsEqualSubjectPrice)
        {
            $resultJson = ExceptionMessage::generateSumLessonPriceIsNotEqualSubjectPriceJson();
            return $resultJson;
        }
        // step6. 检测各个课程的价格之和是否等于科目价格 end

        // step7. 检测上下课时间是否为Y-m-d H:i:s格式的时间 start
        $startAndEndTimeStamp = self::convertStartTimeAndEndTime($startTimeArr, $endTimeArr);
        if(is_null($startAndEndTimeStamp))
        {
            $resultJson = ExceptionMessage::generateDateTimeIsInvalidJson();
            return $resultJson;
        }
        list($startTimeStampArr, $endTimeStampArr) = array_values($startAndEndTimeStamp);
        // step7. 检测上下课时间是否为Y-m-d H:i:s格式的时间 end

        // step8. 检测上下课时间是否早于当前时间 start
        $startAndEndTimeIsBeforeThanNow = self::checkStartAndEndTimeStampIsBeforeThanNow($startTimeStampArr, $endTimeStampArr);
        if(!$startAndEndTimeIsBeforeThanNow)
        {
            $resultJson = ExceptionMessage::generateStartOrEndTimeInvalidJson();
            return $resultJson;
        }
        // step8. 检测上下课时间是否早于当前时间 end

        // step9. 检测各个课程的下课时间是否早于上课时间 start
        $endTimeIsBeforeThanStartTime = self::checkEndTimeStampIsBeforeThanStartTimeStamp($startTimeStampArr, $endTimeStampArr);
        if(!$endTimeIsBeforeThanStartTime)
        {
            $resultJson = ExceptionMessage::generateEndTimeBeforeThanStartTimeJson();
            return $resultJson;
        }
        // step9. 检测各个课程的下课时间是否早于上课时间 end

        // step10. 将各个课程的科目ID 标题 授课教师id 价格 上/下课时间 课程简介 封装成待保存状态 start
        $lessonsOffline = self::assembleLessonOfflineInfo($subjectId, $titleArr, $teacherIdArr, $priceArr, $startTimeArr, $endTimeArr, $descArr);
        // step10. 将各个课程的标题 授课教师id 价格 上/下课时间 课程简介 封装成待保存状态 end

        // step11. 检测各个课程之间的上课时间段是否存在重叠 start
        $timeSlotIsOverlap = self::checkTimeSlotIsOverlap($lessonsOffline);
        if(!$timeSlotIsOverlap)
        {
            $resultJson = ExceptionMessage::generateTimeSlotHasOverlapJson();
            return $resultJson;
        }
        // step11. 检测各个课程之间的上课时间段是否存在重叠 end

        // step12. 将各个课程信息按照上课时间排序(升序) start
        $afterSortLessonsOffline = self::sortLessonsOfflineByStartTime($lessonsOffline);
        // step12. 将各个课程信息按照上课时间排序(升序) end

        // step12. 保存课程信息并更新科目上下课时间信息 start
        $lessonOfflineModel = new LessonOffline();
        try
        {
            $lessonOfflineModel->saveLessonsInfoAndUpdateSubjectInfo($subjectId, $afterSortLessonsOffline);
        } catch (\Exception $e)
        {
            $resultJson = ExceptionMessage::generateTransactionFailedJson();
            return $resultJson;
        }
        // step12. 保存课程信息并更新科目上下课时间信息 end

        $data[]['saveResult'] = true;
        $resultJson = ExceptionMessage::generateSuccessJson($data);
        return $resultJson;

    }

    /**
     * 本方法用于将 课程标题集合 教师ID集合 课程价格集合 上课时间集合 下课时间集合 课程简介集合 转化为数组
     * @access private
     * @author 杨磊<40486453@qq.com>
     * @param string $titles 课程标题集合
     * @param string $teacherIds 教师ID集合
     * @param string $prices 课程价格集合
     * @param string $startTimes 上课时间集合
     * @param string $endTimes 下课时间集合
     * @param string $descs 课程简介集合
     * @return array|null $collectionArr 各个集合转化为数组后的集合数组
    */
    private function convertMultiStringToCollectionArr(string $titles, string $teacherIds, string $prices, string $startTimes, string $endTimes, string $descs) :?array
    {
        $titleArr = parent::convertStrToArr(',', $titles);
        if(!$titleArr)
        {
            return null;
        }

        $teacherIdArr = parent::convertStrToArr(',', $teacherIds);
        if(!$teacherIdArr)
        {
            return null;
        }

        $priceArr = parent::convertStrToArr(',', $prices);
        if(!$priceArr)
        {
            return null;
        }

        $startTimeArr = parent::convertStrToArr(',', $startTimes);
        if(!$startTimeArr)
        {
            return null;
        }

        $endTimeArr = parent::convertStrToArr(',', $endTimes);
        if(!$endTimeArr)
        {
            return null;
        }

        $descArr = parent::convertStrToArr(',', $descs);
        if(!$descArr)
        {
            return null;
        }

        $collectionArr['title'] = $titleArr;
        $collectionArr['teacherId'] = $teacherIdArr;
        $collectionArr['price'] = $priceArr;
        $collectionArr['startTime'] = $startTimeArr;
        $collectionArr['endTime'] = $endTimeArr;
        $collectionArr['desc'] = $descArr;

        return $collectionArr;
    }

    /**
     * 本方法用于根据subjectId查询科目的课程数和价格信息
     * @access private
     * @author 杨磊<40486453@qq.com>
     * @param int $subjectId 科目ID
     * @return array|null 查询成功返回包含科目下的课程数和价格的数组 失败返回null
    */
    private function findLessonNumAndPrice(int $subjectId) :?array
    {
        $subjectModel = new Subject();
        $subjectInfo = $subjectModel->findInfoById($subjectId);
        if(is_null($subjectInfo))
        {
            return null;
        }

        $arr['lessNum'] = $subjectInfo->lesson_num;
        $arr['subjectPrice'] = $subjectInfo->subject_price;
        return $arr;
    }

    /**
     * 本方法用于检测各个课程的价格之和是否等于科目价格
     * @access private
     * @author 杨磊<40486453@qq.com>
     * @param array $lessonPriceArr 各个课程的价格数组
     * @param int $subjectPrice 科目价格
     * @return bool true表示相等 false表示不等
    */
    private function checkSumLessonPriceIsEqualSubjectPrice(array $lessonPriceArr, int $subjectPrice) :bool
    {
        $sumLessonPrice = 0;
        foreach ($lessonPriceArr as $lessonPrice)
        {
            $sumLessonPrice += $lessonPrice;
        }

        if($sumLessonPrice != $subjectPrice)
        {
            return false;
        }
        return true;
    }

    /**
     * 本方法用于将上/下课时间数组中的字符串日期时间转化为时间戳
     * @access private
     * @author 杨磊<40486453@qq.com>
     * @param array $startTimeArr 上课时间数组
     * @param array $endTimeArr 下课时间数组
     * @return array|null $timeStampArr 该数组包含2项:转化后的上课时间戳数组和下课时间戳数组 如果存在无法转化的字符串 则返回null
    */
    private function convertStartTimeAndEndTime(array $startTimeArr, array $endTimeArr) :?array
    {
        $startTimeStampArr = [];
        $endTimeStampArr = [];
        foreach ($startTimeArr as $startTime)
        {
            $startTimeStamp = parent::convertDateTimeToTimeStamp($startTime);
            if(is_null($startTimeStamp))
            {
                return null;
            }
            $startTimeStampArr[] = $startTimeStamp;
        }

        foreach ($endTimeArr as $endTime)
        {
            $endTimeStamp = parent::convertDateTimeToTimeStamp($endTime);
            if(is_null($endTimeStamp))
            {
                return null;
            }
            $endTimeStampArr[] = $endTimeStamp;
        }

        $timeStampArr['startTimeStamp'] = $startTimeStampArr;
        $timeStampArr['endTimeStamp'] = $endTimeStampArr;
        return $timeStampArr;
    }

    /**
     * 本方法用于检测上下课时间是否早于当前时间
     * @access private
     * @author 杨磊<40486453@qq.com>
     * @param array $startTimeStampArr 上课时间的时间戳数组
     * @param array $endTimeStampArr 下课时间的时间戳数组
     * @return bool true表示不存在早于当前时间的时间戳 false表示存在早于当前时间的时间戳
    */
    private function checkStartAndEndTimeStampIsBeforeThanNow(array $startTimeStampArr, array $endTimeStampArr) :bool
    {
        $nowTimeStamp = parent::getNowTimeStamp();
        foreach ($startTimeStampArr as $startTimeStamp)
        {
            if($startTimeStamp < $nowTimeStamp)
            {
                return false;
            }
        }

        foreach ($endTimeStampArr as $endTimeStamp)
        {
            if($endTimeStamp < $nowTimeStamp)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * 本方法用于检测各个课程的下课时间是否早于上课时间
     * @access private
     * @author 杨磊<40486453@qq.com>
     * @param array $startTimeStampArr 上课时间的时间戳数组
     * @param array $endTimeStampArr 下课时间的时间戳数组
     * @return bool true表示存在下课时间早于上课时间的课程 false表示不存在
    */
    private function checkEndTimeStampIsBeforeThanStartTimeStamp(array $startTimeStampArr, array $endTimeStampArr) :bool
    {
        foreach ($endTimeStampArr as $key => $endTimeStamp)
        {
            $startTimeStamp = $startTimeStampArr[$key];
            if($endTimeStamp <= $startTimeStamp)
            {
                return false;
            }
        }
        return true;
    }

    /**
     * 本方法用于根据各个课程的科目ID 标题 授课教师id 价格 上/下课时间 课程简介 封装为待保存状态
     * @access private
     * @author 杨磊<40486453@qq.com>
     * @param int $subjectId 科目信息ID
     * @param array $titleArr 各个课程的标题数组
     * @param array $teacherIdArr 各个课程的授课教师ID数组
     * @param array $priceArr 各个课程的价格数组
     * @param array $startTimeArr 各个课程的上课时间数组
     * @param array $endTimeArr 各个课程的下课时间数组
     * @param array $descArr 各个课程的课程简介数组
     * @return array $lessonsOffline 待保存状态的课程数组
    */
    private function assembleLessonOfflineInfo(int $subjectId, array $titleArr, array $teacherIdArr, array $priceArr, array $startTimeArr, array $endTimeArr, array $descArr) :array
    {
        $lessonsOffline = [];
        foreach ($titleArr as $key => $title)
        {
            $lessonsOffline[$key]['subject_id'] = $subjectId;
            $lessonsOffline[$key]['title'] = $title;
            $lessonsOffline[$key]['teacher_id'] = $teacherIdArr[$key];
            $lessonsOffline[$key]['price'] = $priceArr[$key];
            $lessonsOffline[$key]['start_time'] = $startTimeArr[$key];
            $lessonsOffline[$key]['end_time'] = $endTimeArr[$key];
            $lessonsOffline[$key]['duration'] = (parent::convertDateTimeToTimeStamp($endTimeArr[$key]) - parent::convertDateTimeToTimeStamp($startTimeArr[$key])) / 60;
            $lessonsOffline[$key]['desc'] = $descArr[$key];
            $lessonsOffline[$key]['state'] = LessonOffline::STATE['notStart'];
        }
        return $lessonsOffline;
    }

    /**
     * 本方法用于检测各个课程之间的上课时间段是否存在重叠
     * 检测标准:对于任意一个上课时间T,如果课程数组中存在这样的一个时间段S1,使得:
     * S1的上课时间 < T 且 S1的下课时间 > T 则判定为时间段存在重叠 否则判定为时间段不存在重叠
     * @access private
     * @author 杨磊<40486453@qq.com>
     * @param array $lessonsOffline 线下课课程信息数组
     * @return bool true表示时间段不存在重叠 false表示时间段存在重叠
    */
    private function checkTimeSlotIsOverlap(array $lessonsOffline) :bool
    {
        foreach ($lessonsOffline as $presentKey => $presentLesson)
        {
            foreach ($lessonsOffline as $comparisonKey => $comparisonLesson)
            {
                if($comparisonKey == $presentKey)
                {
                    break;
                }
                $presentStartTimeStamp = self::convertDateTimeToTimeStamp($presentLesson['start_time']);
                $comparisonStartTimeStamp = self::convertDateTimeToTimeStamp($comparisonLesson['start_time']);
                $comparisonEndTimeStamp = self::convertDateTimeToTimeStamp($comparisonLesson['end_time']);
                if($comparisonStartTimeStamp <= $presentStartTimeStamp && $comparisonEndTimeStamp >= $presentStartTimeStamp)
                {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 本方法用于根据各个课程的上课时间排序(升序)
     * @access private
     * @author 杨磊<40486453@qq.com>
     * @param array $lessonsOffline 排序前的课程信息集合
     * @return array $afterSortLessonsOffline 排序后的课程信息集合
    */
    private function sortLessonsOfflineByStartTime(array $lessonsOffline) :array
    {
        // 基线条件
        if(count($lessonsOffline) < 2)
        {
            return $lessonsOffline;
        }

        // 递归条件
        $pivot = $lessonsOffline[0];
        $lessArr = [];
        $greatArr = [];
        for ($i = 1; $i < count($lessonsOffline); $i++)
        {
            if(strtotime($lessonsOffline[$i]['start_time']) <= strtotime($pivot['start_time']))
            {
                array_push($lessArr, $lessonsOffline[$i]);
            }
            else
            {
                array_push($greatArr, $lessonsOffline[$i]);
            }
        }

        $afterSortLessonsOffline = array_merge(self::sortLessonsOfflineByStartTime($lessArr), [$pivot], self::sortLessonsOfflineByStartTime($greatArr));
        return $afterSortLessonsOffline;
    }
}