<?php


class Sessions
{
    private function getPrivateToken(): string
    {
        if (empty($_SESSION['csrf_token']))
        {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public function getFormId(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function getFormToken(string $formId): string
    {
        if (!isset($_SESSION['token_expire'][$formId]))
        {
            $_SESSION['token_expire'][$formId] = time() + 7200;
        }

        return hash_hmac('sha256', $formId, $this->getPrivateToken());
    }

    public function verifyFormToken(string $formToken, string $formId): bool|string
    {
        if (!isset($_SESSION['token_expire'][$formId]))
        {
            $message = 'invalid csrf token_expire';

            if ($_POST)
            {
                return $message;
            }

            die($message);
        }

        if ($_SESSION['token_expire'][$formId] < time())
        {
            unset($_SESSION['token_expire'][$formId]);

            $message = 'csrf token expired';

            if ($_POST)
            {
                return $message;
            }

            die($message);
        }

        $isTokenValid = hash_equals($formToken, $this->getFormToken($formId));

        if (!$_POST)
        {
            unset($_SESSION['token_expire'][$formId]);
        }

        if (rand(0, 99) < 3)
        {
            foreach ($_SESSION['token_expire'] as $key => $value)
            {
                if ($value < time())
                {
                    unset($_SESSION['token_expire'][$key]);
                }
            }
        }

        return $isTokenValid;
    }
}
