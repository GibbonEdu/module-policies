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

@session_start();

//Module includes
include './modules/Policies/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Policies/policies_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/policies_manage.php'>Manage Policies</a> > </div><div class='trailEnd'>Add Policy</div>";
    echo '</div>';

    $returns = array();
    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Policies/policies_manage_edit.php&policiesPolicyID='.$_GET['editID'].'&search='.$_GET['search'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, $returns);
    }


    if ($_GET['search'] != '') { echo "<div class='linkTop'>";
        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Policies/policies_manage.php&search='.$_GET['search']."'>Back to Search Results</a>";
        echo '</div>';
    }

    ?>
	<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/Policies/policies_manage_addProcess.php?search='.$_GET['search'] ?>" enctype="multipart/form-data">
		<table class='smallIntBorder' cellspacing='0' style="width: 100%">
			<tr>
				<td>
					<b>Scope *</b><br/>
					<span style="font-size: 90%"><i></i></span>
				</td>
				<td class="right">
					<select name="scope" id="scope" style="width: 302px">
						<option value="Please select...">Please select...</option>
						<option value="School">School</option>
						<option value="Department">Department</option>
					</select>
					<script type="text/javascript">
						var scope=new LiveValidation('scope');
						scope.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "Select something!"});
					 </script>
				</td>
			</tr>

			<script type="text/javascript">
				$(document).ready(function(){
					$("#learningAreaRow").css("display","none");

					$("#scope").change(function(){
						if ($('#scope option:selected').val() == "Department" ) {
							$("#learningAreaRow").slideDown("fast", $("#learningAreaRow").css("display","table-row")); //Slide Down Effect
							gibbonDepartmentID.enable();
						}
						else {
							$("#learningAreaRow").css("display","none");
							gibbonDepartmentID.disable();
						}
					 });
				});
			</script>
			<tr id='learningAreaRow'>
				<td>
					<b>Department *</b><br/>
					<span style="font-size: 90%"><i></i></span>
				</td>
				<td class="right">
					<?php
                    try {
                        $dataSelect = array();
                        $sqlSelect = 'SELECT * FROM gibbonDepartment ORDER BY name';
                        $resultSelect = $connection2->prepare($sqlSelect);
                        $resultSelect->execute($dataSelect);
                    } catch (PDOException $e) {
                    }
    				?>
					<select name="gibbonDepartmentID" id="gibbonDepartmentID" style="width: 302px">
						<option value="Please select...">Please select...</option>
						<?php
                        while ($rowSelect = $resultSelect->fetch()) {
                            echo "<option value='".$rowSelect['gibbonDepartmentID']."'>".$rowSelect['name'].'</option>';
                        }
   						?>
					</select>
					<script type="text/javascript">
						var gibbonDepartmentID=new LiveValidation('gibbonDepartmentID');
						gibbonDepartmentID.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "Select something!"});
					</script>
				</td>
			</tr>
			<tr>
				<td>
					<b>Name *</b><br/>
				</td>
				<td class="right">
					<input name="name" id="name" maxlength=100 value="" type="text" style="width: 300px">
					<script type="text/javascript">
						var name=new LiveValidation('name');
						name.add(Validate.Presence);
					</script>
				</td>
			</tr>
			<tr>
				<td>
					<b>Name Short *</b><br/>
				</td>
				<td class="right">
					<input name="nameShort" id="nameShort" maxlength=14 value="" type="text" style="width: 300px">
					<script type="text/javascript">
						var nameShort=new LiveValidation('nameShort');
						nameShort.add(Validate.Presence);
					</script>
				</td>
			</tr>
			<tr>
				<td>
					<b>Active *</b><br/>
					<span style="font-size: 90%"><i></i></span>
				</td>
				<td class="right">
					<select name="active" id="active" style="width: 302px">
						<option value="Y">Y</option>
						<option value="N">N</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<b>Category</b><br/>
				</td>
				<td class="right">
					<input name="category" id="category" maxlength=50 value="" type="text" style="width: 300px">
					<script type="text/javascript">
						$(function() {
							var availableTags=[
								<?php
                                try {
                                    $dataAuto = array();
                                    $sqlAuto = 'SELECT DISTINCT category FROM policiesPolicy ORDER BY category';
                                    $resultAuto = $connection2->prepare($sqlAuto);
                                    $resultAuto->execute($dataAuto);
                                } catch (PDOException $e) {
                                }

								while ($rowAuto = $resultAuto->fetch()) {
									echo '"'.$rowAuto['category'].'", ';
								}
								?>
							];
							$( "#category" ).autocomplete({source: availableTags});
						});
					</script>
				</td>
			</tr>
			<tr>
				<td>
					<b>Description</b><br/>
				</td>
				<td class="right">
					<textarea name='description' id='description' rows=5 style='width: 300px'></textarea>
				</td>
			</tr>
			<tr>
				<td>
					<b>Type *</b><br/>
				</td>
				<td class="right">
					<input checked type="radio" id="type" name="type" class="type" value="Link" /> Link
					<input type="radio" id="type" name="type" class="type" value="File" /> File
				</td>
			</tr>
			<script type="text/javascript">
				/* Subbmission type control */
				$(document).ready(function() {
					$("#fileRow").css("display","none");
					$("#linkRow").slideDown("fast", $("#linkRow").css("display","table-row")); //Slide Down Effect

					$(".type").click(function(){
						if ($('input[name=type]:checked').val() == "Link" ) {
							$("#fileRow").css("display","none");
							$("#linkRow").slideDown("fast", $("#linkRow").css("display","table-row")); //Slide Down Effect
						} else {
							$("#linkRow").css("display","none");
							$("#fileRow").slideDown("fast", $("#fileRow").css("display","table-row")); //Slide Down Effect
						}
					 });
				});
			</script>

			<tr id="fileRow">
				<td>
					<b>Policy File *</b><br/>
				</td>
				<td class="right">
					<input type="file" name="file" id="file"><br/><br/>
					<?php
                    echo getMaxUpload($guid);

                    //Get list of acceptable file extensions
                    try {
                        $dataExt = array();
                        $sqlExt = 'SELECT * FROM gibbonFileExtension';
                        $resultExt = $connection2->prepare($sqlExt);
                        $resultExt->execute($dataExt);
                    } catch (PDOException $e) {
                    }
					$ext = '';
					while ($rowExt = $resultExt->fetch()) {
						$ext = $ext."'.".$rowExt['extension']."',";
					}
					?>
					<script type="text/javascript">
						var file=new LiveValidation('file');
						file.add( Validate.Inclusion, { within: [<?php echo $ext; ?>], failureMessage: "Illegal file type!", partialMatch: true, caseSensitive: false } );
					</script>
				</td>
			</tr>
			<tr id="linkRow">
				<td>
					<b>Policy Link *</b><br/>
				</td>
				<td class="right">
					<input name="link" id="link" maxlength=255 value="" type="text" style="width: 300px">
					<script type="text/javascript">
						var link=new LiveValidation('link');
						link.add( Validate.Inclusion, { within: ['http://', 'https://'], failureMessage: "Address must start with http:// or https://", partialMatch: true } );
					</script>
				</td>
			</tr>
			<tr>
				<td>
					<b>Audience</b><br/>
					<span style="font-size: 90%"><i>User role groups who should have view access.<br/></i></span>
				</td>
				<td class="right">
					<?php
                    $roleCount = 0;
					try {
						$dataRoles = array();
						$sqlRoles = 'SELECT * FROM gibbonRole ORDER BY name';
						$resultRoles = $connection2->prepare($sqlRoles);
						$resultRoles->execute($dataRoles);
					} catch (PDOException $e) {
					}
					while ($rowRoles = $resultRoles->fetch()) {
						echo $rowRoles['name']." <input type='checkbox' name='gibbonRoleID$roleCount' value='".$rowRoles['gibbonRoleID']."'><br/>";
						++$roleCount;
					}
					?>
					<input type="hidden" name="roleCount" value="<?php echo $roleCount ?>">
				</td>
			</tr>

			<tr>

				<td>
					<span style="font-size: 90%"><i>* denotes a required field</i></span>
				</td>
				<td class="right">
					<input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
					<input type="submit" value="Submit">
				</td>
			</tr>
		</table>
	</form>
	<?php

}
?>
