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

編寫的yaml遵守的swagger規則是 2.0 版本的

將 合併之後的yaml 文件轉換爲 json

```
java -jar /opt/swagger-codegen-cli-2.3.1.jar generate -i yaml/swagger.yaml -l swagger -o public/remote`
```