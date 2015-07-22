<?php

class ApiTest extends TestCase {

	public function testBasicExample()
	{
		$response = $this->call('GET', '/');
		$this->assertEquals(200, $response->getStatusCode());
	}


    public function setLogin(){
        $parameters = ['Phone' => '10012345678', 'Password' => md5('111111') ];
        $response = $this->call('POST', '/setLogin', $parameters, $cookies = null , $files = null , $server=null, $content=null);
		$this->assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getContent());

    }
}
