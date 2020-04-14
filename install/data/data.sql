CREATE TABLE `[_PRE]admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tuid` int(11) DEFAULT '0' COMMENT '推广用户',
  `status` int(1) DEFAULT '1' COMMENT '账号状态',
  `name` varchar(64) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL COMMENT '用户中文说明',
  `pass` varchar(64) DEFAULT NULL,
  `email` varchar(64) DEFAULT NULL,
  `mobile` varchar(11) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL COMMENT '级别',
  `balance` decimal(10,2) DEFAULT '0.00' COMMENT '余额',
  `addtime` datetime DEFAULT NULL COMMENT '添加时间',
  `lasttime` datetime DEFAULT NULL COMMENT '到期时间',
  `logintime` datetime DEFAULT NULL COMMENT '最后登陆时间',
  `parameter` text COMMENT '套餐包',
  `ip` varchar(64) DEFAULT NULL COMMENT '登录IP',
  `lcms` int(11) DEFAULT '0' COMMENT '上级用户',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `type` (`type`),
  KEY `lcms` (`lcms`),
  KEY `email` (`email`),
  KEY `mobile` (`mobile`),
  KEY `tuid` (`tuid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `[_PRE]admin_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0' COMMENT '上级是谁',
  `name` varchar(64) DEFAULT NULL COMMENT '权限名称',
  `parameter` text COMMENT '权限内容',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[_PRE]config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL COMMENT '模块名字',
  `type` varchar(32) DEFAULT NULL COMMENT '应用type',
  `cate` varchar(32) DEFAULT NULL COMMENT '自定义保存的参数',
  `parameter` longtext COMMENT '数据内容',
  `lcms` int(11) DEFAULT '0' COMMENT '0为所有用户读取',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `type` (`type`),
  KEY `lcms` (`lcms`),
  KEY `cate` (`cate`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

insert  into `[_PRE]config`(`id`,`name`,`type`,`cate`,`parameter`,`lcms`) values (1,'menu','sys','admin','a:2:{s:3:\"sys\";a:2:{i:0;a:2:{s:5:\"title\";s:12:\"用户中心\";s:4:\"menu\";a:1:{s:4:\"user\";a:1:{s:5:\"class\";a:2:{s:6:\"normal\";s:1:\"1\";s:5:\"admin\";i:1;}}}}i:1;a:2:{s:5:\"title\";s:12:\"系统设置\";s:4:\"menu\";a:2:{s:6:\"config\";a:1:{s:5:\"class\";a:3:{s:5:\"admin\";i:1;s:3:\"web\";i:1;s:6:\"update\";i:1;}}s:4:\"menu\";a:1:{s:5:\"class\";a:1:{s:5:\"admin\";i:1;}}}}}s:4:\"open\";a:1:{i:1;a:2:{s:5:\"title\";s:12:\"应用中心\";s:4:\"menu\";a:1:{s:8:\"appstore\";a:1:{s:5:\"class\";a:2:{s:5:\"local\";i:1;s:5:\"store\";i:1;}}}}}}',0),(2,'config','sys','admin','a:13:{s:10:\"oauth_code\";s:0:\"\";s:5:\"title\";s:21:\"盘企PHP开发框架\";s:3:\"ver\";s:15:\"2020.0414145813\";s:8:\"lcmsmode\";s:1:\"1\";s:9:\"developer\";s:39:\"运城市盘石网络科技有限公司\";s:4:\"logo\";s:43:\"../public/static/images/logo.png?1582619927\";s:5:\"https\";s:1:\"0\";s:7:\"gonggao\";s:416:\"PGgzPjxzdHJvbmc+5qGG5p625byA5Y+R5paH5qGjPC9zdHJvbmc+PC9oMz48cD48YSBocmVmPSJodHRwczovL3d3dy5rYW5jbG91ZC5jbi9sdWNreW1va2UvbGNtcy8iIHRhcmdldD0iX2JsYW5rIj5odHRwczovL3d3dy5rYW5jbG91ZC5jbi9sdWNreW1va2UvbGNtcy88L2E+PC9wPjxwPjxici8+PC9wPjxoMz48c3Ryb25nPuWumOaWueS6pOa1gee+pDwvc3Ryb25nPjwvaDM+PHA+UVHnvqTvvJo8YSBocmVmPSJodHRwczovL2pxLnFxLmNvbS8/X3d2PTEwMjcmaz01aE9MMjF3IiB0YXJnZXQ9Il9ibGFuayI+ODQ5OTY2ODk4PC9hPjxici8+PC9wPg==\";s:3:\"dir\";s:5:\"admin\";s:11:\"sessiontime\";s:0:\"\";s:10:\"login_code\";a:4:{s:4:\"type\";s:1:\"0\";s:6:\"domain\";s:0:\"\";s:8:\"luosimao\";a:2:{s:8:\"site_key\";s:0:\"\";s:7:\"api_key\";s:0:\"\";}s:7:\"tencent\";a:2:{s:5:\"appid\";s:0:\"\";s:9:\"appsecret\";s:0:\"\";}}s:7:\"attsize\";s:3:\"300\";s:8:\"mimelist\";s:32:\"jpg|jpeg|png|gif|bmp|mp3|txt|csv\";}',0),(3,'config','sys','plugin','a:2:{s:5:\"email\";a:6:{s:8:\"fromname\";s:12:\"我的邮箱\";s:4:\"from\";s:11:\"mail@qq.com\";s:4:\"pass\";s:3:\"123\";s:4:\"smtp\";s:11:\"smtp.qq.com\";s:4:\"port\";s:3:\"465\";s:3:\"ssl\";s:1:\"1\";}s:6:\"alisms\";a:3:{s:2:\"id\";s:0:\"\";s:6:\"secret\";s:0:\"\";s:4:\"sign\";s:0:\"\";}}',0),(4,'config','sys','web','a:7:{s:5:\"https\";s:1:\"1\";s:6:\"domain\";s:0:\"\";s:4:\"logo\";s:0:\"\";s:11:\"domain_must\";s:1:\"0\";s:5:\"title\";s:21:\"盘企PHP开发框架\";s:13:\"image_default\";s:0:\"\";s:6:\"tongji\";s:0:\"\";}',0);

CREATE TABLE `[_PRE]order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_no` varchar(64) DEFAULT NULL COMMENT '订单号',
  `body` varchar(255) DEFAULT NULL COMMENT '商品名称',
  `pay` decimal(10,2) DEFAULT '0.00' COMMENT '订单金额',
  `payment` varchar(32) DEFAULT NULL COMMENT '支付方式',
  `paytype` varchar(32) DEFAULT NULL COMMENT '支付具体方式',
  `payid` int(11) DEFAULT NULL COMMENT '支付方式的id',
  `status` int(1) DEFAULT '0' COMMENT '支付状态',
  `addtime` datetime DEFAULT NULL COMMENT '下单时间',
  `paytime` datetime DEFAULT NULL COMMENT '支付时间',
  `repaytime` datetime DEFAULT NULL COMMENT '退款时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_no` (`order_no`),
  KEY `payment` (`payment`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[_PRE]payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(64) DEFAULT NULL COMMENT '名称',
  `payment` varchar(64) DEFAULT NULL COMMENT '支付标识',
  `parameter` longtext,
  `lcms` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment` (`payment`),
  KEY `lcms` (`lcms`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;