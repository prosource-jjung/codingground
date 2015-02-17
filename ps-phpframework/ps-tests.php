<?php
       require('ps-orm.php');
       /**
         * @Table
         */
       class User{}   
       
       $em = PS_DBEntityManagerFactory::getInstance()->produce();
       $em->getAll('SELECT * FROM USER', 'User');
?> 
