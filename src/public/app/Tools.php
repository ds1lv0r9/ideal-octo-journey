<?php


class Tools
{
    public static function convertForHTML($string): string
    {
        if (is_bool($string))
        {
            return ($string) ? 'true' : 'false';
        }

        return htmlspecialchars($string, ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function importCsv(string $file): array
    {
        $result = [];
        $handle = fopen($file, "r");

        if (empty($handle) === false)
        {
            while (($data = fgetcsv($handle, 0, ',')) !== false)
            {
                $user = [];
                $user['first_name'] = $data[0];
                $user['last_name'] = $data[1];
                $user['birthdate'] = $data[2];
                $user['height'] = $data[3];
                $user['club_member'] = $data[4];

                $result[] = $user;
            }

            fclose($handle);
        }

        return $result;
    }
}
