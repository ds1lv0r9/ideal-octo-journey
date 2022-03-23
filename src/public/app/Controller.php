<?php


class Controller
{
    protected DB_Model $db;
    protected Template $template;
    protected Sessions $session;

    public function __construct(DB_Model $db, Template $template, Sessions $session)
    {
        $this->db = $db;
        $this->template = $template;
        $this->session = $session;
    }
}
