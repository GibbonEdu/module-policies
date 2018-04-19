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

//Module includes
include './modules/Policies/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Policies/policies_view.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) { echo "<div class='error'>";
        echo 'The highest grouped action cannot be determined.';
        echo '</div>';
    } else {
        echo "<div class='trail'>";
        echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > </div><div class='trailEnd'>View Policies</div>";
        echo '</div>';

        $allPolicies = '';
        if (isset($_GET['allPolicies'])) {
            $allPolicies = $_GET['allPolicies'];
        }

        //Build role lookup array
        $allRoles = array();
        try {
            $dataRoles = array();
            $sqlRoles = 'SELECT * FROM gibbonRole';
            $resultRoles = $connection2->prepare($sqlRoles);
            $resultRoles->execute($dataRoles);
        } catch (PDOException $e) {
        }
        while ($rowRoles = $resultRoles->fetch()) {
            $allRoles[$rowRoles['gibbonRoleID']] = $rowRoles['name'];
        }

        echo '<p>';
        if ($highestAction == 'View Policies_all') {
            echo 'On this page you can see all policies for which you are a member of the designated audience. To view a policy, click on its name in the left-hand column below. As a privileged user, you can also override audience restrictions, and view all policies.';
        } else {
            echo 'On this page you can see all policies for which you are a member of the designated audience. To view a policy, click on its name in the left-hand column below.';
        }
        echo '</p>';

        if ($highestAction == 'View Policies_all') {
            echo "<h3 class='top'>";
            echo 'Filters';
            echo '</h3>';
            ?>
			<form method="get" action="<?php echo $_SESSION[$guid]['absoluteURL']?>/index.php">
				<table class='smallIntBorder' cellspacing='0' style="width: 100%">
					<tr>
						<td>
							<b>All Policies</b><br/>
							<span style="font-size: 90%"><i>Override audience to reveal all policies.</i></span>
						</td>
						<td class="right">
							<?php
                            $checked = '';
							if ($allPolicies == 'on') {
								$checked = 'checked';
							}
							echo "<input $checked name=\"allPolicies\" id=\"allPolicies\" type=\"checkbox\">";
							?>
						</td>
					</tr>
					<tr>
						<td colspan=2 class="right">
							<input type="hidden" name="q" value="/modules/<?php echo $_SESSION[$guid]['module'] ?>/policies_view.php">
							<input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
							<input type="submit" value="Submit">
						</td>
					</tr>
				</table>
			</form>
			<?php

        }

        try {
            if ($allPolicies == 'on') {
                $data = array();
                $sql = "SELECT policiesPolicy.*, gibbonDepartment.name AS department, gibbonPerson.surname, gibbonPerson.preferredName, gibbonPerson.title FROM policiesPolicy JOIN gibbonPerson ON (policiesPolicy.gibbonPersonIDCreator=gibbonPerson.gibbonPersonID) LEFT JOIN gibbonDepartment ON (policiesPolicy.gibbonDepartmentID=gibbonDepartment.gibbonDepartmentID) WHERE active='Y' ORDER BY scope, gibbonDepartment.name, category, policiesPolicy.name";
            } else {
                $data = array();
                $idWhere = "(";
                $data["role"] = '%'.$_SESSION[$guid]['gibbonRoleIDCurrent'].'%';
                $idWhere .= "gibbonRoleIDList LIKE :role";
                $roleCategory = getRoleCategory($_SESSION[$guid]['gibbonRoleIDCurrent'], $connection2);
                if ($roleCategory == 'Parent' || $roleCategory == 'Staff' || $roleCategory == 'Student') {
                    $idWhere .= " OR ".strtolower($roleCategory)."='Y'";
                }
                if ($idWhere == '(') {
                    $idWhere = '';
                } else {
                    $idWhere .= ")";
                }

                $sql = "SELECT policiesPolicy.*, gibbonDepartment.name AS department, gibbonPerson.surname, gibbonPerson.preferredName, gibbonPerson.title FROM policiesPolicy JOIN gibbonPerson ON (policiesPolicy.gibbonPersonIDCreator=gibbonPerson.gibbonPersonID) LEFT JOIN gibbonDepartment ON (policiesPolicy.gibbonDepartmentID=gibbonDepartment.gibbonDepartmentID) WHERE $idWhere ORDER BY scope, gibbonDepartment.name, category, policiesPolicy.name";
            }
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() < 1) {
            echo '<div class="error">';
            echo 'There are no policies for you to view';
            echo '</div>';
        } else {
            $lastHeader = '';
            $headerCount = 0;
            $count = 0;
            while ($row = $result->fetch()) {
                if ($count % 2 == 0) {
                    $rowNum = 'even';
                } else {
                    $rowNum = 'odd';
                }
                ++$count;

                if ($row['scope'] == 'School') {
                    $currentHeader = 'School';
                } else {
                    $currentHeader = $row['department'];
                }

                if ($currentHeader != $lastHeader) {
                    if ($lastHeader != '') {
                        echo '</tr>';
                        echo '</table>';
                    }

                    echo "<h2>$currentHeader</h2>";

                    $count = 0;
                    $rowNum = 'odd';
                    echo "<table cellspacing='0' style='width: 100%'>";
                    echo "<tr class='head'>";
                    echo "<th style='width: 200px'>";
                    echo "Name<br/><span style='font-style: italic; font-size: 85%'>Short Name</span>";
                    echo '</th>';
                    echo "<th style='width: 200px'>";
                    echo 'Category';
                    echo '</th>';
                    echo "<th style='width: 150px'>";
                    echo 'Audience';
                    echo '</th>';
                    echo "<th style='width: 200px'>";
                    echo 'Created By';
                    echo '</th>';
                    echo "<th style='width: 60px'>";
                    echo 'Action';
                    echo '</th>';
                    echo '</tr>';

                    ++$headerCount;
                }

                echo "<tr class=$rowNum>";
                echo '<td>';
                if ($row['type'] == 'File') {
                    echo "<a style='font-weight: bold' href='".$_SESSION[$guid]['absoluteURL'].'/'.$row['location']."'>".$row['name'].'</a><br/>';
                } elseif ($row['type'] == 'Link') {
                    echo "<a style='font-weight: bold' target='_blank' href='".$row['location']."'>".$row['name'].'</a><br/>';
                }
                echo "<span style='font-style: italic; font-size: 85%'>".$row['nameShort'].'</span>';
                echo '</td>';
                echo '<td>';
                echo '<b>'.$row['category'].'</b>';
                echo '</td>';
                echo '<td>';
                if ($row['gibbonRoleIDList'] == '' && $row['parent'] == 'N' && $row['staff'] == 'N' && $row['student'] == 'N') {
                    echo '<i>No audience set</i>';
                } else {
                    if ($row['gibbonRoleIDList'] != '') {
                        $roles = explode(',', $row['gibbonRoleIDList']);
                        foreach ($roles as $role) {
                            echo $allRoles[$role].'<br/>';
                        }
                    }
                    if ($row['parent'] == 'Y') {
                        print "Parents<br/>";
                    }
                    if ($row['staff'] == 'Y') {
                        print "Staff<br/>";
                    }
                    if ($row['student'] == 'Y') {
                        print "Students<br/>";
                    }
                }
                echo '</td>';
                echo '<td>';
                echo formatName($row['title'], $row['preferredName'], $row['surname'], 'Staff');
                echo '</td>';
                echo '<td>';
                echo "<script type='text/javascript'>";
                echo '$(document).ready(function(){';
                echo "\$(\".comment-$count\").hide();";
                echo "\$(\".show_hide-$count\").fadeIn(1000);";
                echo "\$(\".show_hide-$count\").click(function(){";
                echo "\$(\".comment-$count\").fadeToggle(1000);";
                echo '});';
                echo '});';
                echo '</script>';
                if ($row['description'] != '') {
                    echo "<a class='show_hide-$count' onclick='false' href='#'><img style='padding-right: 5px' src='".$_SESSION[$guid]['absoluteURL']."/themes/Default/img/page_down.png' title='Show Description' onclick='return false;' /></a>";
                }
                echo '</td>';
                echo '</tr>';
                if ($row['description'] != '') {
                    echo "<tr class='comment-$count' id='comment-$count'>";
                    echo "<td style='background-color: #fff' colspan=5>";
                    echo $row['description'];
                    echo '</td>';
                    echo '</tr>';
                }

                $lastHeader = $currentHeader;
            }
            echo '</tr>';
            echo '</table>';
        }
    }
}
?>
