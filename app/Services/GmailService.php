<?php

namespace App\Services;

use Google\Client;
use Google\Service\Gmail;

class GmailService
{
    protected Gmail $service;

    public function __construct(AuthService $authService)
    {
        $client = $authService->getClient();
        $this->service = new Gmail($client);
    }

    /**
     * List recent messages from the user's inbox.
     *
     * @param int $limit
     * @return array
     */
    public function listMessages(int $limit = 10): array
    {
        $params = [
            'maxResults' => $limit,
            'labelIds' => ['INBOX'],
        ];

        $messages = $this->service->users_messages->listUsersMessages('me', $params);

        return $messages->getMessages() ?: [];
    }

    /**
     * Get details of a specific message.
     *
     * @param string $id
     * @return \Google\Service\Gmail\Message
     */
    public function getMessage(string $id)
    {
        return $this->service->users_messages->get('me', $id);
    }
}
