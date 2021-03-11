<?php

declare(strict_types=1);

namespace JohanSatge\WorkFlowy;

class WorkFlowyAccount
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $theme;

    /**
     * @var int
     */
    private $itemsCreatedInCurrentMonth;

    /**
     * @var int
     */
    private $monthlyItemQuota;

    /**
     * @var \DateTimeImmutable
     */
    private $registrationDate;

    /**
     * Account information of logged in user.
     *
     * @param array<mixed> $data Data received from WorkFlowy server
     */
    public function __construct(array $data)
    {
        $this->username = !empty($data['settings']['username']) ? $data['settings']['username'] : '';
        $this->theme = !empty($data['settings']['theme']) ? $data['settings']['theme'] : '';
        $this->email = !empty($data['settings']['email']) ? $data['settings']['email'] : '';
        $this->itemsCreatedInCurrentMonth = !empty($data['projectTreeData']['mainProjectTreeInfo']['itemsCreatedInCurrentMonth']) ? (int) $data['projectTreeData']['mainProjectTreeInfo']['itemsCreatedInCurrentMonth'] : 0;
        $this->monthlyItemQuota = !empty($data['projectTreeData']['mainProjectTreeInfo']['monthlyItemQuota']) ? (int) $data['projectTreeData']['mainProjectTreeInfo']['monthlyItemQuota'] : 0;
        $this->registrationDate = !empty($data['projectTreeData']['mainProjectTreeInfo']['dateJoinedTimestampInSeconds']) ? new \DateTimeImmutable((string) $data['projectTreeData']['mainProjectTreeInfo']['dateJoinedTimestampInSeconds']) : new \DateTimeImmutable();
    }

    /**
     * Username.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Current theme name.
     *
     * @return string
     */
    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * Email address.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Number of items created in current month.
     *
     * @return int
     */
    public function getItemsCreatedInCurrentMonth(): int
    {
        return $this->itemsCreatedInCurrentMonth;
    }

    /**
     * Quota of items per month.
     *
     * @return int
     */
    public function getMonthlyItemQuota(): int
    {
        return $this->monthlyItemQuota;
    }

    /**
     * Date of registration
     *
     * @return \DateTimeImmutable
     */
    public function getRegistrationDate(): \DateTimeImmutable
    {
        return $this->registrationDate;
    }
}
