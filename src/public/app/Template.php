<?php


class Template
{
    public function render(string $template, array $data = [])
    {
        require_once 'templates/' . $template;
        require_once 'templates/template.html.php';
    }
}
