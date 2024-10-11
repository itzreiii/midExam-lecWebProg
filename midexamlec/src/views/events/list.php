<h1>All Events</h1>
<ul>
<?php foreach ($events as $event): ?>
    <li><a href="details.php?id=<?= $event['id'] ?>"><?= $event['name'] ?></a></li>
<?php endforeach; ?>
</ul>
