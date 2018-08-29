<?php
namespace App\Http\Controllers\v1;

use App\Http\Models\Grade;
use App\Http\Models\Teacher;
use chineseFuture\ExceptionMessage;
use chineseFuture\ServerController;
use Illuminate\Http\Request;

class TeacherController extends ServerController
{
    /**
     * 本方法用于保存1条教师信息
     * @access public
     * @author 杨磊<40486453@qq.com>
     * @param \Illuminate\Http\Request 请求组件 实际参数为:
     * name string 教师姓名
     * mobile string 教师手机号
     * headImg string 教师头像
     * desc string 教师简介
     * grade string 教师可教授的年级
     * @return string $resultJson
    */
    public function addTeacher(Request $request)
    {
        // step1. 接收参数 验证空值 start

        $name = $request->post('name');
        $mobile = $request->post('mobile');
        $headImg = $request->post('headImg');
        $desc = $request->post('desc');
        $grade = $request->post('grade');

        $paramsIsNull = parent::checkParamIsNull($name, $mobile, $headImg, $desc, $grade);
        if(!$paramsIsNull)
        {
            $resultJson = ExceptionMessage::generateParamNullJson();
            return $resultJson;
        }

        // step1. 接收参数 验证空值 end

        // step2. 验证年级信息是否均在数据库中存在 start

        $gradeIdArr = parent::convertStrToArr(',', $grade);
        $gradeIdArr = parent::uniqueArr($gradeIdArr);
        if(!$gradeIdArr)
        {
            $data[]['file'] = __FILE__;
            $data[]['line'] = __LINE__;
            $resultJson = ExceptionMessage::generateInternalServerErrorJson($data);
            return $resultJson;
        }
        $checkGradeResult = self::checkGradeIds($gradeIdArr);
        if(!$checkGradeResult)
        {
            $resultJson = ExceptionMessage::generateGradeIdsInvalidJson();
            return $resultJson;
        }

        // step2. 验证年级信息是否均在数据库中存在 end

        // step3. 将教师信息存入数据库 start

        $teacherId = self::saveTeacherInfo($name, $mobile, $headImg, $desc, $grade);
        if(is_null($teacherId))
        {
            $teacherModel = new Teacher();
            $resultJson = ExceptionMessage::generateSaveFailJson($teacherModel->getTable());
            return $resultJson;
        }

        // step3. 将教师信息存入数据库 end

        // step4. 生成返回json start

        $data[]['teacherId'] = $teacherId;
        $resultJson = ExceptionMessage::generateSuccessJson($data);
        return $resultJson;

        // step4. 生成返回json end
    }

    /**
     * 本方法用于根据指定的年级id集合 验证信息是否均在数据库中存在
     * @access private
     * @author 杨磊<40486453@qq.com>
     * @param array $gradeIdArr 年级id数组
     * @return bool true表示均存在 false表示有不存在的信息id
    */
    private function checkGradeIds($gradeIdArr)
    {
        $gradeModel = new Grade();
        $gradeInfoNum = $gradeModel->countInfoByIdArrAndNameIsNotNull($gradeIdArr);
        $gradeIdNum = count($gradeIdArr);
        if($gradeInfoNum == $gradeIdNum)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 本方法用于根据教师姓名 教师手机号 教师头像 教师简介 教师可教授的年级 将教师信息存入数据库
     * @access public
     * @author 杨磊<40486453@qq.com>
     * @param string $name 教师姓名
     * @param string $mobile 教师手机号
     * @param string $headImg 教师头像
     * @param string $desc 教师简介
     * @param string $grade 教师可教授的年级
     * @return int|null $teacherId 存入成功返回教师id 失败返回null
    */
    public function saveTeacherInfo($name, $mobile, $headImg, $desc, $grade)
    {
        $model = new Teacher();
        $teacherId = $model->saveTeacherInfo($name, $mobile, $headImg, $desc, $grade);
        return $teacherId;
    }
}