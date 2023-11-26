CREATE TABLE IF NOT EXISTS `cpay_config`(
    `k` varchar(255) NOT NULL COMMENT '配置项',
    `v` varchar(255) NOT NULL  COMMENT '配置值',
    PRIMARY KEY (`k`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;