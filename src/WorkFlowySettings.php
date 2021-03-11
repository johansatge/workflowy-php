<?php

declare(strict_types=1);

namespace JohanSatge\WorkFlowy;

class WorkFlowySettings
{
    private string $theme;
    private string $font;
    private bool $showCompleted;
    private bool $showKeyboardShortcuts;
    private bool $unsubscribeFromSummaryEmails;
    private bool $backupToDropbox;
    private string $email;
    private string $username;

    /**
     * @var array<string>
     */
    private array $favorites;
    private WorkFlowySettingsFeatures $features;

    /**
     * @param array<mixed> $array
     *
     * @throws \JsonException
     */
    public function __construct(array $array)
    {
        $this->theme = $array['theme'] ?? 'default';
        $this->font = $array['font'] ?? 'system';
        $this->showCompleted = $array['show_completed'] ?? false;
        $this->showKeyboardShortcuts = $array['show_keyboard_shortcuts'] ?? false;
        $this->unsubscribeFromSummaryEmails = $array['unsubscribe_from_summary_emails'] ?? true;
        $this->backupToDropbox = $array['backup_to_dropbox'] ?? true;
        $this->email = $array['email'] ?? '';
        $this->username = $array['username'] ?? '';

        $favorites = \json_decode($array['saved_views_json'], true, 512, JSON_THROW_ON_ERROR);
        $this->favorites = \array_filter(\array_map(function ($entry) {
            return $entry['zoomedProject']['projectid'] ?? null;
        }, $favorites));

        $this->features = new WorkFlowySettingsFeatures($array['features']);
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function getFont(): string
    {
        return $this->font;
    }

    public function showCompleted(): bool
    {
        return $this->showCompleted;
    }

    public function showKeyboardShortcuts(): bool
    {
        return $this->showKeyboardShortcuts;
    }

    public function unsubscribeFromSummaryEmails(): bool
    {
        return $this->unsubscribeFromSummaryEmails;
    }

    public function backupToDropbox(): bool
    {
        return $this->backupToDropbox;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return array<string>
     */
    public function getFavorites(): array
    {
        return $this->favorites;
    }

    public function isFavorite(string $listId): bool
    {
        return \in_array($listId, $this->favorites, true);
    }

    public function getFeatures(): WorkFlowySettingsFeatures
    {
        return $this->features;
    }
}
