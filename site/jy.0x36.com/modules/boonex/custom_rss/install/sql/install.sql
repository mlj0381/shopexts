-- create tables
CREATE TABLE `[db_prefix]_main` (
  `ID` int(11) unsigned NOT NULL auto_increment,
  `ProfileID` int(11) unsigned NOT NULL,
  `RSSUrl` varchar(255) NOT NULL,
  `Quantity` int(11) unsigned NOT NULL,
  `Description` varchar(255) NOT NULL,
  `Status` enum('active','passive') NOT NULL default 'passive',
  PRIMARY KEY  (`ID`),
  KEY `ProfileID` (`ProfileID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- admin menu
SET @iExtOrd = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT INTO `sys_menu_admin` (`id`, `parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES (NULL, 2, 'Custom RSS Moderation', 'Custom RSS Moderation', '{siteUrl}modules/boonex/custom_rss/post_mod_crss.php', 'Custom RSS Moderation description', 'mmi_host_tools.gif', '', '', @iExtOrd+1);

-- settings
SET @iMaxOrder = (SELECT `menu_order` + 1 FROM `sys_options_cats` ORDER BY `menu_order` DESC LIMIT 1);
INSERT INTO `sys_options_cats` VALUES(NULL, 'Custom RSS', @iMaxOrder);
SET @iCategId = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`) VALUES('enable_crss_module', 'on', @iCategId, 'Enable Custom RSS Module', 'checkbox', '', '', NULL);
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`) VALUES('crss_AutoApprove_RSS', 'on', @iCategId, 'Autoapprove Custom RSS of Custom RSS Module', 'checkbox', '', '', NULL);

-- page compose pages
SET @PageKey1 = (SELECT MAX(`order`) FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Column`=2);
INSERT INTO `sys_page_compose` (`ID`, `Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES(NULL, 'profile', '998px', 'Custom RSS block', '_crss_Custom_Feeds', 2, @PageKey1+1, 'PHP', 'return BxDolService::call(''custom_rss'', ''gen_custom_rss_block'', array($this->oProfileGen->_iProfileID));', 0, 66, 'non,memb', 0);