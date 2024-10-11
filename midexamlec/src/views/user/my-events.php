<h1>My Events</h1>
<ul>
<?php foreach ($userEvents as $event): ?>
    <li><?= $event['name'] ?></li>
<?php endforeach; ?>
</ul>
