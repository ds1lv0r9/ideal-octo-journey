<?php


class UsersRepository extends DB_Model
{
    public function __construct(Settings $settings)
    {
        parent::__construct($settings);
    }

    public function createUsersTable()
    {
        try
        {
            $query = '
                CREATE TABLE IF NOT EXISTS users
                (
                    id INT UNSIGNED auto_increment NOT NULL,
                    first_name varchar(100) NULL,
                    last_name varchar(100) NULL,
                    birthdate DATE NULL,
                    height FLOAT NULL,
                    club_member BOOL NULL,
                    CONSTRAINT users_PK PRIMARY KEY (id)
                )
                ENGINE=InnoDB
                DEFAULT CHARSET=utf8mb4
                COLLATE=utf8mb4_0900_ai_ci;
            ';

            $stmt = $this->db->prepare($query);

            $stmt->execute();
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            die;
        }
    }

    public function insertUsers(array $users): int
    {
        try
        {
            $this->db->beginTransaction();

            $query = 'INSERT INTO users (first_name, last_name, birthdate, height, club_member) '
                . 'VALUES (:first_name, :last_name, :birthdate, :height, :club_member)';

            $stmt = $this->db->prepare($query);

            $rowsAffected = 0;
            foreach ($users as $user)
            {
                $stmt->bindValue(':first_name', $user['first_name'], PDO::PARAM_STR);
                $stmt->bindValue(':last_name', $user['last_name'], PDO::PARAM_STR);
                $stmt->bindValue(':birthdate', $user['birthdate'], PDO::PARAM_STR);
                $stmt->bindValue(':height', $user['height'], PDO::PARAM_STR);
                $stmt->bindValue(':club_member', $user['club_member'], PDO::PARAM_BOOL);

                $stmt->execute();

                if ($stmt->rowCount() === 1)
                {
                    $rowsAffected++;
                }
            }

            $this->db->commit();
            return $rowsAffected;
        }
        catch (PDOException $e)
        {
            if ($this->db->inTransaction())
            {
                $this->db->rollBack();
                return 0;
            }

            echo $e->getMessage();
            die;
        }
    }

    public function getUsers(): array
    {
        try
        {
            $query = 'SELECT * FROM users LIMIT 100';

            $stmt = $this->db->prepare($query);

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            die;
        }
    }

    public function create(array $user): int
    {
        $query = '  INSERT INTO users (first_name, last_name, birthdate, height, club_member)
                    VALUES (:first_name, :last_name, :birthdate, :height, :club_member)';

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':first_name', $user['first_name'], PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $user['last_name'], PDO::PARAM_STR);
        $stmt->bindParam(':birthdate', $user['birthdate'], PDO::PARAM_STR);
        $stmt->bindParam(':height', $user['height'], PDO::PARAM_STR);
        $stmt->bindParam(':club_member', $user['club_member'], PDO::PARAM_BOOL);

        $stmt->execute();

        return $this->db->lastInsertId();
    }

    public function read(int $id): array|bool
    {
        $query = '  SELECT * FROM users WHERE id = :id';

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update(array $user): bool
    {
        try
        {
            $query = '  UPDATE users 
                    SET first_name = :first_name, last_name = :last_name, birthdate = :birthdate, height = :height, club_member = :club_member 
                    WHERE id=:id';

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':first_name', $user['first_name'], PDO::PARAM_STR);
            $stmt->bindParam(':last_name', $user['last_name'], PDO::PARAM_STR);
            $stmt->bindParam(':birthdate', $user['birthdate'], PDO::PARAM_STR);
            $stmt->bindParam(':height', $user['height'], PDO::PARAM_STR);
            $stmt->bindParam(':club_member', $user['club_member'], PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $user['id'], PDO::PARAM_INT);

            return $stmt->execute();
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            die;
        }
    }

    public function delete(int $id): int
    {
        try
        {
            $query = 'DELETE FROM users WHERE id=:id';

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->rowCount();
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            die;
        }
    }

    public function getUsersFiltered(array $searchParams, array $mode = []): array
    {
        try
        {
            $bParams = [];

            $i = 0;

            $sql = 'SELECT id, first_name, last_name, birthdate, height, club_member FROM users ';

            foreach ($searchParams as $param)
            {
                switch ($param['type'])
                {
                    case 'search':

                        if (!isset($mode['totalEntries']))
                        {
                            $sql .= 'where ';

                            $fields = [
                                ['name' => 'id', 'bind' => 'like_user_id'],
                                ['name' => 'first_name', 'bind' => 'like_first_name'],
                                ['name' => 'last_name', 'bind' => 'like_last_name'],
                                ['name' => 'birthdate', 'bind' => 'like_birthdate'],
                                ['name' => 'height', 'bind' => 'like_height'],
                                ['name' => 'club_member', 'bind' => 'like_club_member'],
                            ];

                            $searchStrings = explode(' ', trim($param['value']));

                            for ($ssi = 0; $ssi < count($searchStrings); $ssi++)
                            {
                                for ($f = 0; $f < count($fields); $f++)
                                {
                                    $bindParameter = ':' . $fields[$f]['bind'] . $i++;
                                    $sql .= $fields[$f]['name'] . ' like ' . $bindParameter . ($f === count($fields) - 1 ? ' ' : ' or ');

                                    $bParams [] = [
                                        'bind_parameter' => $bindParameter,
                                        // search term:
                                        'parameter' => '%' . $searchStrings[$ssi] . '%',
                                        'type' => PDO::PARAM_STR,
                                    ];
                                }

                                $sql .= ($ssi === count($searchStrings) - 1 ? ' ' : ' or ');
                            }
                        }
                        break;
                    default:
                        break;
                }
            }

            $allowedColumns = ['id', 'first_name', 'last_name', 'birthdate', 'height', 'club_member'];
            $allowedDirections = ['asc', 'desc'];

            foreach ($searchParams as $param)
            {
                switch ($param['type'])
                {
                    case 'orderBy':
                        if (in_array($param['column'], $allowedColumns, true) && in_array($param['direction'], $allowedDirections, true))
                        {
                            $sql .= 'order by ' . $param['column'] . ' ' . $param['direction'] . ' ';
                        }
                        break;
                    default:
                        break;
                }
            }

            foreach ($searchParams as $param)
            {
                switch ($param['type'])
                {
                    case 'pagination':
                        if (!(isset($mode['recordsFiltered']) || isset($mode['totalEntries'])))
                        {
                            $sql .= 'limit ';

                            $bindParameter = ':' . 'limit_start' . $i++;
                            $sql .= $bindParameter . ', ';

                            $bParams [] = [
                                'bind_parameter' => $bindParameter,
                                'parameter' => $param['start'],
                                'type' => PDO::PARAM_INT,
                            ];

                            $bindParameter = ':' . 'limit_length' . $i++;
                            $sql .= $bindParameter . ' ';

                            $bParams [] = [
                                'bind_parameter' => $bindParameter,
                                'parameter' => $param['length'],
                                'type' => PDO::PARAM_INT,
                            ];
                        }
                        break;
                    default:
                        break;
                }
            }

            if (isset($mode['recordsFiltered']) || isset($mode['totalEntries']))
            {
                $sql = 'select count(howMany.id) from ( ' . $sql . ' ) howMany';
            }

            $stmt = $this->db->prepare($sql);

            for ($i = 0; $i < count($bParams); $i++)
            {
                $stmt->bindParam($bParams[$i]['bind_parameter'], $bParams[$i]['parameter'], $bParams[$i]['type']);
            }

            $stmt->execute();

            if (isset($mode['recordsFiltered']) || isset($mode['totalEntries']))
            {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else
            {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return $result;
        }
        catch (Exception $e)
        {
            echo '<pre>';
            echo $e->getMessage();
            echo '</pre>';
            die;
        }
    }
}
