<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use JWTAuth;
use Faker\Factory as Faker;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    protected static $tablesToReseed = [];
    public $testData; //测试数据
    /**
     * @var \Faker\Generator
     */
    protected $faker;

    public function seed($class = 'DatabaseSeeder', array $tables = [])
    {
        $this->artisan('db:seed', ['--class' => $class, '--tables' => implode(',', $tables)]);
    }

    protected function reseed()
    {
        // TEST_SEEDERS is defined in phpunit.xml, e.g. <env name="TEST_SEEDERS" value="\SimpleYamlSeeder"/>
        $seeders = env('TEST_SEEDERS') ? explode(',', env('TEST_SEEDERS')) : [];

        if ($seeders && is_array(static::$tablesToReseed)) {
            foreach ($seeders as $seeder) {
                $this->seed($seeder, static::$tablesToReseed);
            }
        }

        \Cache::flush();

        static::$tablesToReseed = false;
    }

    protected static function reseedInNextTest(array $tables = [])
    {
        static::$tablesToReseed = $tables;
    }

    /**
     * Call protected or private method of a class.
     *
     * @param object $object      instantiated object that we will run method on.
     * @param string $method_name method name to call
     * @param array  $parameters  array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    protected function invokeNonPublicMethod($object,  $method_name, ...$parameters)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method     = $reflection->getMethod($method_name);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    protected function getNonPublicMethod($object,  $method_name)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method     = $reflection->getMethod($method_name);
        $method->setAccessible(true);

        return $method;
    }

    protected function invokeNonPublicMethodSecond($object,  $method_name, ...$parameters)
    {
        return $this->getNonPublicMethod($object, $method_name)->invokeArgs($object, $parameters);
    }

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();

        $dataFile = dirname(__FILE__).'/testdata.yml';
        if(file_exists($dataFile)){
            $values = \Symfony\Component\Yaml\Yaml::parseFile($dataFile);
//            \Log::debug("data".json_encode($values));
            $this->testData = $values;
        }

    }

    public function getUserJwtToken(){
        return [
            'Authorization' => 'Bearer '.$this->testData['data']['jwt_token']
        ];
    }

    /**
     * 获取路由列表或者单个路由
     * @param string $group 路由组
     * @param string $action 路由下标
     * @return string
     */
    public function getRouteData($group,$action = ''){
        $routes = $this->testData['routes'][$group];
        if($action){
            $routes = $routes[$action];
        }
        return $routes;
    }


    /**
     * 获取测试数据组或者单个测试数据组
     * @param string $group 数据组
     * @param string $item_key 数据组下标
     * @return mixed
     */
    public function getGroupData($group,$item_key = ''){
        $groupData = $this->testData['data'][$group];
        if($item_key){
            $groupData = $groupData[$item_key];
        }
        return $groupData;
    }

    /**
     * 产生一个用户的jwt token,建议将所有测试数据放入到testdata.yaml中去
     */
    public function getUserJwtTokenHeader($user = null){
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
        if (!is_null($user)) {
            $token = JWTAuth::fromUser($user);
            JWTAuth::setToken($token);
            $headers['Authorization'] = 'Bearer '.$token;
        }

        return $headers;
    }
}
