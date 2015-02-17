<?php
/*
    ps-annotations.php <Annotations to put on object meta-data>
    
    Copyright (C) 2015  ProSource Web Team: Jean Jung - Team leader.
                            

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

/**
  * Use this class to map entities on the data base.
  * Add it to a class to say that the class instances must origin by that table, 
  * and then it will be stored there by the PS_DBManagers.
  * Example of use:
  * 
  * To class: class User {}
  * 
  * Put this @Table(tableName=USER) at the DocComment of the class.
  *
  * * If tableName is missing, the name of the class as UPPERCASE mode will be used . 
  */ 
class Table {

        private $className;
        private $tableName;
        
        /**
          * Default constructor.
          */
        public function __construct($className, array $dataArray)
        {
                foreach ($dataArray as $property => $argument) {
                        $this->{$property} = $argument;
                }
                $this->$className = $className;
        }

        /**
          * Returns the $tableName of the class. If missed, $className uppercased will be returned.
          * @return string the $tableName of the class
          */
        public function getTableName() 
        {
                if ($tableName)
                { 
                        return $tableName;
                }
                
                return strtoupper($className);
        }
}

?>