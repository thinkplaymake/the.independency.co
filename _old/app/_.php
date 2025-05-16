<?php

    require('vendor/autoload.php');
    
    
    
    function print_pre($object,$suppressoutput=false) {
	    print '<pre>';
	    print_r($object, false);
	    print '</pre>';
    }