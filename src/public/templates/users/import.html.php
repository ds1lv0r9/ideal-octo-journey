<?php

$title = 'Setup';

ob_start();

echo '<pre>';
foreach ($data['info'] as $info)
{
    echo $info;
}
echo '</pre>';

$output = ob_get_contents();
ob_end_clean();
