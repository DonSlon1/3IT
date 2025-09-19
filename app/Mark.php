<?php

declare(strict_types=1);

namespace app;

use Exception;

/**
 * Record marking/selection controller for AJAX requests
 *
 * Handles marking and unmarking records with session-based persistence
 * for user selections across page reloads and navigation.
 */
class Mark implements App
{
    /**
     * Handle record marking/unmarking requests
     *
     * Processes AJAX POST requests to mark or unmark records
     * with proper validation and session management.
     *
     * @return void
     */
    public function run(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $this->validateRequest();
            $data = $this->getRequestData();

            $recordId = $this->validateRecordId($data['id']);
            $marked = (bool)$data['marked'];

            $this->ensureSessionStarted();
            $this->verifyRecordExists($recordId);
            $this->updateMarkingStatus($recordId, $marked);

            $this->sendSuccessResponse($marked);

        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage());
        }
    }

    /**
     * Validate HTTP request method
     *
     * @return void
     * @throws Exception If request method is not POST
     */
    private function validateRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Method not allowed - POST required');
        }
    }

    /**
     * Get and validate JSON request data
     *
     * @return array Parsed request data
     * @throws Exception If JSON is invalid or missing required fields
     */
    private function getRequestData(): array
    {
        $json = file_get_contents('php://input');

        if ($json === false) {
            throw new Exception('Failed to read request body');
        }

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON format: ' . json_last_error_msg());
        }

        if (!isset($data['id']) || !isset($data['marked'])) {
            throw new Exception('Missing required parameters: id and marked');
        }

        return $data;
    }

    /**
     * Validate and convert record ID
     *
     * @param mixed $id Record ID from request
     * @return int Validated record ID
     * @throws Exception If ID is invalid
     */
    private function validateRecordId($id): int
    {
        $recordId = filter_var($id, FILTER_VALIDATE_INT);

        if ($recordId === false || $recordId <= 0) {
            throw new Exception('Invalid record ID');
        }

        return $recordId;
    }

    /**
     * Ensure session is started
     *
     * @return void
     */
    private function ensureSessionStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Verify that record exists in database
     *
     * @param int $recordId Record ID to verify
     * @return void
     * @throws Exception If record does not exist
     */
    private function verifyRecordExists(int $recordId): void
    {
        DbConfig::getDbConnection();

        $exists = \dibi::query('SELECT id FROM `zaznamy` WHERE id = %i', $recordId)->fetch();

        if (!$exists) {
            throw new Exception('Record not found');
        }
    }

    /**
     * Update record marking status in database
     *
     * @param int $recordId Record ID to update
     * @param bool $marked Whether to mark or unmark
     * @return void
     * @throws Exception If database operation fails
     */
    private function updateMarkingStatus(int $recordId, bool $marked): void
    {
        $sessionId = session_id();

        if ($marked) {
            $this->addRecordMark($recordId, $sessionId);
        } else {
            $this->removeRecordMark($recordId, $sessionId);
        }
    }

    /**
     * Add record mark to database
     *
     * @param int $recordId Record ID
     * @param string $sessionId Session ID
     * @return void
     */
    private function addRecordMark(int $recordId, string $sessionId): void
    {
        \dibi::query('INSERT IGNORE INTO `marked_records`', [
            'zaznam_id' => $recordId,
            'session_id' => $sessionId,
            'marked_at' => new \DateTime()
        ]);
    }

    /**
     * Remove record mark from database
     *
     * @param int $recordId Record ID
     * @param string $sessionId Session ID
     * @return void
     */
    private function removeRecordMark(int $recordId, string $sessionId): void
    {
        \dibi::query(
            'DELETE FROM `marked_records` WHERE zaznam_id = %i AND session_id = %s',
            $recordId,
            $sessionId
        );
    }

    /**
     * Send successful JSON response
     *
     * @param bool $marked Current marking status
     * @return void
     */
    private function sendSuccessResponse(bool $marked): void
    {
        echo json_encode([
            'success' => true,
            'marked' => $marked,
            'timestamp' => time()
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * Send error JSON response
     *
     * @param string $message Error message
     * @return void
     */
    private function sendErrorResponse(string $message): void
    {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'timestamp' => time()
        ], JSON_THROW_ON_ERROR);
    }
}