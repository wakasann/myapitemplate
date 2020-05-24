<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ToolControllerTest extends TestCase
{

    public function testTokenCheckNoneJwtToken(){
        $url = $this->getRouteData('tools','token_check');
        $response = $this->json('GET',$url);
        $response->assertStatus(401);
    }

    public function testTokenCheckJwtToken(){
        $url = $this->getRouteData('tools','token_check');
        $response = $this->json('GET',$url,[],$this->getUserJwtToken());
        $response->assertStatus(200);
    }
}
