<?php

$HOME = realpath(dirname(__FILE__)) . "/../../../..";
require_once($HOME . "/tests/class/Common_TestCase.php");
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * SC_Utils::sfIsInternalDomain()のテストクラス.
 *
 * @author Hiroko Tamagawa
 * @version $Id$
 */
class SC_Utils_sfIsInternalDomainTest extends Common_TestCase
{


  protected function setUp()
  {
    // parent::setUp();
  }

  protected function tearDown()
  {
    // parent::tearDown();
  }

  /////////////////////////////////////////
  public function testsfIsInternalDomain_ドメインが一致する場合_trueが返る()
  {
    $url = HTTP_URL . 'index.php';
    $this->expected = true;
    $this->actual = SC_Utils::sfIsInternalDomain($url);

    $this->verify($url);
  }

  public function testsfIsInternalDomain_アンカーを含むURLの場合_trueが返る()
  {
    $url = HTTP_URL . 'index.php#hoge';
    $this->expected = true;
    $this->actual = SC_Utils::sfIsInternalDomain($url);

    $this->verify($url);
  }

  public function testsfIsInternalDomain_ドメインが一致しない場合_falseが返る()
  {
    // 一致しないようなURLにする
    $url = 'http://unmatched.example.jp/html/index.php';

    $this->expected = false;
    $this->actual = SC_Utils::sfIsInternalDomain($url);

    $this->verify($url);
  }

  //////////////////////////////////////////
}

