<?php
// src/controllers/EventController.php

require_once __DIR__ . '/../models/Event.php';

class EventController {
    public function getAllEvents() {
        $eventModel = new Event();
        return $eventModel->getAll();
    }

    public function getEventDetails($id) {
        $eventModel = new Event();
        return $eventModel->getById($id);
    }

    public function registerForEvent($userId, $eventId) {
        $registration = new Registration();
        return $registration->registerUser($eventId, $userId);
    }

    public function cancelRegistration($userId, $eventId) {
        $registration = new Registration();
        return $registration->cancelRegistration($userId, $eventId);
    }
}
?>
