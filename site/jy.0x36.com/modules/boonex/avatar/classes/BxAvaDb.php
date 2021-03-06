<?
/***************************************************************************
*                            Dolphin Smart Community Builder
*                              -------------------
*     begin                : Mon Mar 23 2006
*     copyright            : (C) 2007 BoonEx Group
*     website              : http://www.boonex.com
* This file is part of Dolphin - Smart Community Builder
*
* Dolphin is free software; you can redistribute it and/or modify it under
* the terms of the GNU General Public License as published by the
* Free Software Foundation; either version 2 of the
* License, or  any later version.
*
* Dolphin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
* without even the implied warranty of  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
* See the GNU General Public License for more details.
* You should have received a copy of the GNU General Public License along with Dolphin,
* see license.txt file; if not, write to marketing@boonex.com
***************************************************************************/

bx_import('BxDolModuleDb');

define('BX_AVATAR_TABLE_PREFIX', 'bx_avatar_');

/*
 * Avatar module Data
 */
class BxAvaDb extends BxDolModuleDb {	
    var $_oConfig;
	/*
	 * Constructor.
	 */
	function BxAvaDb(&$oConfig) {
		parent::BxDolModuleDb();
		
		$this->_oConfig = $oConfig;
	}

    function updateProfile ($iAvatar, $iOwner, $isAdmin) {

        if (-1 == $iAvatar)
            $iAvatar = (int)$this->getOne ("SELECT `id` FROM `" . BX_AVATAR_TABLE_PREFIX . "images` WHERE `author_id` = $iOwner LIMIT 1");
               
        if ((int)$iOwner)
            return $this->query("UPDATE `Profiles` SET `Avatar` = '$iAvatar' WHERE `ID` = " . (int)$iOwner);
        else
            return false;
    }

    function getCurrentAvatar ($iOwner, $isAdmin) {
        if ((int)$iOwner)
            return $this->getOne("SELECT `Avatar` FROM `Profiles` WHERE `ID` = " . (int)$iOwner);
        else
            return 0;
    }

    function getAvatarByIdAndOwner ($iId, $iOwner, $isAdmin) {
        $sWhere = '';
        if (!$isAdmin) 
            $sWhere = " AND `author_id` = '$iOwner' ";
        return $this->getRow ("SELECT * FROM `" . BX_AVATAR_TABLE_PREFIX . "images` WHERE `id` = $iId $sWhere LIMIT 1");
    }

    function getAvatarsByAuthor($iProfileId) {
        return $this->getPairs ("SELECT `id` FROM `" . BX_AVATAR_TABLE_PREFIX . "images` WHERE `author_id` = '$iProfileId'", 'id', 'id');
    }

    function addAvatar($iProfileId) {
        if (!$this->query ("INSERT INTO `" . BX_AVATAR_TABLE_PREFIX . "images` SET `author_id` = '$iProfileId'"))
            return false;
        return $this->lastId();
    }

    function deleteAvatarByIdAndOwner ($iId, $iOwner, $isAdmin) {
        $sWhere = '';
        if (!$isAdmin)
            $sWhere = " AND `author_id` = '$iOwner' ";
        if (!($iRet = $this->query ("DELETE FROM `" . BX_AVATAR_TABLE_PREFIX . "images` WHERE `id` = $iId $sWhere LIMIT 1")))
            return false;
        return true;
    }

    function getSettingsCategory() {
        return $this->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Avatar' LIMIT 1");
    }

    function suspendProfile($iProfileId) {
        return $this->query("UPDATE `Profiles` SET `Status` = 'Approval' WHERE `ID` = $iProfileId AND `Status` = 'Active' LIMIT 1");
    }
}

?>