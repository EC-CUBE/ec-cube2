<?php

class Fixture_SC_Customer extends SC_Customer
{
    public function getValue($key)
    {
        return $key;
    }
}
