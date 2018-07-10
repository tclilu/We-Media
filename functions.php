<?php
/**
 * Created by PhpStorm.
 * User: LiLu
 * Date: 2018/7/10
 * Time: 14:26
 * 封装常用函数
 */
//引入配置文件
require_once $_SERVER['DOCUMENT_ROOT'] . './config.php';
//开启session
session_start();
/**
 * 获取当前登录用户信息
 * @return 如果当前已经登录则直接返回登录的用户信息
 */
function get_now_user(){
    if (!isset($_SESSION['now_user'])) {
        // 如果不存在当前用户，则跳转到登录页面
        header("Location: /admin/login.php");
        exit();
    }
    return $_SESSION['now_user'];
}

/**
 * @return bool 返回数据库连接对象
 */
function getConnection(){
    // 连接数据库
    $conn = @mysqli_connect(DB_HOST,DB_USER,DB_PWD,DB_NAME) or die("数据库连接失败！");
    return $conn;
}
/**
 * @param $sql 要执行查询的sql语句
 * @return array 将查询的结果集以二维数组形式返回
 */
function fetch_all($sql){
    $result = array();
    $conn = getConnection();
    // 执行sql语句
    $query_result = mysqli_query($conn,$sql);
    // 返回结果
    while($row = mysqli_fetch_assoc($query_result)){
        $result[] = $row;
    }
    // 释放资源、关闭连接
    close_mysql_conn($query_result,$conn);
    return $result;
}

/**
 * 获取一条数据
 * @param $sql 要执行的sql语句
 * @return array|null 如果查询到结果则返回第一行
 */
function fetch_one($sql){
    return isset(fetch_all($sql)[0]) ? fetch_all($sql)[0] : null;
}

/**
 * 执行增删改的方法
 * @param $sql 要执行的sql语句
 * @return int 返回受影响的行数
 */
function execute($sql){
    // 连接数据库
    $conn = getConnection();
    // 执行sql语句
    $query_result = mysqli_query($conn,$sql);
    // 获取受影响的行数
    $affected_rows = mysqli_affected_rows($conn);
    // 释放资源、关闭连接
    close_mysql_conn($query_result,$conn);
    return $affected_rows;
}

/**
 * 释放资源、关闭连接
 * @param $query_result 查询结果集
 * @param $conn 数据库连接对象
 */
function close_mysql_conn($query_result,$conn){
    // 释放资源、关闭连接
    mysqli_free_result($query_result);
    mysqli_close($conn);
}