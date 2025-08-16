<?php

namespace Core;
class MokeDB implements IDB
{
    var array $topics = [];
    public function __construct()
    {
        $this->topics = [
            new Topic('Тема 1', [
                new SubTopic('Подтема 1.1', 'Текст подтемы 1.1'),
                new SubTopic('Подтема 1.2', 'Текст подтемы 1.2'),
                new SubTopic('Подтема 1.3', 'Текст подтемы 1.3'),
            ]),
            new Topic('Тема 2', [
                new SubTopic('Подтема 2.1', 'Текст подтемы 2.1'),
                new SubTopic('Подтема 2.2', 'Текст подтемы 2.2'),
                new SubTopic('Подтема 2.3', 'Текст подтемы 2.3'),
            ])
        ];
    }
    public function GetData() : array
    {
        return $this->topics;
    }

    public function GetTextSubTopics(string $topics, string $subtopics) : string
    {
        foreach ($this->topics as $topic) {
            if ($topic->name === $topics) {
                foreach ($topic->subtopics as $subtopic) {
                    if ($subtopic->name === $subtopics) {
                        return $subtopic->text;
                    }
                }
            }
        }
        return '';
    }
}