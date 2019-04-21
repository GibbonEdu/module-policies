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
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.
  You should have received a copy of the GNU General Public License
  along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace Gibbon\Module\Policies;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

/**
 * Policies Gateway
 *
 * @version v16
 * @since   v16
 */
class PoliciesGateway extends QueryableGateway {

    use TableAware;

    private static $tableName = 'policiesPolicy';
    private static $searchableColumns = ['policiesPolicy.name', 'policiesPolicy.nameShort', 'policiesPolicy.category', 'gibbonDepartment.name'];

    public function queryPolicies(QueryCriteria $criteria) {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'policiesPolicy.policiesPolicyID',
                'policiesPolicy.name',
                'policiesPolicy.nameShort',
                'policiesPolicy.category',
                'policiesPolicy.description',
                'policiesPolicy.active',
                'policiesPolicy.scope',
                'policiesPolicy.gibbonDepartmentID',
                'policiesPolicy.type',
                'policiesPolicy.gibbonRoleIDList',
                'policiesPolicy.staff',
                'policiesPolicy.student',
                'policiesPolicy.parent',
                'policiesPolicy.location',
                'policiesPolicy.gibbonPersonIDCreator',
                'policiesPolicy.timestampCreated',
                'gibbonDepartment.name AS department',
                'gibbonPerson.surname',
                'gibbonPerson.preferredName',
                'gibbonPerson.title'
            ])
            ->innerJoin('gibbonPerson', 'policiesPolicy.gibbonPersonIDCreator=gibbonPerson.gibbonPersonID')
            ->leftJoin('gibbonDepartment', 'policiesPolicy.gibbonDepartmentID=gibbonDepartment.gibbonDepartmentID');

        return $this->runQuery($query, $criteria);
    }

    public function queryViewPoliciesByRole(QueryCriteria $criteria, $gibbonRoleIDCurrent = null) {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'policiesPolicy.policiesPolicyID',
                'policiesPolicy.name',
                'policiesPolicy.nameShort',
                'policiesPolicy.category',
                'policiesPolicy.description',
                'policiesPolicy.active',
                'policiesPolicy.scope',
                'policiesPolicy.gibbonDepartmentID',
                'policiesPolicy.type',
                'policiesPolicy.gibbonRoleIDList',
                'policiesPolicy.staff',
                'policiesPolicy.student',
                'policiesPolicy.parent',
                'policiesPolicy.location',
                'policiesPolicy.gibbonPersonIDCreator',
                'policiesPolicy.timestampCreated',
                'gibbonDepartment.name AS department',
                'gibbonPerson.surname',
                'gibbonPerson.preferredName',
                'gibbonPerson.title'
            ])
            ->innerJoin('gibbonPerson', 'policiesPolicy.gibbonPersonIDCreator=gibbonPerson.gibbonPersonID')
            ->leftJoin('gibbonDepartment', 'policiesPolicy.gibbonDepartmentID=gibbonDepartment.gibbonDepartmentID')
            ->where("policiesPolicy.active = 'Y'");

        if ($gibbonRoleIDCurrent) {
            $query->where('FIND_IN_SET(:gibbonRoleIDCurrent , gibbonRoleIDList)')
                ->bindValue('gibbonRoleIDCurrent', $gibbonRoleIDCurrent);
        }

        return $this->runQuery($query, $criteria);
    }


    public function selectPolicyById(array $data) {
        
        $sql = "SELECT * 
                FROM policiesPolicy
                WHERE policiesPolicyID=:policiesPolicyID";

        return $this->db()->selectOne($sql, $data);
    }


    public function insertPolicy(array $data)
    {
        $sql = 'INSERT INTO policiesPolicy SET scope=:scope, gibbonDepartmentID=:gibbonDepartmentID, name=:name, nameShort=:nameShort, active=:active, category=:category, description=:description, type=:type, location=:location, gibbonRoleIDList=:gibbonRoleIDList, parent=:parent, staff=:staff, student=:student, gibbonPersonIDCreator=:gibbonPersonIDCreator, timestampCreated=:timestampCreated';

        return $this->db()->insert($sql, $data);
    }

    public function updatePolicy(array $data)
    {
        $sql = 'UPDATE policiesPolicy SET name=:name, nameShort=:nameShort, active=:active, category=:category, description=:description, gibbonRoleIDList=:gibbonRoleIDList, parent=:parent, staff=:staff, student=:student, location=:location WHERE policiesPolicyID=:policiesPolicyID';

        return $this->db()->update($sql, $data);
    }

    public function deletePolicy(array $data)
    {
        $sql = "DELETE FROM policiesPolicy WHERE policiesPolicyID=:policiesPolicyID";

        return $this->db()->delete($sql, $data);
    }
    
}
