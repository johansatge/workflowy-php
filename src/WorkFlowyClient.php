<?php

declare(strict_types=1);

namespace JohanSatge\WorkFlowy;

use JohanSatge\WorkFlowy\Model\Document;
use JohanSatge\WorkFlowy\Model\Item;

class WorkFlowyClient
{
    public const ROOT_ITEM_ID = 'root';

    private const LOGIN_URL = 'https://workflowy.com/ajax_login';
    private const API_URL = 'https://workflowy.com/%s';
    private const TIMEOUT = 5;

    /**
     * Character set for push poll ids
     */
    private const PUSH_POLL_CHARS = [
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l',
        'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
        'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
    ];

    /**
     * API version to talk to WorkFlowy.
     */
    private const CLIENT_VERSION = 21;

    /**
     * Session Id.
     *
     * Is valid for 6 months after creating it with a login. There is no refreshing mechanism, it will expire.
     *
     * @var string|null Session id for interacting with WorkFlowy servers
     */
    private ?string $sessionId;

    /**
     * Client Id.
     *
     * Given by the server and sent back with each request. Also seems to foster reliable communication.
     */
    private string $clientId;

    /**
     * Transaction id of last transaction.
     *
     * Is a timestamp of last state the client saw, so the server can send the needed changes to keep the client
     * current.
     */
    private int $mostRecentOperationTransactionId;

    /**
     * General client that talks to WorkFlowy.
     *
     * If instantiated without parameter it's used to make the login call. All other calls require an active session.
     *
     * @throws WorkFlowyException
     */
    public function __construct(?string $sessionId = null)
    {
        if (null !== $sessionId) {
            if (!\preg_match('/^[a-z0-9]{32}$/', $sessionId)) {
                throw new WorkFlowyException('Invalid session Id');
            }

            $this->sessionId = $sessionId;
        }
    }

    /**
     * Makes a WorkFlowy login and returns an active session id.
     *
     * Saves the session id internally as well.
     * Each new call to login makes a new session, so caching the session id may be advised.
     *
     * @throws WorkFlowyException If user and password are empty, or login failed
     * @return string Session Id
     */
    public function login(string $username, string $password): string
    {
        if (empty($username) || empty($password)) {
            throw new WorkFlowyException('You must provide username and password');
        }

        // Curl will write into this array where the session id can be extracted from
        $headers = [];
        $this->curl(
            self::LOGIN_URL,
            ['username' => $username, 'password' => $password, 'next' => ''],
            $headers
        );

        if (\is_array($headers['set-cookie'])) {
            foreach ($headers['set-cookie'] as $cookie) {
                $matches = [];
                if (\preg_match('`^sessionid=([a-z0-9]+);`mis', $cookie, $matches)) {
                    $this->sessionId = $matches[1];

                    return $this->sessionId;
                }
            }
        }

        throw new WorkFlowyException('Login failed, can\'t find session in headers');
    }

    /**
     * Fetches and returns the main list.
     *
     * @throws WorkFlowyException
     */
    public function getDocument(): Document
    {
        $data = $this->apiRequest('get_initialization_data');

        return WorkFlowyParser::parseDocument($data);
    }

    /**
     * Updates an item after changing data.
     *
     * If no changes were done, no server queries are made.
     *
     * @param Item $item
     *
     * @throws WorkFlowyException
     */
    public function update(Item $item): void
    {
        $changeStack = $item->getChangeStack();

        // No changes
        if (0 === \count($changeStack)) {
            return;
        }

        $properties = [];

        foreach ($changeStack as $field => $true) {
            switch ($field) {
                case Item::FIELD_NAME:
                    $properties['name'] = $item->getName();
                    break;
                case Item::FIELD_DESCRIPTION:
                    $properties['description'] = $item->getDescription();
                    break;
                case Item::FIELD_COMPLETE:
                    $this->listRequest(
                        ($item->isComplete() ? 'complete' : 'uncomplete'),
                        ['projectid' => $item->getId()]
                    );
                    break;
                case Item::FIELD_BOARD:
                    $properties['metadataPatches'][] = (object)[
                        'op' => 'replace',
                        'path' => ['layoutMode'],
                        'value' => (true === $item->isBoard()) ? 'board' : 'bullets',
                    ];
                    $properties['metadataInversePatches'][] = (object)[
                        'op' => 'replace',
                        'path' => ['layoutMode'],
                        'value' => (true === $item->isBoard()) ? 'bullets' : 'board',
                    ];
                    break;
                default:
                    // Do nothing, don't know what that is
                    break;
            }
        }

        // These properties can be combined in one call
        if (0 !== \count($properties)) {
            $this->listRequest('edit', \array_merge(['projectid' => $item->getId()], $properties));
        }

        // Done, clear the stack
        $item->clearChangeStack();
    }

    /**
     * Delete a given item.
     *
     * Also deletes all its children.
     *
     * @param Item $item
     *
     * @throws WorkFlowyException
     */
    public function delete(Item $item): void
    {
        $this->listRequest('delete', ['projectid' => $item->getId()]);
    }

    /**
     * Creates an item.
     *
     * @param string $parentId    Id of the parent list you want to add this to
     * @param string $name        Optional name, the text behind the bullet on WorkFlowy
     * @param string $description Optional description, expandable text below the name of an item
     * @param int    $priority    Optional priority, order in parent, high means further down the list
     *
     * @throws WorkFlowyException
     */
    public function createItem(
        string $parentId,
        string $name = '',
        string $description = '',
        int $priority = 0
    ): void {
        $newId = $this->generateID();

        $this->listRequest(
            'create',
            [
                'projectid' => $newId,
                'parentid' => $parentId,
                'priority' => $priority,
            ]
        );

        // Collecting things to change
        $properties = [];

        if (!empty($name)) {
            $properties['name'] = $name;
        }
        if (!empty($description)) {
            $properties['description'] = $description;
        }

        // Changing all of the properties at once
        if (0 !== \count($properties)) {
            $this->listRequest('edit', \array_merge(['projectid' => $newId], $properties));
        }
    }

    /**
     * Moves a list into another list.
     *
     * @param Item $subject  Which list to move
     * @param Item $target   Subject will be moved as a child into this list
     * @param int  $priority Optional positioning info
     *
     * @throws WorkFlowyException
     */
    public function move(Item $subject, Item $target, int $priority = 0): void
    {
        $this->listRequest(
            'move',
            [
                'projectid' => $subject->getId(),
                'parentid' => $target->getId(),
                'priority' => $priority,
            ]
        );
    }

    /**
     * Get account information.
     *
     * @throws WorkFlowyException on connection error
     *
     * @return WorkFlowyAccount
     */
    public function getAccount(): WorkFlowyAccount
    {
        $data = $this->apiRequest('get_initialization_data');

        return new WorkFlowyAccount($data);
    }

    /**
     * Get settings information.
     *
     * Contains the favorites as well.
     *
     * @throws WorkFlowyException on connection error
     *
     * @return WorkFlowySettings
     */
    public function getSettings(): WorkFlowySettings
    {
        $data = $this->apiRequest('get_settings');

        return new WorkFlowySettings($data);
    }

    /**
     * Performs a list request.
     *
     * These are basically the requests for editing, deleting, moving and creation of lists.
     *
     * @param string       $action List action to perform
     * @param array<mixed> $data   Data to perform action with
     *
     * @throws WorkFlowyException
     */
    private function listRequest(string $action, array $data): void
    {
        $this->apiRequest(
            'push_and_poll',
            [
                'client_id' => $this->clientId,
                'client_version' => self::CLIENT_VERSION,
                // Has to be random between requests for a few seconds, or the server might think it's the same request
                'push_poll_id' => $this->generatePushPollId(),
                'push_poll_data' => \json_encode(
                    [
                        (object)[
                            // This id is a sort of timestamp so the server knows what to send in terms of changes
                            // since that request has been made
                            'most_recent_operation_transaction_id' => $this->mostRecentOperationTransactionId,
                            'operations' => [
                                (object)[
                                    'type' => $action,
                                    'data' => (object)$data,
                                ],
                            ],
                        ],
                    ],
                    JSON_THROW_ON_ERROR
                ),
            ]
        );
    }

    /**
     * Makes an API request and returns the answer.
     *
     * @param string       $method
     * @param array<mixed> $request
     *
     * @return array<mixed>
     * @throws WorkFlowyException
     */
    private function apiRequest(string $method, array $request = [])
    {
        if (empty($this->sessionId)) {
            throw new WorkFlowyException('A session Id is required to make API calls');
        }

        $json = $this->curl(\sprintf(self::API_URL, $method), $request);

        try {
            $data = \json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new WorkFlowyException('Could not decode JSON: '.$e->getMessage());
        }

        if (!empty($data['projectTreeData']['mainProjectTreeInfo']['initialMostRecentOperationTransactionId'])) {
            $this->mostRecentOperationTransactionId = $data['projectTreeData']['mainProjectTreeInfo']['initialMostRecentOperationTransactionId'];
            $this->clientId = $data['projectTreeData']['clientId'];
        }

        if (!empty($data['results'][0]['new_most_recent_operation_transaction_id'])) {
            $this->mostRecentOperationTransactionId = $data['results'][0]['new_most_recent_operation_transaction_id'];
        }

        if (!empty($data['results'][0]['error'])) {
            throw new WorkFlowyException('An error occurred when executing the API request');
        }

        return $data;
    }

    /**
     * Sends a CURL request to the server, by using the given POST data.
     *
     * @param string       $url
     * @param array<mixed> $postFields
     * @param array<mixed> $headers
     *
     * @return string
     * @throws WorkFlowyException
     */
    private function curl(string $url, array $postFields = [], array &$headers = []): string
    {
        // @TODO: This whole thing here could use a cleanup or better replace with external client
        $ch = \curl_init();
        \curl_setopt($ch, CURLOPT_URL, $url);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        \curl_setopt($ch, CURLOPT_HEADER, false);
        \curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);

        $headers = [];
        // Code from https://stackoverflow.com/a/41135574/696517
        // This function is called by curl for each header received
        \curl_setopt(
            $ch,
            CURLOPT_HEADERFUNCTION,
            function ($curl, $header) use (&$headers) {
                $len = \strlen($header);
                $header = \explode(':', $header, 2);
                if (\count($header) < 2) { // ignore invalid headers
                    return $len;
                }

                $name = \strtolower(\trim($header[0]));
                if (!\array_key_exists($name, $headers)) {
                    $headers[$name] = [\trim($header[1])];
                } else {
                    $headers[$name][] = \trim($header[1]);
                }

                return $len;
            }
        );

        if (\count($postFields) > 0) {
            \curl_setopt($ch, CURLOPT_POST, true);
            \curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }

        if (!empty($this->sessionId)) {
            \curl_setopt($ch, CURLOPT_HTTPHEADER, ['Cookie: sessionid='.$this->sessionId]);
        }

        $response = (string) \curl_exec($ch);

        $error = \curl_error($ch);
        \curl_close($ch);
        if (!empty($error)) {
            throw new WorkFlowyException($error);
        }

        return $response;
    }

    /**
     * Generates a random UUIDv4.
     *
     * Is used for creating new items. Official client doesn't do v4 yet, but will change to them in the future.
     *
     * @return string UUID v4
     */
    private function generateId(): string
    {
        $randomBytes = \random_bytes(16);

        $randomBytes[6] = \chr(\ord($randomBytes[6]) & 0x0f | 0x40); // set version to 0100
        $randomBytes[8] = \chr(\ord($randomBytes[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return \vsprintf('%s%s-%s-%s-%s-%s%s%s', \str_split(\bin2hex($randomBytes), 4));
    }

    /**
     * Generates a random push poll id.
     *
     * Format is 8 chars, lower and upper case letters, and numbers are allowed.
     *
     * @return string Random push poll id
     */
    private function generatePushPollId(): string
    {
        $picks = \array_rand(\array_flip(self::PUSH_POLL_CHARS), 8);

        return \implode('', $picks);
    }
}
