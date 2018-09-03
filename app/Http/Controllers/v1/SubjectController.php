<?php
namespace App\Http\Controllers\v1;

use App\Http\Models\Category;
use App\Http\Models\Grade;
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
        $gradeId = $request->post('gradeId');
        $categoryId = $request->post('categoryId');
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

        // step3. 验证年级id是否在数据库中存在 start

        $gradeIdIsValid = self::checkGradeIdValid($gradeId);
        if(!$gradeIdIsValid)
        {
            $resultJson = ExceptionMessage::generateGradeIdInvalidJson();
            return $resultJson;
        }

        // step3. 验证年级id是否在数据库中存在 end

        // step4. 验证课程种类id是否在数据库中存在 start

        $categoryIdIsValid = self::checkCategoryIdValid($categoryId);
        if(!$categoryIdIsValid)
        {
            $resultJson = ExceptionMessage::generateCategoryIdInvalidJson();
            return $resultJson;
        }

        // step4. 验证课程种类id是否在数据库中存在 end

        // step5. 验证教师id集合对应的信息是否在数据库中均存在 start
        $teacherIdArr = parent::convertStrToArr(',', $teacherIds);
        $teacherIdsIsValid = self::checkTeacherIdsIsValid($teacherIdArr);
        if(!$teacherIdsIsValid)
        {
            $resultJson = ExceptionMessage::generateTeacherIdsInvalidJson();
            return $resultJson;
        }
        // step5. 验证教师id集合对应的信息是否在数据库中均存在 end

        // step6. 验证isOnline字段值是否合法 start

        $isOnlineIsValid = self::checkIsOnlineValid($isOnline);
        if(!$isOnlineIsValid)
        {
            $resultJson = ExceptionMessage::generateIsOnlineInvalidJson();
            return $resultJson;
        }

        // step6. 验证isOnline字段值是否合法 end

        // step7. 将信息存入subject表 start
        $info['title'] = $title;
        $info['gradeId'] = $gradeId;
        $info['categoryId'] = $categoryId;
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
        // step7. 将信息存入subject表 end
    }



    /**
     * 本方法用于检测年级id是否在数据库中存在
     * @access private
     * @author 杨磊<40486453@qq.com>
     * @param int $gradeId 前端传入年级ID
     * @return bool true表示存在 false表示不存在
    */
    private function checkGradeIdValid($gradeId)
    {
        $gradeModel = new Grade();
        $allGradeIdArr = $gradeModel->findAllGradeId()->toArray();
        foreach ($allGradeIdArr as $key => $value)
        {
            if($value['id'] == $gradeId)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * 本方法用于检测课程种类id是否在数据库中存在
     * @access private
     * @author 杨磊<40486453@qq.com>
     * @param int $categoryId 前端传入种类ID
     * @return bool true表示存在 false表示不存在
    */
    private function checkCategoryIdValid($categoryId)
    {
        $categoryModel = new Category();
        $allCategoryIdArr = $categoryModel->findAllCategoryId()->toArray();
        foreach ($allCategoryIdArr as $key => $value)
        {
            if($value['id'] == $categoryId)
            {
                return true;
            }
        }
        return false;
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

    /**
     * 本方法用于检测isOnline字段值是否合法
     * @access private
     * @author 杨磊<40486453@qq.com>
     * @param string $isOnline
     * @return bool true表示合法 false表示不合法
    */
    private function checkIsOnlineValid($isOnline)
    {
        if(in_array($isOnline, Subject::IS_ONLINE))
        {
            return true;
        }
        return false;
    }
}