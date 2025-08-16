<?php

$topics = [
    'Тема 1' => ['Подтема 1.1', 'Подтема 1.2', 'Подтема 1.3'],
    'Тема 2' => ['Подтема 2.1', 'Подтема 2.2', 'Подтема 2.3']
];

$defaultTopic = array_key_first($topics);
$defaultSubtopic = $topics[$defaultTopic][0];
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Тестовое задание — Темы/Подтемы</title>
</head>
<body>
<h1 style="font-size:18px;margin-bottom:12px">Тестовое: Тема / Подтема / Содержание</h1>
<div id="root">
    <div class="container" id="app" aria-live="polite">
        <div class="panel" id="topicsPanel" aria-label="Темы">
            <h2>Тема</h2>
            <div class="list" id="topicsList">

            </div>
        </div>

        <div class="panel" id="subtopicsPanel" aria-label="Подтемы">
            <h2>Подтема</h2>
            <div class="list" id="subtopicsList">

            </div>
        </div>

        <div class="panel" id="contentPanel" aria-label="Содержание">
            <h2>Содержимое</h2>
            <div class="content" id="contentArea">Загрузка...</div>
            <div class="muted" id="statusArea"></div>
        </div>
    </div>
</div>

<script>
    const API_TOPICS = '/api/topics.php';
    const API_SUBTOPIC = '/api/textSubTopic.php';

    const topicsListEl = document.getElementById('topicsList');
    const subtopicsListEl = document.getElementById('subtopicsList');
    const contentArea = document.getElementById('contentArea');
    const statusArea = document.getElementById('statusArea');

    let state = {
        topics: [],
        selectedTopic: null,
        selectedSubtopic: null
    };

    function setStatus(text, isError=false) {
        statusArea.textContent = text || '';
        statusArea.classList.toggle('error', !!isError);
    }

    function clearSelection(container) {
        container.querySelectorAll('.item.selected').forEach(el => el.classList.remove('selected'));
    }

    function renderTopics() {
        topicsListEl.innerHTML = '';
        state.topics.forEach(topic => {
            const div = document.createElement('div');
            div.className = 'item';
            div.tabIndex = 0;
            div.textContent = topic.name;
            div.dataset.topic = topic.name;
            div.addEventListener('click', () => {
                onTopicClick(topic.name);
            });
            div.addEventListener('keydown', (ev) => {
                if (ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); div.click(); }
            });
            topicsListEl.appendChild(div);
        });
    }

    function renderSubtopicsFor(topicName, autoSelectFirst=true) {
        subtopicsListEl.innerHTML = '';
        const topic = state.topics.find(t => t.name === topicName);
        if (!topic) {
            subtopicsListEl.innerHTML = '<div class="muted">Нет подтем</div>';
            return;
        }
        topic.subtopics.forEach(s => {
            const d = document.createElement('div');
            d.className = 'item';
            d.tabIndex = 0;
            d.textContent = s.name;
            d.dataset.subtopic = s.name;
            d.addEventListener('click', () => {
                onSubtopicClick(s.name);
            });
            d.addEventListener('keydown', (ev) => {
                if (ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); d.click(); }
            });
            subtopicsListEl.appendChild(d);
        });

        if (autoSelectFirst && topic.subtopics.length > 0) {
            console.log(topic.subtopics[0].name);
            onSubtopicClick(topic.subtopics[0].name, /*fromRender=*/true);
        } else {
            contentArea.textContent = '';
        }
    }

    function onTopicClick(topicName) {
        state.selectedTopic = topicName;

        clearSelection(topicsListEl);
        const el = Array.from(topicsListEl.children).find(n => n.dataset.topic === topicName);
        if (el) el.classList.add('selected');

        renderSubtopicsFor(topicName, true);
        setStatus('');
    }

    let lastFetchController = null;

    function fetchSubtopicText(topicName, subtopicName) {

        if (lastFetchController) lastFetchController.abort();
        lastFetchController = new AbortController();
        const signal = lastFetchController.signal;

        contentArea.textContent = 'Загрузка...';
        contentArea.classList.add('loading');
        setStatus('');

        const url = `${API_SUBTOPIC}?topic=${encodeURIComponent(topicName)}&subtopic=${encodeURIComponent(subtopicName)}`;
        return fetch(url, { method: 'GET', signal })
            .then(resp => {
                if (!resp.ok) {
                    if (resp.status === 404) throw new Error('Подтема не найдена (404).');
                    throw new Error('Ошибка при получении содержимого: ' + resp.status);
                }
                return resp.json();
            })
            .then(json => {

                const text = json.text ?? (json[0]?.text ?? '');
                contentArea.textContent = text || `Не найден текст для ${subtopicName}`;
                contentArea.classList.remove('loading');
                setStatus('');
                return text;
            })
            .catch(err => {
                if (err.name === 'AbortError') return;
                contentArea.classList.remove('loading');
                contentArea.textContent = '';
                setStatus(err.message, true);
                console.error(err);
            });
    }

    function onSubtopicClick(subtopicName, fromRender=false) {
        state.selectedSubtopic = subtopicName;

        clearSelection(subtopicsListEl);
        const el = Array.from(subtopicsListEl.children).find(n => n.dataset.subtopic === subtopicName);
        if (el) el.classList.add('selected');

        if (!state.selectedTopic) return;
        console.log(state.selectedTopic);
        fetchSubtopicText(state.selectedTopic, subtopicName);
    }


    function init() {
        setStatus('Загрузка списка тем...');
        contentArea.textContent = '';
        fetch(API_TOPICS, { method: 'GET' })
            .then(resp => {
                if (!resp.ok) throw new Error('Не удалось получить список тем. Код: ' + resp.status);
                return resp.json();
            })
            .then(json => {

                state.topics = json.map(t => {

                    const name = t.name ?? t.Name ?? t.title ?? Object.values(t)[0]?.name ?? null;
                    const rawSubs = t.subtopics ?? t.subTopics ?? t.subs ?? [];
                    const subs = Array.isArray(rawSubs) ? rawSubs.map(s => {

                        if (typeof s === 'string') return { name: s, text: '' };
                        return { name: s.name ?? s.Name ?? s.title ?? s[0] ?? '', text: s.text ?? '' };
                    }) : [];
                    return { name: name, subtopics: subs };
                });


                if (!state.topics.length) {
                    setStatus('Список тем пуст', true);
                    topicsListEl.innerHTML = '<div class="muted">Нет тем</div>';
                    return;
                }

                renderTopics();


                const firstTopic = state.topics[0].name;
                state.selectedTopic = firstTopic;
                const topicEl = Array.from(topicsListEl.children).find(n => n.dataset.topic === firstTopic);
                if (topicEl) topicEl.classList.add('selected');

                renderSubtopicsFor(firstTopic, true);

                setStatus('');
            })
            .catch(err => {
                setStatus(err.message || 'Ошибка при загрузке тем', true);
                topicsListEl.innerHTML = '<div class="error">Ошибка загрузки тем</div>';
                console.error(err);
            });
    }

    document.addEventListener('DOMContentLoaded', init);
</script>
</body>
</html>

<style>
    :root {
        --gap: 16px;
        --panel-bg: #fafafa;
        --border: #ddd;
        --highlight: #fff59d;
    }
    body {
        font-family: Inter, Roboto, Arial, sans-serif;
        margin: 24px;
        color: #111;
        background: #f4f6f8;
    }
    .container {
        display: grid;
        grid-template-columns: 240px 240px 1fr;
        gap: var(--gap);
        align-items: start;
    }

    .panel {
        background: var(--panel-bg);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 12px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        min-height: 320px;
    }

    .panel h2 {
        margin: 0 0 10px 0;
        font-size: 14px;
        color: #333;
        font-weight: 600;
    }

    .list {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .item {
        padding: 8px 10px;
        border-radius: 6px;
        cursor: pointer;
        user-select: none;
        border: 1px solid transparent;
    }

    .item.selected {
        background: var(--highlight);
        border-color: #e0d780;
    }

    .content {
        padding: 12px;
        font-size: 16px;
        line-height: 1.5;
        white-space: pre-wrap;
        min-height: 200px;
        display: flex;
        align-items: flex-start;
    }

</style>