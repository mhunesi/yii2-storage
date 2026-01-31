Yii2 Storage Component
========================
File Storage Component for Yii2

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist mhunesi/yii2-storage "*"
```

or add

```
"mhunesi/yii2-storage": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php

'components' => [
    //...
    'storage' => [
        'class' => '\mhunesi\storage\Storage',
        'defaultStorage' => 'local',
        'queueFilters' => true,
        'queueFiltersList' => ['tiny-crop', 'medium-thumbnail'],
        'downloadValidationKey' => 'YII_STORAGE_KEY',
        'downloadUrl' => '/site/download',
        'storages' => [
            'local' => [
                'class' => '\mhunesi\storage\filesystem\LocalFilesystem',
                'path' => '@app/web/uploads'
            ],
            'aws' => [
                'class' => 'mhunesi\storage\filesystem\AwsS3Filesystem',
                'bucket' => 'bucket_name',
                'key' => '_key',
                'secret' => 'secret_key',
                'region' => 'eu-central-1',
                // 'version' => 'latest',
                'prefix' => 'wss',
            ],
            'minio' => [
                'class' => 'mhunesi\storage\filesystem\AwsS3Filesystem',
                'bucket' => 'bucket_name',
                'key' => '_key',
                'secret' => 'scret_key',
                'region' => 'eu-central-1',
                // 'version' => 'latest',
                // 'baseUrl' => 'your-base-url',
                'prefix' => 'subfolder',
                // 'options' => [],
                'endpoint' => 'http://127.0.0.1:49160'
            ],
            'ftp' => [
                'class' => 'mhunesi\storage\filesystem\FtpFilesystem',
                'host' => '192.168.1.1',
                'port' => 21,
                'username' => 'ftp_username',
                'password' => 'ftp_password',
                // 'ssl' => true,
                // 'timeout' => 60,
                'root' => '/rootPath',
                'publicUrl' => 'http://ftp_url/',
                // 'permPrivate' => 0700,
                // 'permPublic' => 0744,
                // 'passive' => false,
                // 'transferMode' => FTP_TEXT,
            ],
        ]
    ]    
      
    //...
],
```


```php
'controllerMap' => [
    'migrate' => [
            //..
        'migrationNamespaces' => [
            //..
        ],
        'migrationPath' => [
            //..
            '@mhunesi/storage/migrations'
        ]
    ]
],
```
Upload & Download Action

```php
/**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'download' => [
                'class' => 'mhunesi\storage\actions\DownloadAction',
            ],
            'upload' => [
                'class' => 'mhunesi\storage\actions\FileUploadAction',
                'use_strict' => true,
                'path' => 'folder/folder',
                'folder' => 1,
                'hidden' => true,
                'visibility' => 'public' // 'private' 
            ],
        ];
    }
```

URL Rules

```php
'rules' => [
    'download/<token:[a-zA-Z0-9_\-\+\%\/=]*>/<filename:[a-zA-Z0-9_\-\+\%\.\ \/=]*>' => 'site/download'
]
```
