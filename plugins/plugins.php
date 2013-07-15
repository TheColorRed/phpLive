<?php

$plugins = array(
    "Twitter"  => array(
        "root"         => "TwitterAPI",
        "fileName"     => "Twitter.plugin.php",
        "instanceName" => "twitter",
        "sessionRef"   => "twitter",
    ),
    "Database" => array(
        "root"         => "Database",
        "fileName"     => "Database.plugin.php",
        "instanceName" => "db",
        "sessionRef"   => "db",
        "information"  => array(
            "dbtype"   => "mysql",
            "hostname" => "localhost",
            "database" => "test",
            "username" => "root",
            "password" => "afrid123",
        )
    ),
);