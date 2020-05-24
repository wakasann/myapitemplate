## 介紹

我的 PHP 項目Api 基礎結構，方便自己搭建項目時，不用再反覆的安裝依賴的第三方庫



基於: Laravel 5.5.*

使用過的第三方庫:


| 庫 |  說明/我使用時的用途 |
| ------ | ----- |
|   dingo/api  | |
|   symfony/yaml |  單元測試時，讀取yaml格式的測試數據 |
|   tymon/jwt-auth | 生成jwt token 和驗證token |
|  barryvdh/laravel-cors | 跨域 |
| php-amqplib/php-amqplib |  amqp(Advanced Message Queuing Protocol)，發送隊列信息 |
| jaxl/jaxl | 連接openfire 發送消息|
| predis/predis | 連接 redis |
| prettus/laravel-validation|  表單驗證|
| ~~zircote/swagger-php~~ | 通過 PHP註釋生成swagger.json(因個人認爲註釋的形式有點麻煩，已轉爲使用 yaml轉json) |

`swagger-merget` Node.js 合并 YAML 文件的工具


文件用途:

| 文件 |  說明/我使用時的用途 |
| ------ | ----- |
| build.xml | jenkins build或者直接使用apache ant命令編譯一個壓縮包時調用的 |
| test_worker.conf |   |
| sonar-project.properites |  執行sonar-scanner 命令時的一個配置文件 |
| Jenkinsfile | Jenkins piple的嘗試測試文件 |
| init.sh |  當基於 Laravel 5.5 的項目部署到服務器時，不會自動產生一些需要使用的目錄 |


## 開始

```
git clone https://github.com/wakasann/myapitemplate
composer install
```

## 初始化
```
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

##### 只填充單個表的數據時，可運行

```angular2
`php artisan db:seed --class=UserSeeder`
```

##### 文檔Yaml

編寫的yaml遵守的swagger規則是 2.0 版本的
Api文档源文件地址: `yaml` ，主文件是`yaml/main.yaml`

```
npm i -g swagger-merger
```
通過命令工具进入 `yaml`目录，运行 `swagger-merger -i main.yaml` 命令，运行完之后，会生成`swagger.yaml`


使用浏览器打开[http://editor.swagger.io/](http://editor.swagger.io/),将`swagger.yaml`文件的内容粘贴到swagger Editor中，通过 swagger editor
的菜单依序点击  File -> Convert and save as JSON,就会下载一个 `swagger.json` 文件,放入到项目中的 `public/remote` 文件夹中就可以了。

或者通過執行命令將`swagger.yaml` 轉換爲 `swagger.json`

```
java -jar /opt/swagger-codegen-cli-2.3.1.jar generate -i yaml/swagger.yaml -l swagger -o public/remote`
```

#### 部署

使用 Jenkins 进行部署，部署之后合并yaml文件，并将 swagger.yaml 转换为 swagger.json

```
cd /var/www/html/mybaseapi
sed -i "s|- http|- https|" yaml/common.yaml && sed -i "s|localhost|example.com|" yaml/main.yaml
/usr/src/node-v8.11.1-linux-x64/bin/swagger-merger -i ./yaml/main.yaml -o ./yaml/swagger.yaml && java -jar /opt/swagger-codegen-cli-2.3.1.jar generate -i yaml/swagger.yaml -l swagger -o public/remote
sed -i "s|http://localhost/mybaseapi/public/remote/swagger.json|https://example.com/mybaseapi/public/remote/swagger.json|" public/swagger/index.html
```

我是通過Jenkins 的`publish to ssh`插件，將gitlab上的項目代碼拉下來，部署到目標服務器中 