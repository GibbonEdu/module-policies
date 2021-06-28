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

$search = $_GET['search'] ?? '';

$URL = $session->get('absoluteURL').'/index.php?q=/modules/'.getModuleName($_POST['address']).'/policies_manage_add.php&search='.$search;

if (isActionAccessible($guid, $connection2, '/modules/Policies/policies_manage_add.php') == false) {
    //Fail 0
    $URL = $URL.'&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $scope = $_POST['scope'] ?? '';
    $gibbonDepartmentID = $_POST['gibbonDepartmentID'] ?? null;
    $name = $_POST['name'] ?? '';
    $nameShort = $_POST['nameShort'] ?? '';
    $active = $_POST['active'] ?? '';
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    $type = $_POST['type'] ?? '';
    $link = $_POST['link'] ?? '';

    $gibbonRoleIDList = isset($_POST['gibbonRoleIDList'])? $_POST['gibbonRoleIDList'] : array();
    $gibbonRoleIDList = implode(',', $gibbonRoleIDList);

    $roleCategories = isset($_POST['roleCategories'])? $_POST['roleCategories'] : array();
    $staff = in_array('staff', $roleCategories)? 'Y' : 'N';
    $student = in_array('student', $roleCategories)? 'Y' : 'N';
    $parent = in_array('parent', $roleCategories)? 'Y' : 'N';

    if ($scope == '' or ($scope == 'Department' and is_null($gibbonDepartmentID)) or $name == '' or $nameShort == '' or $active == '' or $type == '' or ($type == 'Link' and $link == '')) {
        //Fail 3
        $URL = $URL.'&return=error3';
        header("Location: {$URL}");
    } else {
        $partialFail = false;
        if ($type == 'Link') {
            $location = $link;
        } else {
            //Check extension to see if allowed
            try {
                $ext = explode('.', $_FILES['file']['name']);
                $dataExt = array('extension' => end($ext));
                $sqlExt = 'SELECT * FROM gibbonFileExtension WHERE extension=:extension';
                $resultExt = $connection2->prepare($sqlExt);
                $resultExt->execute($dataExt);
            } catch (PDOException $e) {
                $partialFail = true;
            }

            if ($resultExt->rowCount() != 1) {
                $partialFail = true;
            } else {
                //Move attached image  file, if there is one
                if (!empty($_FILES['file']['tmp_name'])) {
                    $fileUploader = new Gibbon\FileUploader($pdo, $session);

                    $file = (isset($_FILES['file']))? $_FILES['file'] : null;

                    // Upload the file, return the /uploads relative path
                    $location = $fileUploader->uploadFromPost($file, 'policy_');

                    if (empty($location)) {
                        //Fail 5
                        $URL = $URL.'&return=error5';
                        header("Location: {$URL}");
                    }
                }
                else {
                    //Fail 5
                    $URL = $URL.'&return=error5';
                    header("Location: {$URL}");
                }
            }
        }

        if ($partialFail == true) {
            //Fail 5
            $URL = $URL.'&return=error5';
            header("Location: {$URL}");
            exit();
        } else {
            //Write to database
            $policies = $container->get(PoliciesGateway::class);
            try {
                $data = array('scope' => $scope, 'gibbonDepartmentID' => $gibbonDepartmentID, 'name' => $name, 'nameShort' => $nameShort, 'active' => $active, 'category' => $category, 'description' => $description, 'type' => $type, 'location' => $location, 'gibbonRoleIDList' => $gibbonRoleIDList, 'parent' => $parent, 'staff' => $staff, 'student' => $student, 'gibbonPersonIDCreator' => $session->get('gibbonPersonID'), 'timestampCreated' => date('Y-m-d H:i:s'));
                $policies->insertPolicy($data);
            } catch (Exception $e) {
                //Fail 2
                $URL = $URL.'&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $AI = str_pad($connection2->lastInsertID(), 8, '0', STR_PAD_LEFT);

            //Success 0
            $URL = $URL.'&return=success0&policiesPolicyID='.$AI;
            header("Location: {$URL}");
        }
    }
}
