<?php

namespace Core;

interface IDB
{
    public function GetData();

    public function GetTextSubTopics(string $topics, string $subtopics);
}