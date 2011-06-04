-- Favorites
CREATE TABLE `[db_prefix]_favorites` (
  `ID` int(10) unsigned NOT NULL default '0',
  `Profile` int(10) unsigned NOT NULL default '0',
  `Date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`,`Profile`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Comments Table
CREATE TABLE `[db_prefix]_cmts` (
  `cmt_id` int(11) NOT NULL auto_increment,
  `cmt_parent_id` int(11) NOT NULL default '0',
  `cmt_object_id` int(10) unsigned NOT NULL default '0',
  `cmt_author_id` int(10) unsigned NOT NULL default '0',
  `cmt_text` text NOT NULL,
  `cmt_mood` tinyint NOT NULL default '0',
  `cmt_rate` int(11) NOT NULL default '0',
  `cmt_rate_count` int(11) NOT NULL default '0',
  `cmt_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `cmt_replies` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cmt_id`),
  KEY `cmt_object_id` (`cmt_object_id`,`cmt_parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- main rating table
CREATE TABLE `[db_prefix]_rating` (
  `gal_id` int(10) unsigned NOT NULL default '0',
  `gal_rating_count` int(11) NOT NULL default '0',
  `gal_rating_sum` int(11) NOT NULL default '0',
  UNIQUE KEY `med_id` (`gal_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- rating vote track
CREATE TABLE `[db_prefix]_voting_track` (
  `gal_id` int(10) unsigned NOT NULL default '0',
  `gal_ip` varchar(20) default NULL,
  `gal_date` datetime default NULL,
  KEY `med_ip` (`gal_ip`,`gal_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- views system
CREATE TABLE IF NOT EXISTS `[db_prefix]_views_track` (
  `id` int(10) unsigned NOT NULL,
  `viewer` int(10) unsigned NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `ts` int(10) unsigned NOT NULL,
  KEY `id` (`id`,`viewer`,`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `sys_options_cats` SET `name` = 'Videos';
SET @iKatID = LAST_INSERT_ID();

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`)  VALUES
('category_auto_app_[db_prefix]', 'on', @iKatID, 'Autoapprove categories of files', 'checkbox', '', '', 2, ''),
('[db_prefix]_allowed_exts', 'avi flv mpg wmv mp4 m4v mov divx xvid', @iKatID, 'Allowed extensions', 'digit', '', '', 3, ''),
('[db_prefix]_profile_album_name', '{nickname}''s videos', @iKatID, 'Default profile album name', 'digit', '', '', 4, ''),
('[db_prefix]_mode_index', 'last', @iKatID, 'Show files on index page<br /> (if enabled in the template)', 'select', '', '', 10, 'last,top'),
('[db_prefix]_number_all', '12', @iKatID, 'How many files show on browse page', 'digit', '', '', 20, ''),
('[db_prefix]_number_index', '8', @iKatID, 'How many files show on index page', 'digit', '', '', 21, ''),
('[db_prefix]_number_top', '4', @iKatID, 'How many files show on featured, top and similar sections', 'digit', '', '', 22, ''),
('[db_prefix]_number_user', '4', @iKatID, 'Number of latest files by user', 'digit', '', '', 23, ''),
('[db_prefix]_number_related', '4', @iKatID, 'Number of related files by user', 'digit', '', '', 24, ''),
('[db_prefix]_number_previous_rated', '4', @iKatID, 'Number of previous rated files', 'digit', '', '', 25, ''),
('[db_prefix]_number_browse', '8', @iKatID, 'How many files show on home page', 'digit', '', '', 26, ''),
('[db_prefix]_number_albums_browse', '8', @iKatID, 'How many albums show on browse album page', 'digit', '', '', 27, ''),
('[db_prefix]_number_albums_home', '4', @iKatID, 'How many albums show on home page', 'digit', '', '', 28, ''),
('[db_prefix]_file_width', '600', @iKatID, 'Width of video player (in pixels)', 'digit', '', '', 34, ''),
('[db_prefix]_file_height', '600', @iKatID, 'Height of video player (in pixels)', 'digit', '', '', 35, ''),
('[db_prefix]_browse_width', '140', @iKatID, 'Width of video browse unit (in pixels)', 'digit', '', '', 36, ''),
('[db_prefix]_browse_height', '102', @iKatID, 'Height of video browse unit (in pixels)', 'digit', '', '', 37, ''),
('[db_prefix]_album_slideshow_on', 'on', @iKatID, 'Enable album slideshow', 'checkbox', '', '', 39, ''),
('[db_prefix]_album_slideshow_height', '450', @iKatID, 'Height of album slideshow', 'digit', '', '', 40, ''),
('[db_prefix]_uploader_switcher', 'flash,regular,record,embed', @iKatID, 'Available uploaders', 'list', '', '', 41, 'flash,regular,record,embed');

SET @iPCPOrder = (SELECT MAX(`Order`) FROM `sys_page_compose_pages`);
INSERT INTO `sys_page_compose_pages`(`Name`, `Title`, `Order`) VALUES ('[db_prefix]_view', 'View Video', @iPCPOrder+1);

SET @iPCPOrder = (SELECT MAX(`Order`) FROM `sys_page_compose_pages`);
INSERT INTO `sys_page_compose_pages`(`Name`, `Title`, `Order`) VALUES ('[db_prefix]_home', 'Videos Home', @iPCPOrder+1);

SET @iPCPOrder = (SELECT MAX(`Order`) FROM `sys_page_compose_pages`);
INSERT INTO `sys_page_compose_pages`(`Name`, `Title`, `Order`) VALUES ('[db_prefix]_rate', 'Videos Rate', @iPCPOrder+1);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_view', '998px', '', '_[db_prefix]_view', 2, 0, 'ViewFile', '', 1, 66, 'non,memb', 380),
('[db_prefix]_view', '998px', '', '_[db_prefix]_info_main', 2, 1, 'MainFileInfo', '', 1, 66, 'non,memb', 0),
('[db_prefix]_view', '998px', '', '_[db_prefix]_comments', 2, 2, 'ViewComments', '', 1, 66, 'non,memb', 0),
('[db_prefix]_view', '998px', '', '_[db_prefix]_info', 1, 0, 'FileInfo', '', 1, 34, 'non,memb', 0),
('[db_prefix]_view', '998px', '', '_[db_prefix]_actions', 1, 1, 'ActionList', '', 1, 34, 'non,memb', 0),
('[db_prefix]_view', '998px', '', '_[db_prefix]_albums_latest', 1, 2, 'LastAlbums', '', 1, 34, 'non,memb', 0),
('[db_prefix]_view', '998px', '', '_[db_prefix]_related', 1, 3, 'RelatedFiles', '', 1, 34, 'non,memb', 0);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_home', '998px', '', '_[db_prefix]_albums', 1, 1, 'Albums', '', 1, 34, 'non,memb', 0),
('[db_prefix]_home', '998px', '', '_[db_prefix]_special', 1, 2, 'Special', '', 1, 34, 'non,memb', 0),
('[db_prefix]_home', '998px', '', '_[db_prefix]_favorited', 1, 3, 'Favorited', '', 1, 34, 'non,memb', 0),
('[db_prefix]_home', '998px', '', '_[db_prefix]_latest_file', 2, 1, 'LatestFile', '', 1, 66, 'non,memb', 0),
('[db_prefix]_home', '998px', '', '_[db_prefix]_public', 2, 2, 'All', '', 1, 66, 'non,memb', 380);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_albums_my', '998px', '', '_[db_prefix]_albums_admin', 1, 1, 'adminShort', '', 1, 100, 'memb', 380),
('[db_prefix]_albums_my', '998px', '', '_[db_prefix]_albums_add', 1, 0, 'add', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '998px', '', '_[db_prefix]_albums_admin', 1, 3, 'adminFull', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '998px', '', '_[db_prefix]_albums_disapproved', 1, 5, 'adminFullDisapproved', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '998px', '', '_[db_prefix]_album_edit', 1, 6, 'edit', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '998px', '', '_[db_prefix]_album_delete', 1, 7, 'delete', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '998px', '', '_[db_prefix]_album_organize', 1, 8, 'organize', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '998px', '', '_[db_prefix]_album_add_objects', 1, 9, 'addObjects', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '998px', '', '_[db_prefix]_album_manage_objects', 1, 10, 'manageObjects', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '998px', '', '_[db_prefix]_album_manage_objects_disapproved', 1, 11, 'manageObjectsDisapproved', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '998px', '', '_[db_prefix]_album_manage_objects_not_processed', 1, 12, 'manageObjectsNotProcessed', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '998px', '', '_[db_prefix]_album_manage_objects_failed', 1, 13, 'manageObjectsFailed', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '998px', '', '_[db_prefix]_album_main_objects', 1, 14, 'adminAlbumShort', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '998px', '', '_[db_prefix]_album_objects', 1, 20, 'albumObjects', '', 1, 100, 'memb', 0),
('[db_prefix]_albums_my', '998px', '', '_[db_prefix]_albums_my', 1, 30, 'my', '', 1, 100, 'memb', 0);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('[db_prefix]_rate', '998px', '', '_[db_prefix]_previous_rated', 1, 0, 'RatedSet', '', 1, 34, 'non,memb', 0),
('[db_prefix]_rate', '998px', '', '_[db_prefix]_rate_header', 2, 0, 'RateObject', '', 1, 66, 'non,memb', 0);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('index', '998px', 'Public Videos', '_[db_prefix]_public', 1, 8, 'PHP', 'require_once(BX_DIRECTORY_PATH_MODULES . ''boonex/videos/classes/BxVideosSearch.php'');\r\n $oMedia = new BxVideosSearch();\r\n $aVisible[] = BX_DOL_PG_ALL;\r\n if ($this->iMemberID > 0)\r\n $aVisible[] = BX_DOL_PG_MEMBERS;\r\n $aCode = $oMedia->getBrowseBlock(array(''allow_view''=>$aVisible), array(''menu_top''=>true, ''sorting'' => getParam(''[db_prefix]_mode_index''), ''per_page''=>(int)getParam(''[db_prefix]_number_index'')));\r\n return array($aCode[''code''], $aCode[''menu_top''], $aCode[''menu_bottom''], $aCode[''wrapper'']);', 1, 66, 'non,memb', 0),
('member', '998px', 'Video Albums', '_[db_prefix]_albums', 0, 0, 'PHP', 'return BxDolService::call(''videos'', ''get_profile_albums_block'', array($this->iMember, BX_DOL_URL_ROOT . ''member.php''), ''Search'');', 1, 66, 'non,memb', 0),
('profile', '998px', 'Video Albums', '_[db_prefix]_albums', 1, 6, 'PHP', 'return BxDolService::call(''videos'', ''get_profile_albums_block'', array($this->oProfileGen->_iProfileID), ''Search'');', 1, 34, 'non,memb', 0),
('profile', '998px', 'Profile Videos', '_[db_prefix]_profile_videos', 2, 8, 'PHP', 'return BxDolService::call(''videos'', ''profile_video_block'', array($this->oProfileGen->_iProfileID), ''Search'');', 1, 66, 'non,memb', 0);


INSERT INTO `sys_objects_cmts` (`ObjectName`, `TableCmts`, `TableTrack`, `AllowTags`, `Nl2br`, `SecToEdit`, `PerView`, `IsRatable`, `ViewingThreshold`, `AnimationEffect`, `AnimationSpeed`, `IsOn`, `IsMood`, `RootStylePrefix`, `TriggerTable`, `TriggerFieldId`, `TriggerFieldComments`)
VALUES ('[db_prefix]', '[db_prefix]_cmts', 'sys_cmts_track', 0, 1, 90, 5, 1, -3, 'slide', 2000, 1, 1, 'cmt', 'RayVideoFiles', 'ID', 'CommentsCount');

INSERT INTO `sys_objects_vote` (`ObjectName`, `TableRating`, `TableTrack`, `RowPrefix`, `MaxVotes`, `PostName`, `IsDuplicate`, `IsOn`, `className`, `classFile`, `TriggerTable`, `TriggerFieldRate`, `TriggerFieldRateCount`, `TriggerFieldId`) 
VALUES ('[db_prefix]', '[db_prefix]_rating', '[db_prefix]_voting_track', 'gal_', 5, 'vote_send_result', 'BX_PERIOD_PER_VOTE', 1, 'BxVideosRate', 'modules/boonex/videos/classes/BxVideosRate.php', 'RayVideoFiles', 'Rate', 'RateCount', 'ID');

INSERT INTO `sys_objects_views` (`name`, `table_track`, `period`, `trigger_table`, `trigger_field_id`, `trigger_field_views`, `is_on`)
VALUES ('[db_prefix]', '[db_prefix]_views_track', 86400, 'RayVideoFiles', 'ID', 'Views', 1);

SELECT @iTMOrder:=MAX(`Order`) FROM `sys_menu_top` WHERE `Parent`='0';
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(0, 'Videos', '_[db_prefix]_top_menu_item', 'modules/?r=videos/home/|modules/?r=videos/', @iTMOrder+1, 'non,memb', '', '', '', 1, 1, 1, 'top', 'modules/boonex/videos/|bx_videos.png', 1, '');

SET @iTMParentId = LAST_INSERT_ID( );
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(@iTMParentId, 'VideosHome', '_[db_prefix]_top_menu_home', 'modules/?r=videos/home/', 0, 'non,memb', '', '', '', 1, 1, 1, 'custom', 'modules/boonex/videos/|bx_videos.png', 0, ''),
(@iTMParentId, 'VideosAlbums', '_[db_prefix]_top_menu_albums', 'modules/?r=videos/albums/browse/all', 1, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'VideosAll', '_[db_prefix]_top_menu_all', 'modules/?r=videos/browse/all', 5, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'VideosTop', '_[db_prefix]_top_menu_top', 'modules/?r=videos/browse/top', 10, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'VideosPopular', '_[db_prefix]_top_menu_popular', 'modules/?r=videos/browse/popular', 15, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'VideosFeatured', '_[db_prefix]_top_menu_featured', 'modules/?r=videos/browse/featured', 20, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'VideosTags', '_[db_prefix]_top_menu_tags', 'modules/?r=videos/tags', 25, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'VideosCategories', '_[db_prefix]_top_menu_categories', 'modules/?r=videos/categories', 30, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'VideosRate', '_[db_prefix]_top_menu_rate', 'modules/?r=videos/rate', 31, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'VideosCalendar', '_[db_prefix]_top_menu_calendar', 'modules/?r=videos/calendar|modules/?r=videos/browse/calendar/', 35, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, ''),
(@iTMParentId, 'VideosSearch', '_[db_prefix]_top_menu_search', 'searchKeyword.php?type=bx_videos', 40, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, '');

SET @iCatProfileOrder := (SELECT MAX(`Order`)+1 FROM `sys_menu_top` WHERE `Parent` = 9 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(9, 'Videos', '_[db_prefix]_menu_profile', 'modules/?r=videos/albums/browse/owner/{profileNick}', @iCatProfileOrder, 'non,memb', '', '', '', 1, 1, 1, 'custom', '', 0, '');
SET @iCatProfileOrder := (SELECT MAX(`Order`)+1 FROM `sys_menu_top` WHERE `Parent` = 4 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(4, 'Videos', '_[db_prefix]_menu_profile', 'modules/?r=videos/albums/my/main|modules/?r=videos/albums/my/add/|modules/?r=videos/albums/my/manage/|modules/?r=videos/albums/my/disapproved/', @iCatProfileOrder, 'memb', '', '', '', 1, 1, 1, 'custom', '', 0, '');

INSERT INTO `sys_menu_top` (`Parent`, `Name`, `Caption`, `Link`, `Order`, `Visible`, `Target`, `Onclick`, `Check`, `Editable`, `Deletable`, `Active`, `Type`, `Picture`, `BQuickLink`, `Statistics`) VALUES
(0, 'VideosUnit', '_[db_prefix]_top_menu_item', 'modules/?r=videos/view/', @iTMOrder+1, 'non,memb', '', '', '', 1, 1, 1, 'system', 'modules/boonex/videos/|bx_videos.png', 0, ''),
(0, 'VideosAlbum',  '_[db_prefix]_top_menu_item', 'modules/?r=videos/browse/album/|modules/?r=videos/albums/my/edit/|modules/?r=videos/albums/my/organize/|modules/?r=videos/albums/my/add_objects/|modules/?r=videos/albums/my/manage_objects', @iTMOrder+1, 'non,memb', '', '', '', 1, 1, 1, 'system', 'modules/boonex/videos/|bx_videos.png', 0, '');

INSERT INTO `sys_permalinks`(`standard`, `permalink`, `check`) VALUES('modules/?r=videos/', 'm/videos/', '[db_prefix]_permalinks');

INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`)
VALUES ('[db_prefix]_permalinks', 'on', 26, 'Enable friendly videos permalink', 'checkbox', '', '', 0);

INSERT INTO `sys_objects_search` (`ObjectName`, `Title`, `ClassName`, `ClassPath`)
VALUES ('[db_prefix]', '_[db_prefix]', 'BxVideosSearch', 'modules/boonex/videos/classes/BxVideosSearch.php');

INSERT INTO `sys_categories` (`Category`, `ID`, `Type`, `Owner`, `Status`) VALUES
('Profile videos', 0, 'bx_videos', 0, 'active'),
('Our lovely kids', 0, 'bx_videos', 0, 'active'),
('It is me!', 0, 'bx_videos', 0, 'active'),
('Fun', 0, 'bx_videos', 0, 'active'),
('Music clips', 0, 'bx_videos', 0, 'active'),
('About vacations', 0, 'bx_videos', 0, 'active');

INSERT INTO `sys_objects_categories` (`ObjectName`, `Query`, `PermalinkParam`, `EnabledPermalink`, `DisabledPermalink`, `LangKey`) 
VALUES ('[db_prefix]', 'SELECT `Categories` FROM `RayVideoFiles` WHERE `ID`  = {iID} AND `Status` = ''approved''', '[db_prefix]_permalinks', 'm/videos/browse/category/{tag}', 'modules/?r=videos/browse/category/{tag}', '_[db_prefix]');

INSERT INTO `sys_objects_tag` (`ObjectName`, `Query`, `PermalinkParam`, `EnabledPermalink`, `DisabledPermalink`, `LangKey`)
VALUES ('[db_prefix]', 'SELECT `Tags` FROM `RayVideoFiles` WHERE `ID` = {iID} AND `Status` = ''approved''', '[db_prefix]_permalinks', 'm/videos/browse/tag/{tag}', 'modules/?r=videos/browse/tag/{tag}', '_[db_prefix]');

INSERT INTO `sys_objects_actions` (`Type`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`) VALUES
('[db_prefix]', '_[db_prefix]_action_share', 'action_share.png', '', 'window.open(''{moduleUrl}share/{fileUri}'', ''_blank'', ''width=500,height=380,menubar=no,status=no,resizable=no,scrollbars=yes,toolbar=no,location=no'')', '', 1),
('[db_prefix]', '{evalResult}', 'action_report.png', '', 'window.open(''{moduleUrl}report/{fileUri}'', ''_blank'', ''width=500,height=380,menubar=no,status=no,resizable=no,scrollbars=yes,toolbar=no,location=no'')', 'if ({iViewer}!=0)\r\nreturn _t(''_[db_prefix]_action_report'');\r\nelse\r\nreturn null;', 2),
('[db_prefix]', '{evalResult}', 'action_fave.png', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}favorite/{ID}''); return false;', 'if ({iViewer}==0)\r\nreturn false;\r\n$sMessage = ''{favorited}''=='''' ? ''fave'':''unfave'';\r\nreturn _t(''_[db_prefix]_action_'' . $sMessage); ', 3),
('[db_prefix]', '{evalResult}', 'edit.png', '', 'window.open(''{moduleUrl}edit/{ID}'', ''_blank'', ''width=500,height=380,menubar=no,status=no,resizable=no,scrollbars=yes,toolbar=no,location=no'') ', '$sTitle = _t(''_Edit'');\r\nif ({Owner} == {iViewer})\r\nreturn $sTitle;\r\n$mixedCheck = BxDolService::call(''videos'', ''check_action'', array(''edit'',''{ID}''));\r\nif ($mixedCheck !== false)\r\nreturn $sTitle;\r\nelse\r\n return null;', 5),
('[db_prefix]', '{evalResult}', 'action_block.png', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{moduleUrl}delete/{ID}/{AlbumUri}/{OwnerName}'');return false;', '$sTitle = _t(''_Delete'');\r\nif ({Owner} == {iViewer})\r\nreturn $sTitle;\r\n$mixedCheck = BxDolService::call(''videos'', ''check_delete'', array({ID}));\r\nif ($mixedCheck !== false)\r\nreturn $sTitle;\r\nelse\r\nreturn null;', 6),
('[db_prefix]_title', '{evalResult}', 'action_fave.png', '{BaseUri}albums/my/add/', '', 'return $GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin''] ? _t(''_[db_prefix]_albums_add'') : '''';', 7),
('[db_prefix]_title', '{evalResult}', 'modules/boonex/videos/|pvi.png', '{BaseUri}albums/my/main/', '', 'return $GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin''] ? _t(''_[db_prefix]_albums_my'') : '''';', 8),
('[db_prefix]_title', '{evalResult}', 'modules/boonex/videos/|pvi.png', '{BaseUri}', '', 'return $GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin''] ? _t(''_[db_prefix]_home'') : '''';', 9);

INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`) VALUES 
('t_[db_prefix]_share', 'Someone from <SiteName> shared video with you', '<html><head></head><body style="font: 12px Verdana; color:#000000">\r\n<p><b>Hello</b>,</p>\r\n\r\n<p><SenderNickName> shared a <MediaType> with you: <a href="<MediaUrl>">See It</a>!</p>\r\n\r\n</p>\r\n\r\n<UserExplanation></p>\r\n\r\n</p>\r\n\r\n<p>---</p>\r\nBest regards,  <SiteName> \r\n<p style="font: bold 10px Verdana; color:red">!!!Auto-generated e-mail, please, do not reply!!!</p></body></html>', 'Message about sharing files.'),
('t_[db_prefix]_report', '<SenderNickName> reported about video from <SiteName>', '<html><head></head><body style="font: 12px Verdana; color:#000000">\r\n<p><b>Hello</b>,</p>\r\n\r\n<p>Message about <MediaType>: <a href="<MediaUrl>">See It</a>!</p>\r\n\r\n</p>\r\n\r\n<UserExplanation></p>\r\n\r\n</p>\r\n\r\n<p>---</p>\r\nBest regards,  <SiteName> \r\n<p style="font: bold 10px Verdana; color:red">!!!Auto-generated e-mail, please, do not reply!!!</p></body></html>', 'Message about shared file.');

INSERT INTO `sys_stat_member` (`Type`, `SQL`) VALUES
('pvi', 'SELECT COUNT(*) FROM `RayVideoFiles` WHERE `Owner` = ''__member_id__'' AND `Status` = ''approved''');

INSERT INTO `sys_stat_site` (`Name`, `Title`, `UserLink`, `UserQuery`, `AdminLink`, `AdminQuery`, `IconName`, `StatOrder`) VALUES
('pvi', '[db_prefix]', 'modules/?r=videos/home/', 'SELECT COUNT(`ID`) FROM `RayVideoFiles` WHERE `Status`=''approved''', 'modules/?r=videos/home/', 'SELECT COUNT(`ID`) FROM `RayVideoFiles` WHERE `Status`=''approved''', 'modules/boonex/videos/|pvi.png', 0);

SET @iLevelNonMember := 1;
SET @iLevelStandard  := 2;
SET @iLevelPromotion := 3;

INSERT INTO `sys_acl_actions` (`Name`) VALUES ('videos view');
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelNonMember, @iAction), (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` (`Name`) VALUES ('videos add');
SET @iAction := LAST_INSERT_ID();
INSERT INTO `sys_acl_matrix` (`IDLevel`, `IDAction`) VALUES 
    (@iLevelStandard, @iAction), (@iLevelPromotion, @iAction);

INSERT INTO `sys_acl_actions` (`Name`) VALUES
('videos delete'), ('videos approve'), ('videos edit');

INSERT INTO `sys_privacy_actions` (`module_uri`, `name`, `title`, `default_group`) VALUES
('videos', 'album_view', '_[db_prefix]_album_view', '3');

SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT IGNORE INTO `sys_menu_admin` (`parent_id`, `name`, `title`, `url`, `description`, `icon`, `order`) VALUES
(2, '[db_prefix]', '_[db_prefix]', '{siteUrl}modules/?r=videos/administration', 'Videos module by BoonEx', 'modules/boonex/videos/|pvi.png', @iMax+1);

INSERT INTO `sys_cron_jobs` (`name`, `time`, `class`, `file`, `eval`) VALUES
('[db_prefix]', '*/5', 'BxDolCronVideo', 'flash/modules/video/inc/classes/BxDolCronVideo.php', '');

INSERT INTO `sys_menu_member` (`Name`, `Eval`, `Type`, `Parent`) VALUES
('[db_prefix]', 'return BxDolService::call(''videos'', ''get_member_menu_item'', array({ID}));', 'linked_item', '1');

INSERT INTO `sys_objects_actions`(`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
('{sbs_[db_prefix]_title}', 'action_subscribe.png', '', '{sbs_[db_prefix]_script}', '', 7, '[db_prefix]', 0);

INSERT INTO `sys_sbs_types`(`unit`, `action`, `template`, `params`) VALUES
('[db_prefix]', '', '', 'return BxDolService::call(''videos'', ''get_subscription_params'', array($arg2, $arg3));'),
('[db_prefix]', 'commentPost', 't_sbs_[db_prefix]_comments', 'return BxDolService::call(''videos'', ''get_subscription_params'', array($arg2, $arg3));'),
('[db_prefix]', 'rate', 't_sbs_[db_prefix]_rates', 'return BxDolService::call(''videos'', ''get_subscription_params'', array($arg2, $arg3));');

INSERT INTO `sys_email_templates`(`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_sbs_[db_prefix]_comments', 'New video comments', '<html><head></head><body style="font: 12px Verdana; color:#000000"> <p><b>Dear <RealName></b>,</p><br /><p>The video you subscribed to has new comments!</p><br /> <p>Click <a href="<ViewLink>">here</a> to view them.</p><br /> <p><b>Thank you for using our services!</b></p> <p>--</p> <p style="font: bold 10px Verdana; color:red"><SiteName> mail delivery system!!! <br />Auto-generated e-mail, please, do not reply!!!</p></body></html>', 'New video comments subscription.', '0'),
('t_sbs_[db_prefix]_rates', 'Video was rated', '<html><head></head><body style="font: 12px Verdana; color:#000000"> <p><b>Dear <RealName></b>,</p><br /><p>The video you subscribed to was rated!</p><br /> <p>Click <a href="<ViewLink>">here</a> to view it.</p><br /> <p><b>Thank you for using our services!</b></p> <p>--</p> <p style="font: bold 10px Verdana; color:red"><SiteName> mail delivery system!!! <br />Auto-generated e-mail, please, do not reply!!!</p></body></html>', 'New video rates subscription.', '0');

INSERT INTO `sys_alerts_handlers` (`name`, `eval`) VALUES ('[db_prefix]_profile_delete', 'BxDolService::call(''videos'', ''response_profile_delete'', array($this));');
SET @iHandler := LAST_INSERT_ID();
INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES ('profile', 'delete', @iHandler);