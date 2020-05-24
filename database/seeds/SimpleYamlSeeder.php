<?php
use Illuminate\Database\Seeder;

/**
 * yaml seeder
 * @link https://learnku.com/articles/5053/write-the-laravel-test-code-1
 * Class YamlSeeder
 */
abstract class YamlSeeder extends Seeder
{
    private $files;

    public function __construct(array $files)
    {
        $this->files = $files;
    }

    public function run(array $tables = [])
    {
        // Close unique and foreign key constraint
        $db = $this->container['db'];
        $db->statement('SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;');
        $db->statement('SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;');

        foreach($this->files as $file) {


            // Convert yaml data to array
            $fixtures = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($file));

            foreach($fixtures as $table => $data) {
                // Only seed specified tables, it is important!!!
                if ($tables && !in_array($table, $tables, true)) {
                    continue;
                }

                $db->table($table)->truncate();

                if (!$db->table($table)->insert($data)) {
                    throw new \RuntimeException('xxx');
                }
            }

        }

        // Open unique and foreign key constraint
        $db->statement('SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;');
        $db->statement('SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;');
    }
}

class SimpleYamlSeeder extends YamlSeeder
{
    public function __construct()
    {
        parent::__construct([database_path('seeds/simple.yml')]);
    }
}