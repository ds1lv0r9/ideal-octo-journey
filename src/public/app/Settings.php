<?php


class Settings
{
    private array $settings;

    public function getSettings(): array
    {
        $this->settings = [
            'db' => [
                'driver' => 'mysql',
                'host' => 'database',
                'port' => '3306',
                'dbname' => 'task',
                'username' => 'root',
                'password' => 'example',
                'charset' => 'utf8mb4',
            ]
        ];

        return $this->settings;
    }
}
