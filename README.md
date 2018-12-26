# iicms

#### 项目介绍
iicms内容管理系统，演示地址：cms.i-i.me

#### 软件架构
Thinkphp5.1 + Amaze UI
要求PHP最低版本5.6


#### 安装教程

1. php最低版本5.6
2. 手工导入数据表文件iicms.sql
3. 后台地址：/admin 账号：admin 密码：123456
	

#### 使用说明

1. xxxx
2. xxxx
3. xxxx

#### 参与贡献

1. Fork 本项目
2. 新建 Feat_xxx 分支
3. 提交代码
4. 新建 Pull Request

#### 文件修改

##### 伪静态文件修改：

`RewriteRule ^(.*)$ index.php [L,E=PATH_INFO:$1]`

##### 配置修改：

`/config/app.php 'app_trace'=> true`
`/config/template.php`
`/config/log.php`
`/config/database.php`

##### 添加文件及文件夹：

`/extend/com/captcha	conmon.php中include helper.php`
##### 添加文件：

`/extend/helper/`
##### 添加文件：

`/extend/helper/Auth.php`
##### 添加文件及文件夹：

`/extend/helper/image`

##### 百度编辑器上传文件目录：

`/upload/image`



##### 系统文件修改：

`/thinkphp/library/think/model/relation/OneToOne.php	添加allowField属性及方法`

##### 系统bug修改：

`/thinkphp/library/think/Model.php			注释__isset函数$this->getAttr($name);return true;添加return false;`
##### 系统bug修改：

`/thinkphp/library/think/model/Collection.php		添加if($item)`