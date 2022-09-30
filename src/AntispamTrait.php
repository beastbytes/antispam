<?php

declare(strict_types=1);

namespace BeastBytes\Form\Antispam

Trait AntispamTrait()
{

    /**
     * @param array $honeyPots
     */
    private array $honeyPots = [];



/**
 * Honey pots are attributes that span bots are likely
 * to complete, for example 'email'.
 * Each attribute creates two input fields:
 * 1. A hidden field with the original attribute name.
 * This should be empty on form submission;
 * if not a spam bot has completed it.
 * 2. A text input with a new name that is shown to the user.
 */

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
        $honeyPots = [];
        foreach ($this->HoneyPots as $honeyPot) {
            $honeyPots[$honeyPot] = md5($honeyPot);
        }
        return $honeyPots;
    }
}
