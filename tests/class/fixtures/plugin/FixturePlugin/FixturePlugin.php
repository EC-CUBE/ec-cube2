<?php

class FixturePlugin extends SC_Plugin_Base
{
    public function loadClassFileChange(&$classname, &$classpath) {
        if ($classname === "SC_Customer_Ex") {
            $classpath = "FixturePlugin/Fixture_SC_Customer.php";
            $classname = "Fixture_SC_Customer";
        }
    }
}
