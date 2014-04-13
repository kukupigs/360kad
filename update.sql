--添加分类广告--
ALTER TABLE `ask_category` ADD `ad1` TEXT NULL ,ADD `ad2` TEXT NULL ,ADD `ad3` TEXT NULL;

--问题表加入年龄、性别、是否就诊过--
ALTER TABLE `ask_question` ADD `gender` TINYINT( 1 ) NOT NULL;
ALTER TABLE `ask_question` ADD `age` INT( 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `ask_question` ADD `istreat` TINYINT( 1 ) NOT NULL DEFAULT '0';

--问题表加入诊断描述--
ALTER TABLE `ask_question` ADD `treatdesc` TEXT NULL;
ALTER TABLE `ask_question` ADD `images` TEXT NULL 

