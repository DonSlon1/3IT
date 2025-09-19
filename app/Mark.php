<?php namespace app;


class Mark implements App
{
    public function run(): void
    {
        header('Content-Type: application/json');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Invalid request method');
            }

            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['id']) || !isset($data['marked'])) {
                throw new \Exception('Missing required parameters');
            }

            $recordId = (int)$data['id'];
            $marked = (bool)$data['marked'];

            DbConfig::getDbConnection();

            // Verify record exists
            $exists = \dibi::query('SELECT id FROM `zaznamy` WHERE id = %i', $recordId)->fetch();
            if (!$exists) {
                throw new \Exception('Record not found');
            }

            // Get or create session
            if (!session_id()) {
                session_start();
            }
            $sessionId = session_id();

            if ($marked) {
                // Add mark
                \dibi::query('INSERT IGNORE INTO `marked_records`', [
                    'zaznam_id' => $recordId,
                    'session_id' => $sessionId
                ]);
            } else {
                // Remove mark
                \dibi::query('DELETE FROM `marked_records`
                    WHERE zaznam_id = %i AND session_id = %s',
                    $recordId, $sessionId);
            }

            echo json_encode([
                'success' => true,
                'marked' => $marked
            ]);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}