<?php
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

bx_groups_import ('FormEdit');

class BxGroupsFormUploadMedia extends BxGroupsFormEdit {

    function BxGroupsFormUploadMedia ($oMain, $iProfileId, $iEntryId, &$aDataEntry, $sMedia, $aMediaFields) {

        parent::BxGroupsFormEdit ($oMain, $iProfileId, $iEntryId, $aDataEntry);

        foreach ($this->_aMedia as $k => $a) {
            if ($k == $sMedia)
                continue;
            unset($this->_aMedia[$k]);
        }

        array_push($aMediaFields, 'Submit', 'id');

        foreach ($this->aInputs as $k => $a) {
            if (in_array($k, $aMediaFields))
                continue;
            unset($this->aInputs[$k]);
        }
    }

}

?>
