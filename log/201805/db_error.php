<?php exit;?>	2018-05-16 09:00:53	127.0.0.1	/admin/?plugin-unstall-sq_secret_post.htm	1	SQL:ALTER TABLE bbs_user DROP COLUMN is_secret; errno: 1091, errstr: Can't DROP 'is_secret'; check that column/key exists
<?php exit;?>	2018-05-16 09:01:21	127.0.0.1	/admin/?plugin-install-sq_secret_post.htm	1	SQL:ALTER TABLE bbs_post ADD COLUMN is_secret tinyint(11) NULL DEFAULT '0' COMMENT '匿名'; errno: 1060, errstr: Duplicate column name 'is_secret'
<?php exit;?>	2018-05-16 13:57:40	127.0.0.1	/admin/?plugin-unstall-sq_secret_post.htm	1	SQL:ALTER TABLE bbs_thread DROP COLUMN last_secret; errno: 1091, errstr: Can't DROP 'last_secret'; check that column/key exists
<?php exit;?>	2018-05-17 10:07:04	127.0.0.1	/admin/?plugin-install-sq_secret_post.htm	1	SQL:ALTER TABLE bbs_thread ADD COLUMN is_secret tinyint(1) NULL DEFAULT '0' COMMENT '是否匿名'; errno: 1060, errstr: Duplicate column name 'is_secret'
<?php exit;?>	2018-05-17 10:07:04	127.0.0.1	/admin/?plugin-install-sq_secret_post.htm	1	SQL:ALTER TABLE bbs_thread ADD COLUMN last_secret varchar(2) NULL DEFAULT '0' COMMENT '最近回复是匿名回复'; errno: 1060, errstr: Duplicate column name 'last_secret'
<?php exit;?>	2018-05-17 10:07:04	127.0.0.1	/admin/?plugin-install-sq_secret_post.htm	1	SQL:ALTER TABLE bbs_post ADD COLUMN is_secret tinyint(1) NULL DEFAULT '0' COMMENT '是否匿名'; errno: 1060, errstr: Duplicate column name 'is_secret'
<?php exit;?>	2018-05-17 10:08:00	127.0.0.1	/admin/?plugin-install-sq_secret_post.htm	1	SQL:ALTER TABLE bbs_thread ADD COLUMN is_secret tinyint(1) NULL DEFAULT '0' COMMENT '是否匿名'; errno: 1060, errstr: Duplicate column name 'is_secret'
<?php exit;?>	2018-05-17 10:08:00	127.0.0.1	/admin/?plugin-install-sq_secret_post.htm	1	SQL:ALTER TABLE bbs_thread ADD COLUMN last_secret varchar(2) NULL DEFAULT '0' COMMENT '最近回复是匿名回复'; errno: 1060, errstr: Duplicate column name 'last_secret'
<?php exit;?>	2018-05-17 10:08:00	127.0.0.1	/admin/?plugin-install-sq_secret_post.htm	1	SQL:ALTER TABLE bbs_post ADD COLUMN is_secret tinyint(1) NULL DEFAULT '0' COMMENT '是否匿名'; errno: 1060, errstr: Duplicate column name 'is_secret'
<?php exit;?>	2018-05-17 10:08:43	127.0.0.1	/admin/?plugin-install-sq_secret_post.htm	1	SQL:ALTER TABLE bbs_thread ADD COLUMN is_secret tinyint(1) NULL DEFAULT '0' COMMENT '是否匿名'; errno: 1060, errstr: Duplicate column name 'is_secret'
<?php exit;?>	2018-05-17 10:08:43	127.0.0.1	/admin/?plugin-install-sq_secret_post.htm	1	SQL:ALTER TABLE bbs_thread ADD COLUMN last_secret varchar(2) NULL DEFAULT '0' COMMENT '最近回复是匿名回复'; errno: 1060, errstr: Duplicate column name 'last_secret'
<?php exit;?>	2018-05-17 10:08:43	127.0.0.1	/admin/?plugin-install-sq_secret_post.htm	1	SQL:ALTER TABLE bbs_post ADD COLUMN is_secret tinyint(1) NULL DEFAULT '0' COMMENT '是否匿名'; errno: 1060, errstr: Duplicate column name 'is_secret'
<?php exit;?>	2018-05-17 10:09:33	127.0.0.1	/admin/?plugin-install-sq_secret_post.htm	1	SQL:ALTER TABLE bbs_thread ADD COLUMN is_secret tinyint(1) NULL DEFAULT '0' COMMENT '是否匿名'; errno: 1060, errstr: Duplicate column name 'is_secret'
<?php exit;?>	2018-05-17 10:09:33	127.0.0.1	/admin/?plugin-install-sq_secret_post.htm	1	SQL:ALTER TABLE bbs_thread ADD COLUMN last_secret varchar(2) NULL DEFAULT '0' COMMENT '最近回复是匿名回复'; errno: 1060, errstr: Duplicate column name 'last_secret'
<?php exit;?>	2018-05-17 10:09:33	127.0.0.1	/admin/?plugin-install-sq_secret_post.htm	1	SQL:ALTER TABLE bbs_post ADD COLUMN is_secret tinyint(1) NULL DEFAULT '0' COMMENT '是否匿名'; errno: 1060, errstr: Duplicate column name 'is_secret'
<?php exit;?>	2018-05-17 10:11:45	127.0.0.1	/admin/?plugin-install-sq_secret_post.htm	1	SQL:ALTER TABLE bbs_thread ADD COLUMN is_secret tinyint(1) NULL DEFAULT '0' COMMENT '是否匿名'; errno: 1060, errstr: Duplicate column name 'is_secret'
<?php exit;?>	2018-05-17 10:11:45	127.0.0.1	/admin/?plugin-install-sq_secret_post.htm	1	SQL:ALTER TABLE bbs_thread ADD COLUMN last_secret varchar(2) NULL DEFAULT '0' COMMENT '最近回复是匿名回复'; errno: 1060, errstr: Duplicate column name 'last_secret'
<?php exit;?>	2018-05-17 10:11:45	127.0.0.1	/admin/?plugin-install-sq_secret_post.htm	1	SQL:ALTER TABLE bbs_post ADD COLUMN is_secret tinyint(1) NULL DEFAULT '0' COMMENT '是否匿名'; errno: 1060, errstr: Duplicate column name 'is_secret'
<?php exit;?>	2018-05-17 10:15:33	127.0.0.1	/?post-create-22-1.htm	1	SQL:UPDATE bbs_notice SET `last_secret` = 1 WHERE nid = 19 errno: 1054, errstr: Unknown column 'last_secret' in 'field list'
<?php exit;?>	2018-05-17 10:16:27	127.0.0.1	/?post-create-22-1.htm	1	SQL:UPDATE bbs_notice SET `last_secret` = 1 WHERE nid = 20 errno: 1054, errstr: Unknown column 'last_secret' in 'field list'
