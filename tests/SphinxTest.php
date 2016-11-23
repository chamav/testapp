<?php
class SphinxTest extends TestCase
{

    public  function testConnect()
    {
        $conn = \DB::connection('sphinx');
        $items = $conn->select("SELECT * FROM users WHERE MATCH ('test123')");
        $this->assertInternalType('array',$items);
        $this->assertGreaterThan(0,count($items));
    }

}