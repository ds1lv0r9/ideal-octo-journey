<?php


class DB_Model
{
    private array $settings;
    protected ?PDO $db;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings->getSettings();
        $this->connect();
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    private function connect(): void
    {
        try
        {
            $this->db = new PDO(
                $this->settings['db']['driver'] . ':host=' . $this->settings['db']['host'] . ':' . $this->settings['db']['port'] .
                ';dbname=' . $this->settings['db']['dbname'] . ';charset=' . $this->settings['db']['charset'],
                $this->settings['db']['username'],
                $this->settings['db']['password']
            );
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
            die;
        }
    }

    private function disconnect(): void
    {
        $this->db = null;
    }
}
