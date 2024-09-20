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
  `2fa` varchar(32) DEFAULT NULL COMMENT '两步验证密钥',
  `jwt` varchar(32) DEFAULT NULL COMMENT 'JWT密钥',
  `addtime` datetime DEFAULT NULL COMMENT '添加时间',
  `lasttime` datetime DEFAULT NULL COMMENT '到期时间',
  `logintime` datetime DEFAULT NULL COMMENT '最后登录时间',
  `ip` varchar(128) DEFAULT NULL COMMENT '登录IP',
  `parameter` longtext COMMENT '套餐包',
  `lcms` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `lcms` (`lcms`) USING BTREE,
  KEY `email` (`email`) USING BTREE,
  KEY `mobile` (`mobile`) USING BTREE,
  KEY `tuid` (`tuid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='管理员信息';

CREATE TABLE `[_PRE]admin_band` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `aid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '账号ID',
  `openid` varchar(128) DEFAULT NULL COMMENT 'OPENID',
  PRIMARY KEY (`id`),
  KEY `aid` (`aid`),
  KEY `openid` (`openid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='管理员绑定';

CREATE TABLE `[_PRE]admin_level` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned DEFAULT '0' COMMENT '上级是谁',
  `name` varchar(64) DEFAULT NULL COMMENT '权限名称',
  `parameter` longtext COMMENT '权限内容',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='管理员权限';

CREATE TABLE `[_PRE]cache` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL COMMENT '名称',
  `parameter` longtext,
  `lcms` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `lcms` (`lcms`) USING BTREE,
  KEY `name` (`name`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='硬盘缓存';

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='全局设置数据';

insert  into `[_PRE]config`(`id`,`name`,`type`,`cate`,`parameter`,`lcms`) values (1, 'menu', 'sys', 'admin', 'a:1:{s:4:\"open\";a:0:\"\"}', 0),(2,'config','sys','admin','a:10:{s:10:\"oauth_code\";s:0:\"\";s:5:\"title\";s:21:\"盘企PHP开发框架\";s:9:\"developer\";s:39:\"运城市盘石网络科技有限公司\";s:5:\"https\";s:1:\"0\";s:7:\"gonggao\";s:1676:\"PHRhYmxlPgogICAgPHRib2R5PgogICAgICAgIDx0ciBjbGFzcz0iZmlyc3RSb3ciPgogICAgICAgICAgICA8dGQgYWxpZ249ImNlbnRlciIgdmFsaWduPSJtaWRkbGUiIGNvbHNwYW49IjIiIHJvd3NwYW49IjEiIHN0eWxlPSJiYWNrZ3JvdW5kLWNvbG9yOiByZ2IoOTYsIDk4LCAxMDIpOyI+CiAgICAgICAgICAgICAgICA8c3BhbiBzdHlsZT0iY29sb3I6ICNGRkZGRkY7Ij48c3Ryb25nPuebmOS8gUNNUyAtIFNhYVPlu7rnq5nns7vnu588L3N0cm9uZz48L3NwYW4+CiAgICAgICAgICAgIDwvdGQ+CiAgICAgICAgPC90cj4KICAgICAgICA8dHI+CiAgICAgICAgICAgIDx0ZCB3aWR0aD0iMTAwIiBhbGlnbj0iY2VudGVyIiB2YWxpZ249Im1pZGRsZSI+CiAgICAgICAgICAgICAgICDlrpjnvZEKICAgICAgICAgICAgPC90ZD4KICAgICAgICAgICAgPHRkPgogICAgICAgICAgICAgICAgPGEgaHJlZj0iaHR0cHM6Ly9vdXJjbXMuY24vIiB0YXJnZXQ9Il9ibGFuayI+aHR0cHM6Ly9vdXJjbXMuY24vPC9hPjxici8+CiAgICAgICAgICAgIDwvdGQ+CiAgICAgICAgPC90cj4KICAgICAgICA8dHI+CiAgICAgICAgICAgIDx0ZCBhbGlnbj0iY2VudGVyIiB2YWxpZ249Im1pZGRsZSI+CiAgICAgICAgICAgICAgICDlronoo4XmlZnnqIsKICAgICAgICAgICAgPC90ZD4KICAgICAgICAgICAgPHRkPgogICAgICAgICAgICAgICAgPGEgaHJlZj0iaHR0cHM6Ly9vdXJjbXMuY24vc2hvdy9uZXdzLTEuaHRtbCIgdGFyZ2V0PSJfYmxhbmsiPmh0dHBzOi8vb3VyY21zLmNuL3Nob3cvbmV3cy0xLmh0bWw8L2E+PGJyLz4KICAgICAgICAgICAgPC90ZD4KICAgICAgICA8L3RyPgogICAgICAgIDx0cj4KICAgICAgICAgICAgPHRkIGFsaWduPSJjZW50ZXIiIHZhbGlnbj0ibWlkZGxlIj4KICAgICAgICAgICAgICAgIOW8gOWPkeaWh+ahowogICAgICAgICAgICA8L3RkPgogICAgICAgICAgICA8dGQ+CiAgICAgICAgICAgICAgICA8YSBocmVmPSJodHRwczovL2RvYy5vdXJjbXMuY24vIiB0YXJnZXQ9Il9ibGFuayI+aHR0cHM6Ly9kb2Mub3VyY21zLmNuLzwvYT48YnIvPgogICAgICAgICAgICA8L3RkPgogICAgICAgIDwvdHI+CiAgICAgICAgPHRyPgogICAgICAgICAgICA8dGQgY29sc3Bhbj0iMiIgcm93c3Bhbj0iMSI+CiAgICAgICAgICAgICAgICDmraTlhoXlrrnlj6/lnKjorr7nva4tJmd0O+WQjuWPsOiuvue9ruS4reWIoOmZpO+8gQogICAgICAgICAgICA8L3RkPgogICAgICAgIDwvdHI+CiAgICA8L3Rib2R5Pgo8L3RhYmxlPg==\";s:3:\"dir\";s:5:\"admin\";s:12:\"loginbytoken\";s:0:\"\";s:7:\"attsize\";s:3:\"300\";s:8:\"mimelist\";s:45:\"jpg|jpeg|png|gif|bmp|webp|ico|mp3|mp4|txt|csv\";s:11:\"login_limit\";i:1;}',0),(3,'config','sys','plugin','a:2:{s:5:\"email\";a:6:\"\";s:3:\"sms\";a:0:\"\"}',0),(4,'config','sys','web','a:7:{s:5:\"https\";s:1:\"1\";s:6:\"domain\";s:0:\"\";s:4:\"logo\";s:0:\"\";s:11:\"domain_must\";s:1:\"0\";s:5:\"title\";s:21:\"盘企PHP开发框架\";s:13:\"image_default\";s:0:\"\";s:6:\"tongji\";s:0:\"\";}',0);

CREATE TABLE `[_PRE]log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(32) DEFAULT NULL,
  `type` varchar(16) DEFAULT NULL,
  `ip` varchar(128) DEFAULT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='系统日志';

CREATE TABLE `[_PRE]notify` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL,
  `isread` int(1) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `content` longtext,
  `addtime` datetime NOT NULL,
  `lcms` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `isread` (`isread`),
  KEY `lcms` (`lcms`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='系统通知';

CREATE TABLE `[_PRE]upload` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '图库ID',
  `type` varchar(10) DEFAULT NULL COMMENT '上传类型',
  `datey` varchar(6) DEFAULT NULL COMMENT '上传目录',
  `oname` varchar(64) DEFAULT NULL COMMENT '文件原始名',
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='上传文件表';

CREATE TABLE `[_PRE]upload_class` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(32) DEFAULT NULL,
  `lcms` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `lcms` (`lcms`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='上传图库表';

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='全局订单数据';

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='短链数据';

CREATE TABLE `[_PRE]payment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) DEFAULT NULL COMMENT '名称',
  `payment` varchar(64) DEFAULT NULL COMMENT '支付标识',
  `parameter` longtext,
  `lcms` int(11) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `payment` (`payment`),
  KEY `lcms` (`lcms`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='全局支付方式';