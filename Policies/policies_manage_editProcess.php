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

include '../../gibbon.php';

include './moduleFunctions.php';

$policiesPolicyID = $_GET['policiesPolicyID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/policies_manage_edit.php&policiesPolicyID=$policiesPolicyID&search=".$_GET['search'];

if (isActionAccessible($guid, $connection2, '/modules/Policies/policies_manage_edit.php') == false) {
    //Fail 0
    $URL = $URL.'&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($policiesPolicyID == '') {
        //Fail1
        $URL = $URL.'&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('policiesPolicyID' => $policiesPolicyID);
            $sql = 'SELECT * FROM policiesPolicy WHERE policiesPolicyID=:policiesPolicyID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            //Fail2
            $URL = $URL.'&deleteReturn=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            //Fail 2
            $URL = $URL.'&return=error2';
            header("Location: {$URL}");
        } else {
            $row = $result->fetch();

            //Validate Inputs
            $name = $_POST['name'];
            $nameShort = $_POST['nameShort'];
            $active = $_POST['active'];
            $category = $_POST['category'];
            $description = $_POST['description'];
            $location = $row['location'];
            if ($row['type'] == 'Link' && !empty($_POST['link'])) {
                $location = isset($_POST['link'])? $_POST['link'] : '';
            } else if ($row['type'] == 'File') {
                $location = isset($_POST['attachment'])? $_POST['attachment'] : '';
            }
            $gibbonRoleIDList = isset($_POST['gibbonRoleIDList'])? $_POST['gibbonRoleIDList'] : array();
            $gibbonRoleIDList = implode(',', $gibbonRoleIDList);

            $roleCategories = isset($_POST['roleCategories'])? $_POST['roleCategories'] : array();
            $staff = in_array('staff', $roleCategories)? 'Y' : 'N';
            $student = in_array('student', $roleCategories)? 'Y' : 'N';
            $parent = in_array('parent', $roleCategories)? 'Y' : 'N';

            if ($name == '' or $nameShort == '' or $active == '') {
                //Fail 3
                $URL = $URL.'&return=error3';
                header("Location: {$URL}");
            } else {

                if (!empty($_FILES['file']['tmp_name'])) {
                    $fileUploader = new Gibbon\FileUploader($pdo, $gibbon->session);

                    $file = (isset($_FILES['file']))? $_FILES['file'] : null;

                    // Upload the file, return the /uploads relative path
                    $location = $fileUploader->uploadFromPost($file, 'policy_');

                    if (empty($location)) {
                        //Fail 5
                        $URL = $URL.'&return=error5';
                        header("Location: {$URL}");
                        exit;
                    }
                }

                //Write to database
                try {
                    $data = array('name' => $name, 'nameShort' => $nameShort, 'active' => $active, 'category' => $category, 'description' => $description, 'gibbonRoleIDList' => $gibbonRoleIDList, 'parent' => $parent, 'staff' => $staff, 'student' => $student, 'location' => $location, 'policiesPolicyID' => $policiesPolicyID);
                    $sql = 'UPDATE policiesPolicy SET name=:name, nameShort=:nameShort, active=:active, category=:category, description=:description, gibbonRoleIDList=:gibbonRoleIDList, parent=:parent, staff=:staff, student=:student, location=:location WHERE policiesPolicyID=:policiesPolicyID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    //Fail 2
                    $URL = $URL.'&return=error5';
                    header("Location: {$URL}");
                    exit();
                }

                //Success 0
                $URL = $URL.'&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
