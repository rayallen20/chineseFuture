<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// 发送验证码
Route::post('/v1/user/sendVerificationCode', 'v1\UserController@sendVerificationCode');

// 密码注册登录
Route::post('/v1/user/registerByPassword', 'v1\UserController@registerByPassword');

// 短信验证码登录
Route::post('/v1/user/loginByMsgCode', 'v1\UserController@loginByMsgCode');

// 增加教师信息
Route::post('/v1/teacher/addTeacher', 'v1\TeacherController@addTeacher');

// 增加科目信息
Route::post('/v1/subject/addSubject', 'v1\SubjectController@addSubject');

// 添加课程时展示科目信息
Route::post('/v1/subject/showSubjectInfoOnAddLesson', 'v1\SubjectController@showSubjectInfoOnAddLesson');

// 添加课程信息
Route::post('/v1/lessonOffline/addLessonOffline', 'v1\LessonOfflineController@addLessonOffline');