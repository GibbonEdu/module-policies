<?php
/*
Gibbon: the flexible, open school platform
Founded by Ross Parker at ICHK Secondary. Built by Ross Parker, Sandra Kuipers and the Gibbon community (https://gibbonedu.org/about/)
Copyright © 2010, Gibbon Foundation
Gibbon™, Gibbon Education Ltd. (Hong Kong)

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

use Gibbon\Forms\Prefab\DeleteForm;
use Gibbon\Module\Policies\PoliciesGateway;

//Module includes
include './modules/Policies/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Policies/policies_manage_delete.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $policiesPolicyID = $_GET['policiesPolicyID'] ?? '';
    if ($policiesPolicyID == '') {
        echo "<div class='error'>";
        echo __('You have not specified a policy.');
        echo '</div>';
    } else {
        $policies = $container->get(PoliciesGateway::class);
        $data = array('policiesPolicyID' => $policiesPolicyID);

        if (!$policies->selectPolicyById($data)) {
            echo "<div class='error'>";
            echo __('The selected policy does not exist.');
            echo '</div>';
        } else {
            //Let's go!
            $form = DeleteForm::createForm($session->get('absoluteURL')."/modules/Policies/policies_manage_deleteProcess.php?policiesPolicyID=".$policiesPolicyID."&search=".$_GET['search']);
            echo $form->getOutput();
        }
    }
}
