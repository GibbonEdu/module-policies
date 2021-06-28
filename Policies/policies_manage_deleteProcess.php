<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
use Gibbon\Module\Policies\PoliciesGateway;

include '../../gibbon.php';

include './moduleFunctions.php';

$policiesPolicyID = $_GET['policiesPolicyID'] ?? '';
$URL = $session->get('absoluteURL').'/index.php?q=/modules/'.getModuleName($_POST['address'])."/policies_manage_delete.php&policiesPolicyID=$policiesPolicyID&search=".$_GET['search'];
$URLDelete = $session->get('absoluteURL').'/index.php?q=/modules/'.getModuleName($_POST['address']).'/policies_manage.php&search='.$_GET['search'];

if (isActionAccessible($guid, $connection2, '/modules/Policies/policies_manage_delete.php') == false) {
    //Fail 0
    $URL = $URL.'&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    if ($policiesPolicyID == '') {
        //Fail1
        $URL = $URL.'&return=error1';
        header("Location: {$URL}");
    } else {
        $policies = $container->get(PoliciesGateway::class);
        $data = array('policiesPolicyID' => $policiesPolicyID);

        if (!$policies->selectPolicyById($data)) {
            //Fail 2
            $URL = $URL.'&return=error2';
            header("Location: {$URL}");
        } else {
            //Write to database
            $data = array('policiesPolicyID' => $policiesPolicyID);
            $policies->deletePolicy($data);
            //Success 0
            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
