<?php
class AdminController {
    public function dashboard() {
        // Load the dashboard with events and registrant counts
    }

    public function createEvent($data) {
        // Code to add new event with validation
    }

    public function editEvent($eventId, $data) {
        // Edit the event details
    }

    public function deleteEvent($eventId) {
        // Delete event with confirmation
    }

    public function viewRegistrations($eventId) {
        // Fetch users registered for a particular event
    }

    public function exportRegistrationsCSV($eventId) {
        // Export registrations to CSV (added feature)
    }
}
