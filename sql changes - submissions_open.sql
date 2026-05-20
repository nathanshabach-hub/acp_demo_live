ALTER TABLE `conventionseasons`
ADD COLUMN `submissions_open` SMALLINT(4) NOT NULL DEFAULT 1 COMMENT '1=open\r\n0=locked' AFTER `results_release`;
