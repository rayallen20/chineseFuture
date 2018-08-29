<?php
namespace App\Http\Controllers\v1;

use App\Http\Models\Subject;
use App\Http\Models\Teacher;
use chineseFuture\ExceptionMessage;
use chineseFuture\ServerController;
use Illuminate\Http\Request;

class SubjectController extends ServerController
{
    /**
     * 本方法用于添加1条科目信息
     * @access public
     * @author 杨磊 <40486453@qq.com>
     * @param \Illuminate\Http\Request $request 请求组件 实际参数为:
     * string $title 科目标题
     * string $kind 科目种类
     * string $teacherIds 任课教师id集合
     * string $desc 科目描述
     * string $subjectImg 科目宣传图片
     * int $subjectPrice 科目金额 单位:分
     * int $textbookPrice 教材费用 单位:分
     * int $stock 科目可售数量
     * int $lessonNum 该科目下的课程数量
     * string $isOnline 科目授课形式
     * @return string $resultJson
    */
    public function addSubject(Request $request)
    {
        // step1. 接受参数 验证字符串类型参数空值 start
        $title = $request->post('title');
        $kind = $request->post('kind');
        $teacherIds = $request->post('teacherIds');
        $desc = $request->post('desc');
        $subjectImg = $request->post('subjectImg');
        $subjectPrice = $request->post('subjectPrice');
        $textbookPrice = $request->post('textbookPrice');
        $subjectCode = $request->post('subjectCode');
        $stock = $request->post('stock');
        $lessonNum = $request->post('lessonNum');
        $isOnline = $request->post('isOnline');
        $strParamsIsNull = parent::checkParamIsNull($title, $desc, $subjectImg, $subjectCode);
        if(!$strParamsIsNull)
        {
            $resultJson = ExceptionMessage::generateParamNullJson();
            return $resultJson;
        }
        // step1. 接受参数 验证字符串类型参数空值 end

        // step2. 验证数字类型参数是否合法 start
        $numParamIsValid = parent::checkParamIsLessThanZero($subjectPrice, $textbookPrice, $stock, $lessonNum);
        if(!$numParamIsValid)
        {
            $resultJson = ExceptionMessage::generateNumParamLessThanZeroJson();
            return $resultJson;
        }
        // step2. 验证数字类型参数是否合法 end

        // step3. 验证枚举类型参数是否合法 start
        $enumParamIsValid = self::checkEnumParamIsValid($kind, $isOnline);
        if(!$enumParamIsValid)
        {
            $resultJson = ExceptionMessage::generateEnumerationParamInvalidJson();
            return $resultJson;
        }
        // step3. 验证枚举类型参数是否合法 end

        // step4. 验证教师id集合对应的信息是否在数据库中均存在 start
        $teacherIdArr = parent::convertStrToArr(',', $teacherIds);
        $teacherIdsIsValid = self::checkTeacherIdsIsValid($teacherIdArr);
        if(!$teacherIdsIsValid)
        {
            $resultJson = ExceptionMessage::generateTeacherIdsInvalidJson();
            return $resultJson;
        }
        // step4. 验证教师id集合对应的信息是否在数据库中均存在 end

        // step5. 将信息存入subject表 start
        $info['title'] = $title;
        $info['kind'] = $kind;
        $info['teacherIds'] = $teacherIds;
        $info['desc'] = $desc;
        $info['subjectImg'] = $subjectImg;
        $info['subjectPrice'] = $subjectPrice;
        $info['textbookPrice'] = $textbookPrice;
        $info['subjectCode'] = $subjectCode;
        $info['stock'] = $stock;
        $info['lessonNum'] = $lessonNum;
        $info['isOnline'] = $isOnline;
        $subjectModel = new Subject();
        $saveResult = $subjectModel->saveSubjectInfo($info);
        if(!$saveResult)
        {
            $resultJson = ExceptionMessage::generateSaveFailJson($subjectModel->getTable());
            return $resultJson;
        }

        $data[]['saveResult'] = 'success';
        $resultJson = ExceptionMessage::generateSuccessJson($data);
        return $resultJson;
        // step5. 将信息存入subject表 end
    }

    /**
     * 本方法用于检测枚举类型参数值 是否在其对应的数组中存在
     * @access private
     * @author 杨磊<40486453@qq.com>
     * @param string $kind 科目种类 该值应存在于Subject::KIND中
     * @param string $isOnline 科目授课形式 该值应该存在于Subject::IS_ONLINE中
     * @return bool true表示枚举值在对应数组中均存在 false表示有枚举值在对应数组中不存在
     *
    */
    private function checkEnumParamIsValid($kind, $isOnline)
    {
        if(!in_array($kind, Subject::KIND))
        {
            return false;
        }

        if(!in_array($isOnline, Subject::IS_ONLINE))
        {
            return false;
        }

        return true;
    }

    /**
     * 本方法用于检测教师id集合对应的信息是否在数据库中均存在
     * @access private
     * @author 杨磊 <40486453@qq.com>
     * @param array $teacherIdArr
     * @return bool true表示均存在 false表示有不存在的teacherId
    */
    private function checkTeacherIdsIsValid($teacherIdArr)
    {
        $model = new Teacher();
        $infoNum = $model->countInfoByIdArr($teacherIdArr);
        $teacherIdNum = count($teacherIdArr);
        if($infoNum == $teacherIdNum)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}