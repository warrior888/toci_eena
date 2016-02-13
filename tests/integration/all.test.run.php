<?php

    require_once 'registration.collections.test.php';
    require_once 'registration.person.test.php';
    require_once 'registration.pass.test.php';
    require_once 'person.addupdate.test.php';
    require_once 'apply.person.test.php';
    require_once 'curlrequest.getdata.test.php';
    require_once 'startpraca.xml.test.php';
    
    //$tests[] = new PersonAddUpdateTest();
    //$tests[] = new RegistrationCollectionsTest();
    //$tests[] = new RegistrationPersonTest();
    //$tests[] = new RegistrationPassTest();
    
    //$tests[] = new CurlRequestGetDataTest();
    //$tests[] = new StartPracaXmlTest();
    $tests[] = new ApplyPersonTest();
    
    //TODO wtf clean up - works ?
    foreach ($tests as $test) {
        
        if ($test->run())
            echo get_class($test)." PASS\n";
        else
            echo get_class($test)." NO PASS\n";
    }