<?php


class User extends Controller
{
    public function __construct(DB_Model $db, Template $template, Sessions $session)
    {
        parent::__construct($db, $template, $session);
    }

    public function index()
    {
        session_start();

        $data = [];

        $data['csv'] = $this->db->getUsers();

        $data['form_id'] = $this->session->getFormId();
        $data['form_token'] = $this->session->getFormToken($data['form_id']);

        $this->template->render('users/index.html.php', $data);
    }

    public function create()
    {
        session_start();

        $data = [];

        $user = [];
        $errors = [];

        if ($_POST)
        {
            if (!$this->session->verifyFormToken($_POST['form_token'], $_POST['form_id']))
            {
                die('invalid csrf token');
            }

            $user = $this->getUserData();
            $errors = $this->validateUserData($user);

            if (count($errors) === 0)
            {
                $userId = $this->db->create($user);

                header('Location: /users/show/' . $userId, true);
                die();
            }

            $this->sanitizeUserData($user);
        }

        $data['user'] = $user;
        $data['errors'] = $errors;

        $data['form_id'] = $this->session->getFormId();
        $data['form_token'] = $this->session->getFormToken($data['form_id']);

        $this->template->render('users/create.html.php', $data);
    }

    public function show(string $id)
    {
        $data = [];
        $data['user_id'] = $id;
        $user = $this->db->read((int)$data['user_id']);

        if (!$user)
        {
            header('Location: /', true);
            die('This is not the user you are looking for...');
        }

        $user['club_member'] = ($user['club_member']) ? 'true' : 'false';

        $this->sanitizeUserData($user);

        $data['user'] = $user;

        $this->template->render('users/show.html.php', $data);
    }

    public function edit(string $id)
    {
        session_start();

        $data = [];
        $data['user_id'] = $id;
        $errors = [];
        $user = [];

        if ($_POST)
        {
            if (!$this->session->verifyFormToken($_POST['form_token'], $_POST['form_id']))
            {
                die('invalid csrf token');
            }

            $user = $this->getUserData();
            $errors = $this->validateUserData($user);

            if (count($errors) === 0)
            {
                $user['id'] = $id;
                $success = $this->db->update($user);

                header('Location: /users/show/' . $data['user_id'], true);
                die();
            }
        }
        else
        {
            $user = $this->db->read((int)$data['user_id']);

            if (!$user)
            {
                header('Location: /', true);
                die('This is not the user you are looking for...');
            }

            $user['club_member'] = ($user['club_member']) ? 'true' : 'false';
        }

        $this->sanitizeUserData($user);

        $data['user'] = $user;
        $data['errors'] = $errors;

        $data['form_id'] = $this->session->getFormId();
        $data['form_token'] = $this->session->getFormToken($data['form_id']);

        $this->template->render('users/edit.html.php', $data);
    }

    public function delete(string $id)
    {
        session_start();

        if ($_POST)
        {
            if (!$this->session->verifyFormToken($_POST['form_token'], $_POST['form_id']))
            {
                die('invalid csrf token');
            }
            $this->db->delete((int)$id);

            header('Location: /users', true);
            die();
        }
        http_response_code(403);
        die('forbidden');
    }

    public function setup()
    {
        $this->db->createUsersTable();

        $data = [];
        $data['info'] = [];

        $data['info'][] = 'Table created. <br/>';

        $data['csv'] = Tools::importCsv('sample-data.csv');;
        array_splice($data['csv'], 0, 1);

        foreach ($data['csv'] as &$user)
        {
            $date = date_create($user['birthdate']);
            $user['birthdate'] = date_format($date, "Y-m-d");

            $user['club_member'] = $user['club_member'] === 'true';
        }

        $rowsAffected = $this->db->insertUsers($data['csv']);

        $data['info'][] = 'Data imported (rows: ' . $rowsAffected . '). <br/>';
        $data['info'][] = '[<a href="/">Proceed to index</a>]';

        $this->template->render('users/import.html.php', $data);
    }

    public function getUsersDatatables()
    {
        if ($_POST)
        {
            $data = [];

            $data['draw'] = (int)$_POST['draw'];

            session_start();
            $tokenVerify = $this->session->verifyFormToken($_POST['form_token'], $_POST['form_id']);

            if (is_string($tokenVerify) or is_bool($tokenVerify) and !$tokenVerify)
            {
                if (!$tokenVerify)
                {
                    $data['error'] = 'invalid csfr token';
                }
                else
                {
                    $data['error'] = $tokenVerify;
                }

                header('Content-Type: application/json');
                echo json_encode($data);
                die;
            }

            $searchParams = [];
            foreach ($_POST['order'] as $orderBy)
            {
                $searchParams[] = [
                    'type' => 'orderBy',
                    'column' => $_POST['columns'][$orderBy['column']]['data'],
                    'direction' => $orderBy['dir'],
                ];
            }

            if (!empty($_POST['search']['value']))
            {
                $searchParams[] = [
                    'type' => 'search',
                    'value' => $_POST['search']['value'],
                    'regex' => $_POST['search']['regex'],
                ];
            }

            if (isset($_POST['start']) && isset($_POST['length']))
            {
                $searchParams[] = [
                    'type' => 'pagination',
                    'start' => $_POST['start'],
                    'length' => $_POST['length'],
                ];
            }

            $data['draw'] = (int) $_POST['draw'];

            $mode = ['recordsFiltered' => true];
            $filtered = $this->db->getUsersFiltered($searchParams, $mode);
            $data['recordsFiltered'] = $filtered['count(howMany.id)'];

            $data['data'] = $this->db->getUsersFiltered($searchParams);

            foreach ($data['data'] as &$user)
            {
                $this->sanitizeUserData($user);
            }

            $mode = ['totalEntries' => true];
            $totalRecords = $this->db->getUsersFiltered($searchParams, $mode);

            $data['recordsTotal'] = $totalRecords['count(howMany.id)'];

            header('Content-Type: application/json');
            echo json_encode($data);
            die;
        }
    }

    /**
     * Retrieves user data from $_POST.
     * @return array user data
     */
    private function getUserData(): array
    {
        $user = [];
        $user['first_name'] = trim($_POST['first_name']);
        $user['last_name'] = trim($_POST['last_name']);
        $user['birthdate'] = trim($_POST['birthdate']);
        $user['height'] = trim($_POST['height']);

        if (isset($_POST['club_member']))
        {
            $user['club_member'] = trim($_POST['club_member']);
        }

        return $user;
    }

    /**
     * Validates user data and returns an array with detected errors.
     * @param $user
     * @return array detected error
     */
    private function validateUserData(&$user): array
    {
        $errors = [];

        if (!isset($user['first_name']) || empty($user['first_name']) && $user['first_name'] == '')
        {
            $errors['first_name'] = 'first name cannot be empty';
        }

        if (!isset($user['last_name']) || empty($user['last_name']) && $user['last_name'] == '')
        {
            $errors['last_name'] = 'last name cannot be empty';
        }

        if (!$this->validateDate($user['birthdate']))
        {
            $dateTime = new DateTime('now');

            $user['birthdate'] = $dateTime->format('Y-m-d');
            $user['birthdate'] = '';
            $errors['birthdate'] = 'pick a valid date';
        }

        $userHeight = filter_var($user['height'], FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0, 'max_range' => 400]]);
        if ($userHeight === false)
        {
            $errors['height'] = 'enter a valid height';
        }

        $club_member = false;
        if (isset($user['club_member']) && $user['club_member'] === 'true')
        {
            $club_member = true;
        }
        $user['club_member'] = $club_member;

        return $errors;
    }

    /**
     * Sanitizes user data for HTML output.
     * @param $user
     */
    private function sanitizeUserData(&$user): void
    {
        foreach ($user as &$property)
        {
            $property = Tools::convertForHTML($property);
        }
    }

    private function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
