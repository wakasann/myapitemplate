<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserControllerTest extends TestCase
{
    public function testLoginValidator(){
        $url = $this->getRouteData('user','login');
        $response = $this->json('POST',$url,[]);
        $response->assertStatus(422);
    }

    public function testLoginSuccess(){
        $url = $this->getRouteData('user','login');
        $data = $this->getGroupData('user','login');
        $response = $this->json('POST',$url,[
            'phone' => $data['phone'],
            'zone'  => $data['zone'],
            'password'  => $data['password'],
        ]);
        $response->assertStatus(200);
    }


    public function testProfileNoneToken(){
        $url = $this->getRouteData('user','profile');
        $response = $this->json('GET',$url,[]);
        $response->assertStatus(401);
    }

    public function testProfileWithToken(){
        $url = $this->getRouteData('user','profile');
        $response = $this->json('GET',$url,[],$this->getUserJwtToken());
        $response->assertStatus(200);
    }

    public function testUpdateInfoValidator(){
        $url = $this->getRouteData('user','update');
        $response = $this->json('POST',$url,[]);
        $response->assertStatus(401);
    }

    public function testUpdateInfoSuccess(){
        $url = $this->getRouteData('user','update');
        $data = $this->getGroupData('user','update');
        $response = $this->json('POST',$url,$data,$this->getUserJwtToken());
        $response->assertStatus(200);
    }


}
