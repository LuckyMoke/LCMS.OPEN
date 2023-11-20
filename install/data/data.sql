CREATE TABLE `[_PRE]admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tuid` int(11) unsigned DEFAULT '0' COMMENT '推广用户',
  `status` int(1) unsigned DEFAULT '1' COMMENT '账号状态',
  `name` varchar(64) DEFAULT NULL COMMENT '账号',
  `title` varchar(100) DEFAULT NULL COMMENT '用户中文说明',
  `pass` varchar(64) DEFAULT NULL COMMENT '密码',
  `salt` varchar(16) DEFAULT NULL COMMENT '盐',
  `email` varchar(64) DEFAULT NULL COMMENT '邮箱',
  `mobile` varchar(11) DEFAULT NULL COMMENT '手机号',
  `type` varchar(20) DEFAULT NULL COMMENT '级别',
  `headimg` varchar(255) DEFAULT NULL COMMENT '头像',
  `points` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
  `balance` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `storage` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '存储空间',
  `storage_used` int(11) DEFAULT '0' COMMENT '存储空间已使用',
  `addtime` datetime DEFAULT NULL COMMENT '添加时间',
  `lasttime` datetime DEFAULT NULL COMMENT '到期时间',
  `logintime` datetime DEFAULT NULL COMMENT '最后登陆时间',
  `ip` varchar(64) DEFAULT NULL COMMENT '登录IP',
  `parameter` longtext COMMENT '套餐包',
  `lcms` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `lcms` (`lcms`) USING BTREE,
  KEY `email` (`email`) USING BTREE,
  KEY `mobile` (`mobile`) USING BTREE,
  KEY `tuid` (`tuid`) USING BTREE
) DEFAULT CHARSET=utf8mb4 COMMENT='管理员信息';

CREATE TABLE `[_PRE]admin_band` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `aid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '账号ID',
  `openid` varchar(128) DEFAULT NULL COMMENT 'OPENID',
  PRIMARY KEY (`id`),
  KEY `aid` (`aid`),
  KEY `openid` (`openid`)
) DEFAULT CHARSET=utf8mb4 COMMENT='管理员绑定';

CREATE TABLE `[_PRE]admin_level` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned DEFAULT '0' COMMENT '上级是谁',
  `name` varchar(64) DEFAULT NULL COMMENT '权限名称',
  `parameter` longtext COMMENT '权限内容',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) DEFAULT CHARSET=utf8mb4 COMMENT='管理员权限';

CREATE TABLE `[_PRE]cache` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL COMMENT '名称',
  `parameter` longtext,
  `lcms` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `lcms` (`lcms`) USING BTREE,
  KEY `name` (`name`) USING BTREE
) DEFAULT CHARSET=utf8mb4 COMMENT='硬盘缓存';

CREATE TABLE `[_PRE]config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL COMMENT '模块名字',
  `type` varchar(32) DEFAULT NULL COMMENT '应用type',
  `cate` varchar(32) DEFAULT NULL COMMENT '自定义保存的参数',
  `parameter` longtext COMMENT '数据内容',
  `lcms` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '0为所有用户读取',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `type` (`type`),
  KEY `lcms` (`lcms`),
  KEY `cate` (`cate`) USING BTREE
) DEFAULT CHARSET=utf8mb4 COMMENT='全局设置数据';

insert  into `[_PRE]config`(`id`,`name`,`type`,`cate`,`parameter`,`lcms`) values (1, 'menu', 'sys', 'admin', 'a:1:{s:4:\"open\";a:0:\"\"}', 0),(2,'config','sys','admin','a:9:{s:10:\"oauth_code\";s:0:\"\";s:5:\"title\";s:21:\"盘企PHP开发框架\";s:9:\"developer\";s:39:\"运城市盘石网络科技有限公司\";s:5:\"https\";s:1:\"0\";s:7:\"gonggao\";s:1492:\"PHRhYmxlPjx0Ym9keT48dHIgY2xhc3M9ImZpcnN0Um93Ij48dGQgd2lkdGg9IjYwIiB2YWxpZ249InRvcCI+PGJyLz48L3RkPjx0ZCB3aWR0aD0iIiB2YWxpZ249Im1pZGRsZSIgc3R5bGU9IndvcmQtYnJlYWs6IGJyZWFrLWFsbDsiIGFsaWduPSJjZW50ZXIiPjxzdHJvbmc+55uY5LyBUEhQ5byA5Y+R5qGG5p62PC9zdHJvbmc+PC90ZD48dGQgd2lkdGg9IiIgdmFsaWduPSJtaWRkbGUiIHN0eWxlPSJ3b3JkLWJyZWFrOiBicmVhay1hbGw7IiBhbGlnbj0iY2VudGVyIj48c3Ryb25nPuebmOS8geW7uuermTwvc3Ryb25nPjwvdGQ+PC90cj48dHI+PHRkIHdpZHRoPSIiIHZhbGlnbj0ibWlkZGxlIiBzdHlsZT0id29yZC1icmVhazogYnJlYWstYWxsOyIgYWxpZ249InJpZ2h0Ij7lrpjmlrnnvZHnq5k8L3RkPjx0ZCB3aWR0aD0iIiB2YWxpZ249Im1pZGRsZSIgc3R5bGU9IndvcmQtYnJlYWs6IGJyZWFrLWFsbDsiIGFsaWduPSJjZW50ZXIiPjxhIGhyZWY9Imh0dHBzOi8vd3d3LnBhbnNoaTE4LmNvbS8iIHRhcmdldD0iX2JsYW5rIj5odHRwczovL3d3dy5wYW5zaGkxOC5jb20vPC9hPjwvdGQ+PHRkIHdpZHRoPSIiIHZhbGlnbj0ibWlkZGxlIiBzdHlsZT0id29yZC1icmVhazogYnJlYWstYWxsOyIgYWxpZ249ImNlbnRlciI+PGEgaHJlZj0iaHR0cHM6Ly9vdXJjbXMuY24vIiB0YXJnZXQ9Il9ibGFuayI+aHR0cHM6Ly9vdXJjbXMuY24vPC9hPjwvdGQ+PC90cj48dHI+PHRkIHdpZHRoPSIiIHZhbGlnbj0ibWlkZGxlIiBzdHlsZT0id29yZC1icmVhazogYnJlYWstYWxsOyIgYWxpZ249InJpZ2h0Ij7lvIDlj5HmlofmoaM8L3RkPjx0ZCB3aWR0aD0iIiB2YWxpZ249Im1pZGRsZSIgc3R5bGU9IndvcmQtYnJlYWs6IGJyZWFrLWFsbDsiIGFsaWduPSJjZW50ZXIiPjxhIGhyZWY9Imh0dHBzOi8vZG9jLm91cmNtcy5jbiIgdGFyZ2V0PSJfYmxhbmsiPueCueWHu+afpeecizwvYT48L3RkPjx0ZCB3aWR0aD0iIiB2YWxpZ249Im1pZGRsZSIgc3R5bGU9IndvcmQtYnJlYWs6IGJyZWFrLWFsbDsiIGFsaWduPSJjZW50ZXIiPjxhIGhyZWY9Imh0dHBzOi8vZG9jLm91cmNtcy5jbiIgdGFyZ2V0PSJfYmxhbmsiPueCueWHu+afpeecizwvYT48L3RkPjwvdHI+PC90Ym9keT48L3RhYmxlPg==\";s:3:\"dir\";s:5:\"admin\";s:11:\"sessiontime\";s:0:\"\";s:7:\"attsize\";s:3:\"300\";s:8:\"mimelist\";s:45:\"jpg|jpeg|png|gif|bmp|webp|ico|mp3|mp4|txt|csv\";}',0),(3,'config','sys','plugin','a:2:{s:5:\"email\";a:6:\"\";s:3:\"sms\";a:0:\"\"}',0),(4,'config','sys','web','a:7:{s:5:\"https\";s:1:\"1\";s:6:\"domain\";s:0:\"\";s:4:\"logo\";s:0:\"\";s:11:\"domain_must\";s:1:\"0\";s:5:\"title\";s:21:\"盘企PHP开发框架\";s:13:\"image_default\";s:0:\"\";s:6:\"tongji\";s:0:\"\";}',0);

CREATE TABLE `[_PRE]log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(32) DEFAULT NULL,
  `type` varchar(16) DEFAULT NULL,
  `ip` varchar(64) DEFAULT NULL,
  `info` longtext,
  `url` varchar(255) DEFAULT NULL,
  `parameter` longtext,
  `addtime` datetime DEFAULT NULL,
  `lcms` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user` (`user`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `ip` (`ip`) USING BTREE,
  KEY `addtime` (`addtime`) USING BTREE,
  KEY `lcms` (`lcms`) USING BTREE
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `[_PRE]upload` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(10) DEFAULT NULL COMMENT '上传类型',
  `datey` varchar(6) DEFAULT NULL COMMENT '上传目录',
  `name` varchar(32) DEFAULT NULL COMMENT '文件名称',
  `size` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `src` varchar(255) DEFAULT NULL COMMENT '文件链接',
  `local` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '强制本地存储',
  `addtime` datetime DEFAULT NULL COMMENT '上传时间',
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `lcms` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `datey` (`datey`),
  KEY `name` (`name`),
  KEY `lcms` (`lcms`),
  KEY `uid` (`uid`)
) CHARACTER SET = utf8mb4 COMMENT='上传文件表';

CREATE TABLE `[_PRE]order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_no` varchar(64) DEFAULT NULL COMMENT '订单号',
  `body` varchar(255) DEFAULT NULL COMMENT '商品名称',
  `pay` decimal(12,2) DEFAULT '0.00' COMMENT '订单金额',
  `payment` varchar(32) DEFAULT NULL COMMENT '支付方式',
  `paytype` varchar(32) DEFAULT NULL COMMENT '支付具体方式',
  `payid` int(11) NOT NULL DEFAULT '0' COMMENT '支付方式的id',
  `status` int(1) DEFAULT '0' COMMENT '支付状态',
  `callback` varchar(64) DEFAULT NULL COMMENT '支付回调',
  `addtime` datetime DEFAULT NULL COMMENT '下单时间',
  `paytime` datetime DEFAULT NULL COMMENT '支付时间',
  `repaytime` datetime DEFAULT NULL COMMENT '退款时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_no` (`order_no`),
  KEY `payment` (`payment`),
  KEY `status` (`status`)
) DEFAULT CHARSET=utf8mb4 COMMENT='全局订单数据';

CREATE TABLE `[_PRE]ram` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(32) NOT NULL,
  `value` char(255) DEFAULT NULL,
  `time` int(11) unsigned NOT NULL,
  `lcms` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `time` (`time`),
  KEY `lcms` (`lcms`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8mb4 COMMENT='内存缓存';

CREATE TABLE `[_PRE]shortlink` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(8) NOT NULL,
  `hval` varchar(32) NOT NULL,
  `lasttime` int(11) unsigned NOT NULL,
  `url` text,
  `data` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `hval` (`hval`),
  KEY `lasttime` (`lasttime`)
) DEFAULT CHARSET=utf8mb4 COMMENT='短链数据';

CREATE TABLE `[_PRE]payment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) DEFAULT NULL COMMENT '名称',
  `payment` varchar(64) DEFAULT NULL COMMENT '支付标识',
  `parameter` longtext,
  `lcms` int(11) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `payment` (`payment`),
  KEY `lcms` (`lcms`)
) DEFAULT CHARSET=utf8mb4 COMMENT='全局支付方式';