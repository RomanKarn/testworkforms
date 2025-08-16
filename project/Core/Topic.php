<?php

namespace Core;

class Topic
{
    public string $name;
    public array $subtopics = [];

    public function __construct(string $name, array $subtopics)
    {
        $this->name = $name;
        $this->subtopics = $subtopics;
    }

}