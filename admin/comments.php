<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
get_now_user();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Comments &laquo; Admin</title>
  <link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="/static/assets/vendors/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" href="/static/assets/vendors/nprogress/nprogress.css">
  <link rel="stylesheet" href="/static/assets/css/admin.css">
  <link rel="stylesheet" href="/static/assets/css/loading.css">
  <script src="/static/assets/vendors/nprogress/nprogress.js"></script>
</head>
<body>
  <script>NProgress.start()</script>

  <div class="main">
    <?php include "./common/navbar.php";?>
    <div class="container-fluid">
      <div class="page-title">
        <h1>所有评论</h1>
      </div>
      <!-- 有错误信息时展示 -->
      <!-- <div class="alert alert-danger">
        <strong>错误！</strong>发生XXX错误
      </div> -->
      <div class="page-action">
        <!-- show when multiple checked -->
        <div class="btn-batch" style="display: none">
          <button class="btn btn-info btn-sm">批量批准</button>
          <button class="btn btn-warning btn-sm">批量拒绝</button>
          <button class="btn btn-danger btn-sm">批量删除</button>
        </div>
        <ul class="pagination pagination-sm pull-right"></ul>
      </div>
      <table class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th class="text-center" width="40"><input type="checkbox"></th>
            <th class="text-center">作者</th>
            <th class="text-center" width="600">评论</th>
            <th class="text-center">评论在</th>
            <th class="text-center">提交于</th>
            <th class="text-center">状态</th>
            <th class="text-center" width="100">操作</th>
          </tr>
        </thead>
        <tbody>
<!--          <tr class="danger">-->
<!--            <td class="text-center"><input type="checkbox"></td>-->
<!--            <td>大大</td>-->
<!--            <td>楼主好人，顶一个</td>-->
<!--            <td>《Hello world》</td>-->
<!--            <td>2016/10/07</td>-->
<!--            <td>未批准</td>-->
<!--            <td class="text-center">-->
<!--              <a href="post-add.php" class="btn btn-info btn-xs">批准</a>-->
<!--              <a href="javascript:;" class="btn btn-danger btn-xs">删除</a>-->
<!--            </td>-->
<!--          </tr>-->
        </tbody>
      </table>
    </div>
  </div>
  <!--加载层-->
  <div id="loading">
      <div class="multi-ball">
          <div></div>
          <div></div>
          <div></div>
          <div></div>
          <div></div>
      </div>
  </div>
  <?php include "./common/aside.php";?>
  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <!--引入分页插件-->
  <script src="/static/assets/vendors/twbs-pagination/jquery.twbsPagination.min.js"></script>
  <!--引入前端模板引擎-->
  <script src="/static/assets/vendors/jsrender/jsrender.min.js"></script>
  <script id="comments_tmpl" type="text/x-jsrender">
    {{for comments}}
      <tr {{if comment_status == 'held'}} class="warning" {{else comment_status == 'rejected'}} class="danger" {{/if}} data-id={{:id}}>
        <td class="text-center"><input type="checkbox"></td>
        <td class="text-center">{{:author}}</td>
        <td class="text-center">{{:content}}</td>
        <td class="text-center">{{:post_title}}</td>
        <td class="text-center">{{:comment_time}}</td>
        <td class="text-center">{{if comment_status == 'held'}}待审核{{else comment_status == 'rejected'}}已拒绝{{else comment_status == 'approved'}}已通过{{else comment_status == 'trashed'}}回收站{{/if}}</td>
        <td class="text-center">
            {{if comment_status == 'held'}}
            <a href="javascript:;" class="btn btn-info btn-xs btn-approval">批准</a>
            <a href="javascript:;" class="btn btn-danger btn-xs btn-deny">拒绝</a>
            {{else comment_status == 'rejected'}}
            <a href="javascript:;" class="btn btn-warning btn-xs btn-permission">准许</a>
            <a href="javascript:;" class="btn btn-danger btn-xs btn-delete">删除</a>
            {{else comment_status == 'trashed'}}
            <a href="javascript:;" class="btn btn-warning btn-xs btn-back">还原</a>
            {{else comment_status != 'trashed'}}
            <a href="javascript:;" class="btn btn-danger btn-xs btn-delete">删除</a>
            {{/if}}
        </td>
      </tr>
    {{/for}}
  </script>
  <script>
      // // nprogress
      // Ajax开始和结束的时候
      $(document)
      	.ajaxStart(function () {
      		NProgress.start();
            $("#loading").css("display","flex");
      	})
      	.ajaxStop(function () {
      		NProgress.done();
            $("#loading").css("display","none");
      	});
      $(function () {
          // 当前页
          var current_page = 1;
          // 获取第一页的评论数据
          getComments(current_page);
          /**
           * 获取当前页评论数据
           * @param page
           */
          function getComments(page){
              $("tbody").fadeOut();
              $.get("/admin/api/commentsAPI.php?cp=" + page,function (response) {
                  $(".pagination").twbsPagination({
                      first:"&laquo;",
                      last:"&raquo;",
                      prev:"&lt;",
                      next:"&gt;",
                      totalPages:response.totalPage,
                      visiblePages:10,
                      initiateStartPageClick:false,
                      onPageClick:function (e,page) {
                          getComments(page);
                      }
                  });
                  // 渲染到模板上
                  var commentsHtmlRes = $("#comments_tmpl").render({comments: response.comments});
                  $("tbody").fadeIn().html(commentsHtmlRes);
                  current_page = page;
              });
          }
          // 由于列表使用脚本动态生成，使用委托注册事件,利用事件冒泡
          // 删除按钮点击事件
          $("tbody").on("click",".btn-delete",function () {
              // 获取当前评论的id
              var comment_id = $(this).parent().parent().data("id");
              // 向服务端发送删除的请求
              $.get("/admin/api/comment_delete.php?id=" + comment_id,function (response) {
                  if (!response) return;
                  getComments(current_page);
              });
          });
          // 批准按钮点击事件
          $("tbody").on("click",".btn-approval",function () {
              // 获取当前评论的id
              var comment_id = $(this).parent().parent().data("id");
              // 向服务端发送批准的请求
              $.get("/admin/api/comment_approval.php?id=" + comment_id,function (response) {
                  if (!response) return;
                  getComments(current_page);
              })
          });
          // 拒绝按钮点击事件
          $("tbody").on("click",".btn-deny",function () {
              // 获取当前评论的id
              var comment_id = $(this).parent().parent().data("id");
              // 向服务端发送批准的请求
              $.get("/admin/api/comment_deny.php?id=" + comment_id,function (response) {
                  if (!response) return;
                  getComments(current_page);
              })
          });
          // 准许按钮点击事件
          $("tbody").on("click",".btn-permission",function () {
              // 获取当前评论的id
              var comment_id = $(this).parent().parent().data("id");
              // 向服务端发送批准的请求
              $.get("/admin/api/comment_permission.php?id=" + comment_id,function (response) {
                  if (!response) return;
                  getComments(current_page);
              })
          });
          // 还原按钮点击事件
          $("tbody").on("click",".btn-back",function () {
              // 获取当前评论的id
              var comment_id = $(this).parent().parent().data("id");
              // 向服务端发送批准的请求
              $.get("/admin/api/comment_permission.php?id=" + comment_id,function (response) {
                  if (!response) return;
                  getComments(current_page);
              })
          });
      });
  </script>
  <script>NProgress.done()</script>
</body>
</html>
