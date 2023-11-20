{"admin":{"id":{"type":"int(11) unsigned","index":"PRIMARY","default":"AUTO_INCREMENT"},"tuid":{"type":"int(11) unsigned","index":"BTREE","default":"0"},"status":{"type":"int(1) unsigned","index":null,"default":"1"},"name":{"type":"varchar(64)","index":"UNIQUE","default":"NULL"},"title":{"type":"varchar(100)","index":null,"default":"NULL"},"pass":{"type":"varchar(64)","index":null,"default":"NULL"},"salt":{"type":"varchar(16)","index":null,"default":"NULL"},"email":{"type":"varchar(64)","index":"BTREE","default":"NULL"},"mobile":{"type":"varchar(11)","index":"BTREE","default":"NULL"},"type":{"type":"varchar(20)","index":"BTREE","default":"NULL"},"headimg":{"type":"varchar(255)","index":null,"default":"NULL"},"points":{"type":"int(11)","index":null,"default":"0"},"balance":{"type":"decimal(12,2)","index":null,"default":"0.00"},"storage":{"type":"int(11) unsigned","index":null,"default":"0"},"storage_used":{"type":"int(11)","index":null,"default":"0"},"addtime":{"type":"datetime","index":null,"default":"NULL"},"lasttime":{"type":"datetime","index":null,"default":"NULL"},"logintime":{"type":"datetime","index":null,"default":"NULL"},"ip":{"type":"varchar(64)","index":null,"default":"NULL"},"parameter":{"type":"longtext","index":null,"default":"NULL"},"lcms":{"type":"int(11) unsigned","index":"BTREE","default":"0"}},"admin_band":{"id":{"type":"int(11) unsigned","index":"PRIMARY","default":"AUTO_INCREMENT"},"aid":{"type":"int(11) unsigned","index":"BTREE","default":"0"},"openid":{"type":"varchar(128)","index":"BTREE","default":"NULL"}},"admin_level":{"id":{"type":"int(11) unsigned","index":"PRIMARY","default":"AUTO_INCREMENT"},"uid":{"type":"int(11) unsigned","index":"BTREE","default":"0"},"name":{"type":"varchar(64)","index":null,"default":"NULL"},"parameter":{"type":"longtext","index":null,"default":"NULL"}},"cache":{"id":{"type":"int(11) unsigned","index":"PRIMARY","default":"AUTO_INCREMENT"},"name":{"type":"varchar(32)","index":"BTREE","default":"NULL"},"parameter":{"type":"longtext","index":null,"default":"NULL"},"lcms":{"type":"int(11) unsigned","index":"BTREE","default":"0"}},"config":{"id":{"type":"int(11) unsigned","index":"PRIMARY","default":"AUTO_INCREMENT"},"name":{"type":"varchar(32)","index":"BTREE","default":"NULL"},"type":{"type":"varchar(32)","index":"BTREE","default":"NULL"},"cate":{"type":"varchar(32)","index":"BTREE","default":"NULL"},"parameter":{"type":"longtext","index":null,"default":"NULL"},"lcms":{"type":"int(11) unsigned","index":"BTREE","default":"0"}},"log":{"id":{"type":"int(11) unsigned","index":"PRIMARY","default":"AUTO_INCREMENT"},"user":{"type":"varchar(32)","index":"BTREE","default":"NULL"},"type":{"type":"varchar(16)","index":"BTREE","default":"NULL"},"ip":{"type":"varchar(64)","index":"BTREE","default":"NULL"},"info":{"type":"longtext","index":null,"default":"NULL"},"url":{"type":"varchar(255)","index":null,"default":"NULL"},"addtime":{"type":"datetime","index":"BTREE","default":"NULL"},"parameter":{"type":"longtext","index":null,"default":"NULL"},"lcms":{"type":"int(11) unsigned","index":"BTREE","default":"0"}},"upload":{"id":{"type":"int(11) unsigned","index":"PRIMARY","default":"AUTO_INCREMENT"},"type":{"type":"varchar(10)","index":"BTREE","default":"NULL"},"datey":{"type":"varchar(6)","index":"BTREE","default":"NULL"},"name":{"type":"varchar(32)","index":"BTREE","default":"NULL"},"size":{"type":"int(11) unsigned","index":null,"default":"0"},"src":{"type":"varchar(255)","index":null,"default":"NULL"},"local":{"type":"int(1) unsigned","index":null,"default":"0"},"addtime":{"type":"datetime","index":null,"default":"NULL"},"uid":{"type":"int(11) unsigned","index":"BTREE","default":"0"},"lcms":{"type":"int(11) unsigned","index":"BTREE","default":"0"}},"order":{"id":{"type":"int(11) unsigned","index":"PRIMARY","default":"AUTO_INCREMENT"},"order_no":{"type":"varchar(64)","index":"UNIQUE","default":"NULL"},"body":{"type":"varchar(128)","index":null,"default":"NULL"},"pay":{"type":"decimal(12,2)","index":null,"default":"0.00"},"payment":{"type":"varchar(16)","index":"BTREE","default":"NULL"},"paytype":{"type":"varchar(16)","index":null,"default":"NULL"},"payid":{"type":"int(11) unsigned","index":null,"default":"0"},"status":{"type":"int(1) unsigned","index":"BTREE","default":"0"},"callback":{"type":"varchar(64)","index":null,"default":"NULL"},"addtime":{"type":"datetime","index":null,"default":"NULL"},"paytime":{"type":"datetime","index":null,"default":"NULL"},"repaytime":{"type":"datetime","index":null,"default":"NULL"}},"ram":{"id":{"type":"int(11) unsigned","index":"PRIMARY","default":"AUTO_INCREMENT"},"name":{"type":"char(32)","index":"HASH","default":"NULL"},"value":{"type":"char(255)","index":null,"default":"NULL"},"time":{"type":"int(11) unsigned","index":"HASH","default":"NULL"},"lcms":{"type":"int(11) unsigned","index":"HASH","default":"0"},"LCMSDATAOTHER":{"engine":"MEMORY"}},"shortlink":{"id":{"type":"int(11) unsigned","index":"PRIMARY","default":"AUTO_INCREMENT"},"code":{"type":"varchar(8)","index":"UNIQUE","default":"NULL"},"hval":{"type":"varchar(32)","index":"UNIQUE","default":"NULL"},"lasttime":{"type":"int(11) unsigned","index":"BTREE","default":"NULL"},"url":{"type":"text","index":null,"default":"NULL"},"data":{"type":"text","index":null,"default":"NULL"}},"payment":{"id":{"type":"int(11) unsigned","index":"PRIMARY","default":"AUTO_INCREMENT"},"title":{"type":"varchar(64)","index":null,"default":"NULL"},"payment":{"type":"varchar(64)","index":"BTREE","default":"NULL"},"parameter":{"type":"longtext","index":null,"default":"NULL"},"lcms":{"type":"int(11) unsigned","index":"BTREE","default":"0"}}}