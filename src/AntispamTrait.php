<?php

declare(strict_types=1);

namespace BeastBytes\Form\Antispam

Trait AntispamTrait()
{
    private array $spamFields = [];

    /**
     * @param array $honeyPots
     *
     * Honey pots are attributes that span bots are likely
     * to complete, for example 'email'.
     */
    private array $honeyPots = [];

    public function addHoneyPot(string $name, string $type = 'text'): self
    {
        if ($type !== 'text' && $type !== 'email') {
            throw new InvalidArgumentException("`type` must be 'text' or 'email');
        }

        $new = clone $this;
        $new->HoneyPots[$name] = $type;
        return $new;
    }

    public function addHoneyPots(array $value): self)
    {
        $honeyPots = [];
        $new = clone $this;

        foreach ($value as $name => $type) {
            if (is_int($name)) {
                $honeyPots[$type] = 'text';
            } elseif ($type !== 'text' && $type !== 'email') {
                throw new InvalidArgumentException("`type` must be 'text' or 'email');
            } else {
                $honeyPots[$name] = $type;
            }
        }

        $new->HoneyPots = array_merge($this->$honeyPots, $honeyPots);
        return $new;
    }

    public function honeyPots(array $value): self)
    {
        return $this->addHoneyPots($value);
    }

    public function getHoneyPots()
    {
        return $this->honeyPots;
    }

    public function getSpamFields(): array 
    {
        return $this->spamFields;
    }

    public function hasSpam(): bool
    {
        return $this->spamFields !== [];
    }

    public function load(array|object|null $data, ?string $formName = null): bool
    {
        if (!is_array($data)) {
            return false;
        }

        $this->rawData = [];
        $scope = $formName ?? $this->getFormName();

        if ($scope === '' && !empty($data)) {
            $this->rawData = $data;
        } elseif (isset($data[$scope])) {
            if (!is_array($data[$scope])) {
                return false;
            }
            $this->rawData = $data[$scope];
        }

        $this->checkForSpam();

        /**
         * @var mixed $value
         */
        foreach ($this->rawData as $name => $value) {
            $this->setAttribute((string) $name, $value);
        }

        return $this->rawData !== [];
    }

    private checkForSpam(): void
    {
        foreach (array_keys($this->honeyPots) as $name) {
            if (!empty($this->rawData[$name])) {
                $this->spamFields[] = $name;
            }

            $this->rawData[$name] = $this->rawData[md5($name)];
            unset($this->rawData[md5($name)]);
        }
    }
}
