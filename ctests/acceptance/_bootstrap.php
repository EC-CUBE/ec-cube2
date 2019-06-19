<?php
// Here you can initialize variables that will be available to your tests

$faker = Faker\Factory::create('ja_JP');
Codeception\Util\Fixtures::add('faker', $faker);
